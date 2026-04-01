<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully.',
            'data'    => $task,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'done'])],
        ]);

        $tasks = Task::query()
            ->filterByStatus($request->query('status'))
            ->sortByPriorityAndDate()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $tasks->isEmpty() ? [] : $tasks,
        ]);
    }

    public function update(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully.',
            'data'    => $task->fresh(),
        ]);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $newStatus = $request->validated()['status'];

        if (!$task->canTransitionTo($newStatus)) {
            $allowed = Task::STATUS_TRANSITIONS[$task->status] ?? null;

            return response()->json([
                'success' => false,
                'message' => $allowed
                    ? "Invalid transition. '{$task->status}' can only move to '{$allowed}'."
                    : "Task is already '{$task->status}' and cannot be updated further.",
            ], 422);
        }

        $task->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully.',
            'data'    => $task->fresh(),
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        if ($task->status !== 'done') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Only tasks with status "done" can be deleted.',
            ], 403);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully.',
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date', 'date_format:Y-m-d'],
        ]);

        $date = $request->query('date');

        $tasks = Task::whereDate('due_date', $date)->get();

        $priorities = ['high', 'medium', 'low'];
        $statuses   = ['pending', 'in_progress', 'done'];

        $summary = array_fill_keys($priorities, array_fill_keys($statuses, 0));

        foreach ($tasks as $task) {
            $summary[$task->priority][$task->status]++;
        }

        return response()->json([
            'success' => true,
            'date'    => $date,
            'summary' => $summary,
        ]);
    }
}