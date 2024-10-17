<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Enums\TaskPriorty;
use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'priority',
        'due_date',
        'assigned_to',
        'created_by'
    ];
    protected $casts = [
        'type' => TaskType::class,
        'status' => TaskStatus::class,
        'priority' => TaskPriorty::class,
    ];
    /**
     * Get the user who created the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user to whom the task is assigned.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all status updates for the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses()
    {
        return $this->hasMany(TaskStatusUpdate::class, 'task_id');
    }

    /**
     * Define the relationship for tasks that this task depends on.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'dependencies_tasks', 'task_id', 'dependent_task_id');
    }


    /**
     *  Define the relationship for tasks that depend on this task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function dependents()
    {
        return $this->belongsToMany(Task::class, 'dependencies_tasks', 'dependent_task_id', 'task_id');
    }
    /**
     * Get all comments for the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    /**
     * Get all attachments for the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }
    /**
     * Scope a query to only include tasks of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType(Builder $query, $type): Builder
    {
        return $query->where('type', $type);
    }
    /**
     * Scope a query to only include tasks of a given status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus(Builder $query, $status): Builder
    {
        return $query->where('status', $status);
    }
    /**
     * Scope a query to only include tasks of a given priority.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $priority
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPriority(Builder $query, $priority): Builder
    {
        return $query->where('priority', $priority);
    }
    /**
     * Scope a query to only include tasks of a given due date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $due_date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDueDate(Builder $query, $due_date): Builder
    {
        return $query->where('due_date', $due_date);
    }
    /**
     * Scope a query to only include tasks of a given assigned user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $assigned_to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAssignedTo(Builder $query, $assigned_to): Builder
    {
        return $query->where('assigned_to', $assigned_to);
    }
}
