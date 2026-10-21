<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('bd_number', 'like', "%{$search}%");
            });
        }

        $orderBy = $request->input('order_by', 'created_at');
        $orderDir = $request->input('order_dir', 'desc');

        if (in_array($orderBy, ['name', 'created_at', 'total_orders', 'total_spent', 'due_amount'])) {
            $customers = $query->orderBy($orderBy, $orderDir === 'asc' ? 'asc' : 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $customers = $query->orderByDesc('created_at')->get();
        }

        return response()->json(['data' => $customers]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'bd_number' => ['required', 'string', 'max:100'],
        ]);

        $customer = Customer::create($validated);

        return response()->json(['data' => $customer], 201);
    }

    public function show(string $id): JsonResponse
    {
        $customer = Customer::with(['orders.items', 'orders.paymentHistory'])->findOrFail($id);

        return response()->json(['data' => $customer]);
    }

    public function history(string $id): JsonResponse
    {
        $orders = \App\Models\Order::where('customer_id', $id)
            ->with(['items', 'paymentHistory'])
            ->orderByDesc('order_date')
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:50'],
            'bd_number' => ['sometimes', 'required', 'string', 'max:100'],
        ]);

        $customer->update($validated);

        return response()->json(['data' => $customer]);
    }

    public function destroy(string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        if ($customer->orders()->exists()) {
            return response()->json([
                'error' => 'Cannot delete customer with existing orders. To protect financial and historical records, orders must be reassigned or deleted first.',
                'message' => 'Cannot delete customer with existing orders.',
            ], 400);
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully.']);
    }
}
