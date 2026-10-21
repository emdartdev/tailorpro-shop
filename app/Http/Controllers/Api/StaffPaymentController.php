<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaffPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffPaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StaffPayment::with('worker');

        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->input('worker_id'));
        }

        $payments = $query->orderByDesc('payment_date')->get();

        return response()->json(['data' => $payments]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'worker_id' => ['required', 'uuid', 'exists:workers,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $payment = StaffPayment::create([
            'worker_id' => $validated['worker_id'],
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'] ?? now()->toDateString(),
            'note' => $validated['note'] ?? '',
            'created_at' => now(),
        ]);

        return response()->json($payment, 201);
    }

    public function destroy(string $id): JsonResponse
    {
        $payment = StaffPayment::findOrFail($id);
        $payment->delete();

        return response()->json(['message' => 'Staff payment deleted successfully.']);
    }
}
