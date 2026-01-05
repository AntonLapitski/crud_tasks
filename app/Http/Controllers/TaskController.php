<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $size = (int) $request->input('size', 10); // Tamaño de paginación
        $sort = $request->input('sort', 'created_at'); // Campo y dirección de orden
        $sort_desc = str_starts_with($sort, '-') ? 'desc' : 'asc'; // Verifica si es descendente
        $sort_field = ltrim($sort, '-'); // Remover el "-" para obtener el campo
        $completed = $request->boolean('completed');

        $tasks = Task::query()
            ->when($search, fn($query) => $query->where('description', 'like', "%{$search}%"))
            ->when(!is_null($request->input('completed')), fn($query) => $query->where('completed', $completed))
            ->orderBy($sort_field, $sort_desc)
            ->paginate($size);

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'title' => 'required|string'
            'completed' => 'required|boolean',
        ]);

        $task = Task::create($request->all());

        return response()->json($task, 201);
    }

    public function show($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $request->validate([
            'description' => 'required|string',
            'title' => 'required|string',
            'completed' => 'required|boolean',
        ]);

        $task->update($request->all());

        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
}
