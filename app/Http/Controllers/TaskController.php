<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'in:todo,in_progress,done'],
        ]);
        $tasks = $this->ownedTasks()
            ->search($filters['q'] ?? null)
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->latest()->paginate(8)->withQueryString();
        return view('tasks.index', compact('tasks', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        Task::create($this->validated($request) + ['user_id' => Auth::id()]);
        return to_route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->ownedTaskOrAbort($task)->update($this->validated($request));
        return to_route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->ownedTaskOrAbort($task)->delete();
        return to_route('tasks.index')->with('success', 'Task deleted.');
    }

    private function ownedTasks()
    {
        return Task::query()->where('user_id', Auth::id());
    }

    private function ownedTaskOrAbort(Task $task): Task
    {
        abort_unless($task->user_id === Auth::id(), 404);
        return $task;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:todo,in_progress,done'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ]);
    }
}
