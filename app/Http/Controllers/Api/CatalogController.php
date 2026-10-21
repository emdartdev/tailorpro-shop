<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AirmanItemCatalog;
use App\Models\ItemCatalog;
use App\Models\WorkerItemCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->input('type', 'officer');
        if ($type === 'airman') {
            return response()->json(['data' => AirmanItemCatalog::orderBy('sort_order')->get()]);
        }
        if ($type === 'worker') {
            return response()->json(['data' => WorkerItemCatalog::orderBy('sort_order')->get()]);
        }
        return response()->json(['data' => ItemCatalog::orderBy('sort_order')->get()]);
    }

    public function getOfficer(): JsonResponse
    {
        return response()->json(['data' => ItemCatalog::orderBy('sort_order')->get()]);
    }

    public function storeOfficer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $item = ItemCatalog::create($validated);

        return response()->json($item, 201);
    }

    public function getAirman(): JsonResponse
    {
        return response()->json(['data' => AirmanItemCatalog::orderBy('sort_order')->get()]);
    }

    public function storeAirman(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $item = AirmanItemCatalog::create($validated);

        return response()->json(['data' => $item], 201);
    }

    public function getWorker(): JsonResponse
    {
        return response()->json(['data' => WorkerItemCatalog::orderBy('sort_order')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $type = $request->input('type', 'officer');
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($type === 'airman') {
            $item = AirmanItemCatalog::create($validated);
        } elseif ($type === 'worker') {
            $item = WorkerItemCatalog::create($validated);
        } else {
            $item = ItemCatalog::create($validated);
        }

        return response()->json($item, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $type = $request->input('type', 'officer');
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($type === 'airman') {
            $item = AirmanItemCatalog::findOrFail($id);
        } elseif ($type === 'worker') {
            $item = WorkerItemCatalog::findOrFail($id);
        } else {
            $item = ItemCatalog::findOrFail($id);
        }

        $item->update($validated);

        return response()->json($item);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $type = $request->input('type', 'officer');

        if ($type === 'airman') {
            $item = AirmanItemCatalog::findOrFail($id);
        } elseif ($type === 'worker') {
            $item = WorkerItemCatalog::findOrFail($id);
        } else {
            $item = ItemCatalog::findOrFail($id);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }

    public function storeWorker(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $item = WorkerItemCatalog::create($validated);

        return response()->json($item, 201);
    }
}
