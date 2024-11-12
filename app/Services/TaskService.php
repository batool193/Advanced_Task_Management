<?php

namespace App\Services;

use Exception;
use App\Models\Task;
use App\Enums\TaskStatus;
use App\Models\TaskStatusUpdate;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskService
{
    /**
     * Retrieve all tasks
     *
     * @param \Illuminate\Http\Request $request
     * @return bool|\Illuminate\Database\Eloquent\Collection
     */
    public function All($request)
    {
        try {
            // Get the authenticated user
            $user = JWTAuth::user();

            // If the user is an admin, get all tasks
            // Otherwise, get only the tasks assigned to the user
            if ($user->role == 'admin') {
                $tasks = Task::select(
                    'title',
                    'description',
                    'type',
                    'status',
                    'priority',
                    'due_date',
                    'assigned_to',
                    'created_by'
                );
            } else {
                $tasks = Task::select(
                    'title',
                    'description',
                    'type',
                    'status',
                    'priority',
                    'due_date',
                    'assigned_to',
                    'created_by'
                )->where('assigned_to', $user->id)->orWhere('created_by', $user->id);
            }

            // Filter the tasks based on the provided query parameters
            if ($request->has('type'))
                $tasks = $tasks->ByType($request->type);
            if ($request->has('status'))
                $tasks = $tasks->ByStatus($request->status);
            if ($request->has('priority'))
                $tasks = $tasks->ByPriority($request->priority);
            if ($request->has('due_date'))
                $tasks = $tasks->ByDueDate($request->due_date);
            if ($request->has('assigned_to'))
                $tasks = $tasks->ByAssignedTo($request->assigned_to);

            return $tasks;
        } catch (JWTException  $e) {
            Log::error('Token is invalid or expired: ' . $e->getMessage());
            return false;
        } catch (QueryException $e) {
            Log::error('Database query error: ' . $e->getMessage());
            return false;
        } catch (ModelNotFoundException $e) {
            Log::error('No tasks found' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error('Error message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new task
     *
     * @param array $data Task data
     * @return bool|\App\Models\Task The created task or false if unauthorized or an error occurs
     */
    public function Create(array $data)
    {
        try {
            // Get the authenticated user
            $user = JWTAuth::user();

            // Check if the user has the required role to create a task
          //  if (in_array($user->role, ['admin', 'manager'])) {
            if (($user->role=='admin')||($user->role=='manager')) {
                DB::beginTransaction();

                // Create a new task with the provided data
                $task = Task::create([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'type' => $data['type'],
                    'status'=>TaskStatus::Open,
                    'priority' => $data['priority'],
                    'created_by' => $user->id
                ]);

                // Attach any dependent tasks if provided
                if (!empty($data['dependent_task_ids'])) {
                    foreach ($data['dependent_task_ids'] as $dependent_task_id) {
                        $dependentTask = Task::withoutTrashed()->find($dependent_task_id);
                        if ($dependentTask) {
                            $task->dependencies()->attach($dependentTask->id);
                        }
                    }
                }

                // Update the task status based on dependencies
                if ($task->dependencies()->where('status', '!=', 'Completed')->exists()) {
                    $task->update(['status' => TaskStatus::Blocked]);
                } else {
                    $task->update(['status' => TaskStatus::Open]);
                }

                // Log the status update
                $statusupdate = TaskStatusUpdate::create([
                    'task_id' => $task->id,
                    'update_by' => $user->id,
                    'status' => $task->status
                ]);

                DB::commit();
                return $task;
            } else {
                Log::error('Unauthorized');
                return false;
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Assign a task to a user
     *
     * @param int $taskId ID of the task to assign
     * @param int $userId ID of the user to assign the task to
     * @return bool|\App\Models\Task The assigned task or false if unauthorized or an error occurs
     */
    public function assignToUser($taskId, $userId)
    {
        try {
            // Get the authenticated user
            $user = JWTAuth::user();

            // Check if the user has the required role to assign the task
           // if (in_array($user->role, ['admin', 'manager'])) {
            if (($user->role=='admin')||($user->role=='manager')) {
                DB::beginTransaction();

                // Find the task
                $task = Task::findOrFail($taskId);

                // Check if the task is not completed
                if ($task->status != 'completed') {
                    // Assign the task to the user and update the status
                    $task->assigned_to = $userId;
                    $task->status = TaskStatus::InProgress;
                    $task->save();

                    // Log the status update
                    TaskStatusUpdate::create([
                        'task_id' => $task->id,
                        'update_by' => $user->id,
                        'status' => TaskStatus::InProgress
                    ]);

                    // Commit the transaction
                    DB::commit();

                    // Return the assigned task
                    return $task;
                }
            }

            // Rollback the transaction if any error occurs
            DB::rollBack();

            // Log an error if unauthorized or an exception occurs
         //   if (!in_array($user->role, ['admin', 'manager'])) {
            if (!($user->role=='admin')||!($user->role=='manager')) {
                Log::error('Unauthorized');
            }
            // Return false if any error occurs
            return false;
        } catch (Exception $e) {
            // Rollback the transaction if any error occurs
            DB::rollBack();

            // Log an error if an exception occurs
            Log::error('Error: ' . $e->getMessage());

            // Return false if any error occurs
            return false;
        }
    }

    /**
     * Update a task
     *
     * @param array $data Task data
     * @param int $task Task ID
     * @return bool|\Illuminate\Database\Eloquent\Collection The updated task with its dependencies or false if any error occurs
     */
    public function Update(array $data, $task)
    {
        try {
            // Get the authenticated user
            $user = JWTAuth::user();

            // Check if the user has the required role to update a task
           // if (in_array($user->role, ['admin', 'manager'])) {
            if (($user->role=='admin')||($user->role=='manager')) {
                // Find the task to be updated
                $task = Task::findOrFail($task);

                // Update the task
                $task->update($data);

                // Sync dependent tasks if provided
                if (!empty($data['dependent_task_ids']))
                    $task->dependencies()->sync($data['dependent_task_ids']);

                // Check if the task is blocked
                if ($task->dependencies()->where('status', '!=', 'Completed')->exists()) {
                    // Update the status to blocked
                    $task->update(['status' => TaskStatus::Blocked]);

                    // Log the status update
                    $statusupdate = TaskStatusUpdate::create([
                        'task_id' => $task->id,
                        'update_by' => $user->id,
                        'status' => TaskStatus::Blocked
                    ]);
                }
            }

            // Return the updated task with its dependencies
            return $task->load('dependencies');
        } catch (QueryException $e) {
            // Log an error if a database query error occurs
            Log::error('Database query error: ' . $e->getMessage());
            return false;
        } catch (ModelNotFoundException $e) {
            // Log an error if no tasks are found
            Log::error('No tasks found' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            // Log an error if any exception occurs
            Log::error('error' . $e->getMessage());
            return false;
        }
    }
    /**
     * Update the status of the task with the given ID
     *
     * @param array $data The new status of the task
     * @param int $taskId The ID of the task to update
     * @return bool|\App\Models\Task The task with the updated status or false if unauthorized or an error occurs
     */
    public function updateStatus(array $data, $taskId)
    {
        try {
            // Get the authenticated user
            $user = JWTAuth::user();

            // Start a database transaction
            DB::beginTransaction();

            // Find the task to be updated
            $task = Task::findOrFail($taskId);

            // Get the creator and assignee of the task
            $creator = $task->creator;
            $assignee = $task->assignee;

            // Check if the user has the required role to update the task
            if (($user->role == 'admin') || ($creator->id == $user->id) || ($assignee->id == $user->id)) {

                // Check if the task dependencies is not completed
                if ($task->dependencies()->where('status', '!=', TaskStatus::Completed)->whereNull('dependencies_tasks.deleted_at')->exists()) {
                    // Update the task status to blocked
                    $task->status = TaskStatus::Blocked;
                    $task->save();
                } else {
                    // Update the status
                    $task->status = $data['status'];
                    $task->save();

                    // If the task status is completed, update the due date and check dependent tasks
                    if ($data['status'] == 'completed') {
                        $task->due_date = now()->format('Y-m-d');
                        $task->save();

                        // Get the dependent tasks
                        $dependentTasks = $task->dependents;

                        // Loop through the dependent tasks and update their status if blocked and not deleted
                        foreach ($dependentTasks as $dependentTask) {
                            if (($dependentTask->status == TaskStatus::Blocked) && ($dependentTask->deleted_at == null)) {
                                $dependentTask->status = TaskStatus::Open;
                                $dependentTask->save();

                                // Log the status update
                                TaskStatusUpdate::create([
                                    'task_id' => $dependentTask->id,
                                    'update_by' => $user->id,
                                    'status' => 'open'
                                ]);
                            }
                        }
                    }
                }

                // Log the status update
                TaskStatusUpdate::create([
                    'task_id' => $task->id,
                    'update_by' => $user->id,
                    'status' => $task->status
                ]);
            }

            // Commit the transaction
            DB::commit();

            // Return the task with the updated status
            return $task;
        } catch (QueryException $e) {
            // Rollback the transaction if a database query error occurs
            DB::rollBack();
            Log::error('Database query error: ' . $e->getMessage());
            return false;
        } catch (ModelNotFoundException $e) {
            // Rollback the transaction if no tasks are found
            DB::rollBack();
            Log::error('No tasks found: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            // Rollback the transaction if any exception occurs
            DB::rollBack();
            Log::error('Error: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Retrieve a task by its ID
     *
     * @param int $task The task ID
     * @return bool|array The task info
     */
    public function Show($task)
    {
        try {
            // Find the task with the given ID
            $task = Task::findOrFail($task);

            // Get the authenticated user
            $user = JWTAuth::user();

            // Get the creator and assignee of the task
            $creator = $task->creator;
            $assignee = $task->assignee;

            // Check if the user has the required role or if the user is the creator or assignee of the task
            if (($user->role == 'admin') || ($creator->id == $user->id) || ($assignee->id == $user->id)) {
                // Return the task info
                return $task;
            } else {
                // Log an error if the user is not authorized
                Log::error('Unauthorized');
                return false;
            }
        } catch (ModelNotFoundException $e) {
            // Log an error if no tasks are found
            Log::error('No tasks found: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            // Log an error if any exception occurs
            Log::error('error message' . $e->getMessage());
            return false;
        }
    }
    /**
     * Delete a task
     *
     * @param int $task The task ID to be deleted
     * @return bool|string The result of the deletion or false if unauthorized or an error occurs
     */
    public function deleteTask($task)
    {
        try {
            // Find the task with the given ID
            $task = Task::findOrFail($task);

            // Get the authenticated user
            $user = JWTAuth::user();

            // Check if the user has the required role to delete the task
          //  if (in_array($user->role, ['admin', 'manager'])) {
            if (($user->role=='admin')||($user->role=='manager')) {
                // Get the dependent tasks
                $dependentTasks = $task->dependents;

                // Loop through the dependent tasks and update their status
                foreach ($dependentTasks as $dependentTask) {
                    $this->updateStatus([$dependentTask->status], $dependentTask->id);
                }

                // Update the dependencies table to mark the dependent task as deleted
                DB::table('dependencies_tasks')
                    ->where('dependent_task_id', $task->id)
                    ->update(['deleted_at' => now()]);

                // Delete the task status updates
                $task->statuses()->delete();

                // Delete the task
                $task->delete();

                // Return a success message
                return 'Task deleted';
            } else {
                // Log an error if the user is not authorized
                Log::error('Unauthorized');
                return false;
            }
        } catch (ModelNotFoundException $e) {
            // Log an error if no tasks are found
            Log::error('No tasks found: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            // Log an error if any exception occurs
            Log::error('error message' . $e->getMessage());
            return false;
        }
    }
    /**
     * Restore a soft-deleted task
     *
     * @param int $task The task ID to restore
     * @return bool|array The restored task or false if unauthorized or an error occurs
     */
    public function restoreTask($task)
    {
        try {
            // Find the soft-deleted task with the given ID
            $task = Task::onlyTrashed()->findOrFail($task);

            // Get the authenticated user
            $user = JWTAuth::user();

            // Check if the user has the required role to restore the task
           // if (in_array($user->role, ['admin', 'manager'])) {
            if (($user->role=='admin')||($user->role=='manager')) {
                // Restore the task
                $task->restore();

                // Update the status of the restored task
                $this->updateStatus([$task->status], $task->id);

                // Update the dependencies table to mark the dependent task as not deleted
                DB::table('dependencies_tasks')
                    ->where('dependent_task_id', $task->id)
                    ->update(['deleted_at' => null]);

                // Restore the statuses related to the task
                $task->statuses()->restore();

                // Update the status of dependent tasks
                $dependentTasks = $task->dependents;
                foreach ($dependentTasks as $dependentTask) {
                    $this->updateStatus([$dependentTask->status], $dependentTask->id);
                }
            } else {
                // Log an error if the user is not authorized
                Log::error('Unauthorized');
                return false;
            }
            // Return the restored task
            return $task;
        } catch (ModelNotFoundException $e) {
            // Log an error if no tasks are found
            Log::error('No tasks found: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            // Log an error if any exception occurs
            Log::error('error message' . $e->getMessage());
            return false;
        }
    }
    /**
     * Permanently delete a task
     *
     * @param int $task The ID of the task to be permanently deleted
     * @return bool|string The result of the deletion or false if unauthorized or an error occurs
     */
    public function forceDelete($task)
    {
        try {
            // Retrieve the trashed task with the given ID
            $Task = Task::onlyTrashed()->findOrFail($task);

            // Get the authenticated user
            $user = JWTAuth::user();

            // Check if the user has the required role to permanently delete the task
           // if (in_array($user->role, ['admin', 'manager'])) {
            if (($user->role=='admin')||($user->role=='manager')) {
                // Detach all dependents from the task
                $Task->dependents()->detach();

                // Permanently delete all status updates associated with the task
                $Task->statuses()->forceDelete();

                // Permanently delete the task
                $Task->forceDelete();
            } else {
                // Log an error if the user is not authorized
                Log::error('Unauthorized');
                return false;
            }

            // Return a success message
            return 'Task permanently deleted';
        } catch (ModelNotFoundException $e) {
            // Log an error if no tasks are found
            Log::error('No tasks found: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            // Log an error if any exception occurs
            Log::error('error message' . $e->getMessage());
            return false;
        }
    }
    /**
     * Add a comment to a task
     *
     * @param int $task The task ID
     * @param string $comment The comment content
     * @return bool|\App\Models\Comment The created comment or false if unauthorized or an error occurs
     */
    public function AddComment($task, $comment)
    {
        try {
            // Find the task with the given ID
            $task = Task::findOrFail($task);

            // Get the authenticated user
            $user = JWTAuth::user();

            // Check if the user is authorized to add a comment
            if (($user->role == 'admin') || ($task->creator->id == $user->id) || ($task->assignee->id == $user->id)) {
                // Create and attach the comment to the task
                $comment = $task->comments()->create(['comment' => $comment, 'comment_by' => $user->id]);
            } else {
                // Log an error if the user is not authorized
                Log::error('Unauthorized');
                return false;
            }

            // Return the created comment
            return $comment;
        } catch (ModelNotFoundException $e) {
            // Log an error if no tasks are found
            Log::error('No tasks found: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            // Log an error if any exception occurs
            Log::error('error message' . $e->getMessage());
            return false;
        }
    }
    /**
     * Retrieve all tasks with status 'blocked'
     *
     * @return bool|\Illuminate\Database\Eloquent\Collection The blocked tasks or false if unauthorized or an error occurs
     */
    public function blockedTasks()
    {
        try {
            // Get the authenticated user
            $user = JWTAuth::user();

            // Create a query to select tasks based on the user role
            $tasks = Task::select(
                'title',
                'description',
                'type',
                'status',
                'priority',
                'due_date',
                'assigned_to',
                'created_by'
            );

            // If the user is an admin or manager, retrieve all tasks with status 'blocked'
           // if (in_array($user->role, ['admin', 'manager'])) {
            if (($user->role=='admin')||($user->role=='manager')) {
                $tasks = $tasks->ByStatus('blocked');
            } else {
                // Log an error if the user is not authorized
                Log::error('Unauthorized');
                return false;
            }

            // Return the blocked tasks
            return $tasks->get();
        } catch (Exception $e) {
            // Log an error if any exception occurs
            Log::error('error message' . $e->getMessage());
            return false;
        }
    }
}
