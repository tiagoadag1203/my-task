<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

    Route::middleware('role:user')->group(function (): void {
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

        Route::get('/projects/{project}/sprints', [SprintController::class, 'index']);
        Route::post('/projects/{project}/sprints', [SprintController::class, 'store']);
        Route::get('/sprints/{sprint}', [SprintController::class, 'show']);
        Route::put('/sprints/{sprint}', [SprintController::class, 'update']);
        Route::delete('/sprints/{sprint}', [SprintController::class, 'destroy']);
        Route::put('/sprints/{sprint}/status', [SprintController::class, 'updateStatus']);

        Route::get('/kanban/tasks', [TaskController::class, 'kanbanIndex']);
        Route::post('/kanban/tasks', [TaskController::class, 'kanbanStore']);
        Route::get('/projects/{project}/backlog', [TaskController::class, 'backlogIndex']);
        Route::post('/projects/{project}/backlog', [TaskController::class, 'backlogStore']);
        Route::get('/sprints/{sprint}/tasks', [TaskController::class, 'sprintTasks']);
        Route::put('/tasks/{task}', [TaskController::class, 'update']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
        Route::put('/tasks/{task}/migrate', [TaskController::class, 'migrate']);
    });

    Route::prefix('admin')->middleware('role:admin')->group(function (): void {
        Route::get('/summary', [DashboardController::class, 'summary']);
        Route::get('/analytics', [DashboardController::class, 'analytics']);
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::put('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword']);
        Route::get('/export', [DashboardController::class, 'export']);
    });
});
