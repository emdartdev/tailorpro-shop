<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'active');
        $query = Worker::query();

        if ($status === 'archived') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        } else {
            // 'active' is default for Eloquent SoftDeletes
        }

        $workers = $query->orderBy('created_at')->get();

        return response()->json([
            'data' => $workers,
            'active_count' => Worker::count(),
            'archived_count' => Worker::onlyTrashed()->count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_bn' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['nullable', 'string', 'max:100'],
        ]);

        $worker = Worker::create($validated);

        return response()->json(['data' => $worker], 201);
    }

    public function show(string $id): JsonResponse
    {
        $worker = Worker::withTrashed()->with(['entries', 'staffPayments'])->findOrFail($id);

        return response()->json(['data' => $worker]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $worker = Worker::withTrashed()->findOrFail($id);
        $validated = $request->validate([
            'name_bn' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $worker->update($validated);

        return response()->json($worker);
    }

    public function destroy(string $id): JsonResponse
    {
        $worker = Worker::findOrFail($id);
        // Soft delete: sets deleted_at so all worker_entries & payments remain 100% saved in database
        $worker->delete();

        return response()->json([
            'message' => 'Worker archived successfully. Work history and monthly sheets are safely preserved.',
        ]);
    }

    public function restore(string $id): JsonResponse
    {
        $worker = Worker::onlyTrashed()->findOrFail($id);
        $worker->restore();

        return response()->json([
            'message' => 'Worker restored successfully.',
            'data' => $worker,
        ]);
    }
}
