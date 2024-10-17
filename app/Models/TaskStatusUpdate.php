<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class TaskStatusUpdate extends Model
{
    /** @use HasFactory<\Database\Factories\TaskStatusUpdateFactory> */
    use HasFactory,SoftDeletes;
    protected $fillable = ['task_id','update_by','status'];
    protected $casts = [
        'type' => TaskStatus::class,];

    /**
     * Relation between TaskStatusUpdate and Task
     * @return BelongsTo
     */
    public function tasks()
    {
        return $this->belongsTo(Task::class);
    }
    /**
     * Relation between TaskStatusUpdate and User
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class,'update_by');
    }

}
