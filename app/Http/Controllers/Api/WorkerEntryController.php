<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkerEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerEntryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkerEntry::with('worker');

        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->input('worker_id'));
        }

        if ($request->filled('from_date')) {
            $query->where('entry_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('entry_date', '<=', $request->input('to_date'));
        }

        $sortBy = $request->input('sort_by', 'date_serial');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'serial') {
            $query->orderBy('serial', $sortDir)->orderBy('entry_date', $sortDir);
        } elseif ($sortBy === 'date') {
            $query->orderBy('entry_date', $sortDir)->orderBy('serial', 'asc');
        } else {
            // Default: date-wise then serial-wise sorted
            $query->orderBy('entry_date', $sortDir)->orderBy('serial', 'asc');
        }

        $entries = $query->get();

        return response()->json(['data' => $entries]);
    }

    public function bulkUpsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'worker_id' => ['required', 'uuid', 'exists:workers,id'],
            'entries' => ['required', 'array'],
            'entries.*.id' => ['nullable', 'string'],
            'entries.*.entry_date' => ['nullable', 'date'],
            'entries.*.khoroch_date' => ['nullable', 'date'],
            'entries.*.serial' => ['nullable', 'integer'],
            'entries.*.name' => ['nullable', 'string'],
            'entries.*.item' => ['nullable', 'string'],
            'entries.*.quantity' => ['nullable', 'integer'],
            'entries.*.unit_price' => ['nullable', 'numeric'],
            'entries.*.total_taka' => ['nullable', 'numeric'],
            'entries.*.khoroch' => ['nullable', 'numeric'],
        ]);

        $workerId = $validated['worker_id'];

        $results = \Illuminate\Support\Facades\DB::transaction(function () use ($workerId, $validated) {
            $saved = [];

            foreach ($validated['entries'] as $item) {
                $id = $item['id'] ?? null;
                $serial = isset($item['serial']) && $item['serial'] !== '' ? intval($item['serial']) : 0;
                $name = !empty($item['name']) ? trim($item['name']) : null;
                $itemName = !empty($item['item']) ? trim($item['item']) : null;
                $qty = isset($item['quantity']) ? intval($item['quantity']) : 1;
                $unitPrice = isset($item['unit_price']) ? floatval($item['unit_price']) : 0;
                $totalTaka = isset($item['total_taka']) ? floatval($item['total_taka']) : ($qty * $unitPrice);
                $khoroch = isset($item['khoroch']) ? floatval($item['khoroch']) : 0;

                // Skip completely blank rows that have neither work nor expense
                $isBlank = empty($name) && empty($itemName) && empty($serial) && $totalTaka == 0 && $khoroch == 0;
                $isUuid = !empty($id) && \Illuminate\Support\Str::isUuid($id);

                if ($isBlank && !$isUuid) {
                    continue;
                }

                $data = [
                    'worker_id' => $workerId,
                    'entry_date' => !empty($item['entry_date']) ? $item['entry_date'] : now()->toDateString(),
                    'khoroch_date' => $item['khoroch_date'] ?? null,
                    'serial' => $serial,
                    'name' => $name,
                    'item' => $itemName,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_taka' => $totalTaka,
                    'khoroch' => $khoroch,
                ];

                if ($isUuid && WorkerEntry::where('id', $id)->exists()) {
                    $entry = WorkerEntry::find($id);
                    $entry->update($data);
                    $saved[] = $entry;
                } else {
                    if ($isUuid) {
                        $data['id'] = $id;
                    }
                    $entry = WorkerEntry::create($data);
                    $saved[] = $entry;
                }
            }

            return $saved;
        });

        return response()->json(['message' => 'Entries saved successfully.', 'data' => $results]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'worker_id' => ['required', 'uuid', 'exists:workers,id'],
            'entry_date' => ['nullable', 'date'],
            'khoroch_date' => ['nullable', 'date'],
            'serial' => ['nullable', 'integer'],
            'name' => ['nullable', 'string'],
            'item' => ['nullable', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'total_taka' => ['required', 'numeric', 'min:0'],
            'khoroch' => ['nullable', 'numeric', 'min:0'],
        ]);

        $entry = WorkerEntry::create($validated);

        return response()->json($entry, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $entry = WorkerEntry::findOrFail($id);
        $validated = $request->validate([
            'entry_date' => ['nullable', 'date'],
            'khoroch_date' => ['nullable', 'date'],
            'serial' => ['nullable', 'integer'],
            'name' => ['nullable', 'string'],
            'item' => ['nullable', 'string'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'unit_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'total_taka' => ['sometimes', 'required', 'numeric', 'min:0'],
            'khoroch' => ['nullable', 'numeric', 'min:0'],
        ]);

        $entry->update($validated);

        return response()->json($entry);
    }

    public function destroy(string $id): JsonResponse
    {
        $entry = WorkerEntry::findOrFail($id);
        $entry->delete();

        return response()->json(['message' => 'Worker entry deleted successfully.']);
    }
}
