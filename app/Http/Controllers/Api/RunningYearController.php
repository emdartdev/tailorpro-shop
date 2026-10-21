<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RunningYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RunningYearController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(RunningYear::orderBy('year_label', 'desc')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year_label' => ['required', 'string', 'unique:running_years,year_label'],
        ]);

        $year = RunningYear::create($validated);

        return response()->json($year, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $year = RunningYear::findOrFail($id);

        $validated = $request->validate([
            'year_label' => ['required', 'string', 'unique:running_years,year_label,' . $id],
        ]);

        $oldLabel = $year->year_label;
        $newLabel = trim($validated['year_label']);

        $year->update([
            'year_label' => $newLabel,
        ]);

        if ($oldLabel !== $newLabel) {
            Order::where('running_year', $oldLabel)->update(['running_year' => $newLabel]);
        }

        return response()->json($year);
    }

    public function destroy(string $id): JsonResponse
    {
        $year = RunningYear::findOrFail($id);
        $year->delete();

        return response()->json(['message' => 'Running year deleted successfully.']);
    }
}

