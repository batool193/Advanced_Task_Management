<?php

namespace App\Http\Controllers;

use App\Services\AttachementService;
use App\Services\TaskService;
use App\Http\Requests\task\StoreTaskRequest;
use App\Http\Requests\task\UpdateTaskRequest;
use App\Http\Requests\task\UpdateTaskStatusRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected $taskservice;
    /**
     * StudentController constructor
     *
     * @param TaskService $taskservice
     */
    public function __construct(TaskService $taskservice)
    {
        $this->taskservice = $taskservice;
    }

    public function index(Request $request)
    {
        // Get paginated list of tasks
        $result = $this->taskservice->All($request)->paginate();

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }
        // Return the paginated list
        return $this->paginated($result);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTaskRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTaskRequest $request)
    {
        // Validate the request input
        $validatedInput = $request->validated();

        // Create a new task in the database
        $result = $this->taskservice->Create($validatedInput);

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the created task
        return $this->success($result);
    }
    /**
     * Assign a task to a user.
     *
     * @param int $task
     * @param int $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignToUser($task, $user)
    {
        // Assign the task to a user
        $result = $this->taskservice->assignToUser($task, $user);

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the assigned task
        return $this->success($result);
    }

    /**
     * Update a task's status.
     *
     * @param UpdateTaskRequest $request
     * @param int $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateTaskRequest $request, $task)
    {
        // Validate the request input
        $validatedInput = $request->validated();

        // Update the task's status in the database
        $result = $this->taskservice->Update($validatedInput, $task);

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the updated task
        return $this->success($result);
    }
    /**
     * Update the status of a task.
     *
     * @param UpdateTaskStatusRequest $request
     * @param int $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function UpdateStatus(UpdateTaskStatusRequest $request, $task)
    {
        // Validate the request input
        $validatedInput = $request->validated();

        // Update the status of the task in the database
        $result = $this->taskservice->UpdateStatus($validatedInput, $task);

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the updated task
        return $this->success($result);
    }
    /**
     * Display the specified resource.
     *
     * Shows the specified customer's details.
     *
     * @param integer $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($task)
    {
        // Get the task
        $result = $this->taskservice->Show($task);

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the task
        return $this->success($result);
    }
    /**
     * Remove the specified resource from storage.
     *
     * Deletes the specified task from the database.
     *
     * @param int $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTask($task)
    {
        // Delete the task
        $result = $this->taskservice->deleteTask($task);

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the customer
        return $this->success($result);
    }
    /**
     * Restore a soft deleted task.
     *
     * Retrieves a soft deleted task from the database.
     *
     * @param int $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreTask($task)
    {
        // Get the task
        $result = $this->taskservice->restoreTask($task);

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the task
        return $this->success($result);
    }
    /**
     * Permanently delete a task from the database.
     *
     * @param int $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($task)
    {
        // Delete the task permanently
        $result = $this->taskservice->forceDelete($task);

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the success response
        return $this->success($result);
    }
    /**
     * Add a comment to a task.
     *
     * Adds a comment to the specified task in the database.
     *
     * @param int $task
     * @param string $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function AddComment($task, $comment)
    {
        // Get the task
        $result = $this->taskservice->AddComment($task, $comment);

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the task
        return $this->success($result);
    }
    /**
     * Add an attachment to a task.
     *
     * Adds an attachment to the specified task in the database.
     *
     * @param int $task
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function AddAttachement($task, Request $request)
    {
        // Get the task
        $task = Task::findOrFail($task); // Ensure $task is an object

        // Create an instance of the attachment service
        $attachmentService = new AttachementService();

        // Store the attachment
        $url = $attachmentService->storeAttachement($task, $request);

        // If the attachment was stored successfully, return the URL
        if ($url) {
            return response()->json(['url' => $url], 200);
        } else {
            // If there was an error, return an error response
            return response()->json(['error' => 'Failed to upload attachment'], 500);
        }
    }
    /**
     * Gets all the tasks that are blocked
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function blockedTasks()
    {
        // Get all the tasks that are blocked
        $result = $this->taskservice->blockedTasks();

        // If there is an error in the service, return an error response
        if (!$result) {
            return $this->error();
        }

        // Return the tasks
        return $this->success($result);
    }
}
