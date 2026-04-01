<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Task report
Route::get('/tasks/report', [TaskController::class, 'report']);

// Task API resource routes
Route::apiResource('tasks', TaskController::class)->only([
    'index',   // GET    /api/tasks
    'store',   // POST   /api/tasks
    'update',  // PUT    /api/tasks/{task}  ← edit title/due_date/priority
    'destroy', // DELETE /api/tasks/{task}
]);

// Update task status
Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);