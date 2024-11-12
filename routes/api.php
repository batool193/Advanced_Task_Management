<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\TaskStatusUpdateController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\SecurityMiddleware;

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::middleware(['auth:api',SecurityMiddleware::class])->group(function () {

   Route::get('tasks/blocked', [TaskController::class, 'blockedTasks'])->middleware([CheckRole::class.':admin']);



    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);

    Route::post('tasks/{task}/comment/{comment}', [TaskController::class, 'AddComment']);
    Route::post('tasks/{task}/attachment', [TaskController::class, 'AddAttachement']);
    Route::post('attachments/{task}', [AttachmentController::class, 'store']);
    Route::put('tasks/{task}/status', [TaskController::class, 'UpdateStatus']);
    Route::post('tasks/{task}/assign/{user}', [TaskController::class, 'assignToUser']);

    Route::middleware([CheckRole::class.':admin,manager'])->group(function () {
        Route::delete('tasks/{task}/deletetask', [TaskController::class, 'deleteTask']);
        Route::get('daily-report', [TaskStatusUpdateController::class, 'dailyReport']);
        Route::post('tasks', [TaskController::class, 'store']);
       });
    Route::middleware([CheckRole::class.':admin'])->group(function () {
        Route::delete('tasks/{task}', [TaskController::class, 'destroy']);
        Route::post('tasks/{task}/restore', [TaskController::class, 'restoreTask']);
        Route::apiResource('users',UserController::class);
    });

});
