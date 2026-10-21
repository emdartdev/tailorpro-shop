<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentHistory;
use App\Services\FinancialReconciliationService;
use App\Services\OrderSequenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        protected OrderSequenceService $sequenceService,
        protected FinancialReconciliationService $financialService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['customer', 'items', 'paymentHistory']);

        if ($request->filled('category')) {
            $query->where('order_category', $request->input('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('running_year')) {
            $query->where('running_year', $request->input('running_year'));
        }

        $orders = $query->orderByDesc('created_at')->get();

        return response()->json(['data' => $orders]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string'],
            'customer_phone' => ['nullable', 'string'],
            'bd_number' => ['required', 'string'],
            'order_date' => ['nullable', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'advance' => ['nullable', 'numeric', 'min:0'],
            'due' => ['required', 'numeric', 'min:0'],
            'measurement_notes' => ['nullable', 'string'],
            'extra_notes' => ['nullable', 'string'],
            'order_category' => ['nullable', 'string', 'in:officer,airman'],
            'branch' => ['nullable', 'string'],
            'ribon' => ['nullable', 'integer'],
            'running_year' => ['nullable', 'string'],
            'trade' => ['nullable', 'string'],
            'office' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'string'],
            'items.*.item_name' => ['nullable', 'string'],
            'items.*.measurements' => ['nullable', 'array'],
            'items.*.notes' => ['nullable', 'string'],
            'items.*.iv_number' => ['nullable', 'string'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $category = $validated['order_category'] ?? 'officer';
            $runningYear = $validated['running_year'] ?? null;
            $nextNumber = $this->sequenceService->getNextOrderNumber($category, $runningYear);

            // Resolve or create customer if needed
            $customerId = $validated['customer_id'] ?? null;
            if (! $customerId && ! empty($validated['bd_number'])) {
                $customer = Customer::firstOrCreate(
                    ['bd_number' => $validated['bd_number']],
                    [
                        'name' => $validated['customer_name'] ?? 'Unknown',
                        'phone' => $validated['customer_phone'] ?? '-',
                    ]
                );
                $customerId = $customer->id;
            }

            $order = Order::create([
                'order_number' => $nextNumber,
                'customer_id' => $customerId,
                'bd_number' => $validated['bd_number'],
                'order_date' => $validated['order_date'] ?? now()->toDateString(),
                'delivery_date' => $validated['delivery_date'] ?? null,
                'total_amount' => $validated['total_amount'],
                'advance' => $validated['advance'] ?? 0,
                'due' => $validated['due'],
                'measurement_notes' => $validated['measurement_notes'] ?? null,
                'extra_notes' => $validated['extra_notes'] ?? null,
                'status' => ($validated['due'] <= 0 && ($validated['advance'] ?? 0) > 0) ? OrderStatus::DELIVERED : OrderStatus::PENDING,
                'order_category' => $category,
                'branch' => $validated['branch'] ?? null,
                'ribon' => $validated['ribon'] ?? null,
                'running_year' => $runningYear,
                'trade' => $validated['trade'] ?? null,
                'office' => $validated['office'] ?? null,
            ]);

            // Save items
            foreach ($validated['items'] as $itemData) {
                $order->items()->create([
                    'item_type' => $itemData['item_type'],
                    'item_name' => $itemData['item_name'] ?? null,
                    'measurements' => $itemData['measurements'] ?? [],
                    'notes' => $itemData['notes'] ?? null,
                    'iv_number' => $itemData['iv_number'] ?? null,
                ]);
            }

            // Save advance payment history if > 0
            if (! empty($validated['advance']) && $validated['advance'] > 0) {
                PaymentHistory::create([
                    'order_id' => $order->id,
                    'amount' => $validated['advance'],
                    'payment_type' => 'advance',
                    'note' => 'Advance payment at order creation',
                    'created_at' => now(),
                ]);
            }

            if ($customerId) {
                $this->financialService->recalculateCustomerDue($customerId);
            }

            return $order->load(['customer', 'items', 'paymentHistory']);
        });

        return response()->json($order, 201);
    }

    public function nextNumber(Request $request): JsonResponse
    {
        $category = $request->input('category', 'officer');
        $runningYear = $request->input('running_year');
        $next = $this->sequenceService->getNextOrderNumber($category, $runningYear);

        return response()->json([
            'next_number' => $next,
            'data' => ['next_number' => $next],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $query = Order::with(['customer', 'items', 'paymentHistory']);

        if (is_numeric($id)) {
            $query->where(function ($q) use ($id) {
                $q->where('order_number', (int) $id)
                  ->orWhere('id', $id);
            });
        } else {
            $query->where('id', $id);
        }

        $order = $query->firstOrFail();

        return response()->json(['data' => $order]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'customer_id' => ['nullable', 'string'],
            'customer_name' => ['nullable', 'string'],
            'customer_phone' => ['nullable', 'string'],
            'bd_number' => ['sometimes', 'nullable', 'string'],
            'order_date' => ['nullable', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'total_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'advance' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'due' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'measurement_notes' => ['nullable', 'string'],
            'extra_notes' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:pending,delivered,due_payment'],
            'delivered_date' => ['nullable', 'date'],
            'receiver_name' => ['nullable', 'string'],
            'receiver_phone' => ['nullable', 'string'],
            'order_category' => ['nullable', 'string', 'in:officer,airman'],
            'branch' => ['nullable', 'string'],
            'ribon' => ['nullable', 'integer'],
            'running_year' => ['nullable', 'string'],
            'trade' => ['nullable', 'string'],
            'office' => ['nullable', 'string'],
            'downloaded_categories' => ['nullable', 'array'],
            'downloaded_categories.*' => ['string'],
            'items' => ['sometimes', 'array'],
            'items.*.item_type' => ['sometimes', 'nullable', 'string'],
            'items.*.item_name' => ['nullable', 'string'],
            'items.*.measurements' => ['nullable', 'array'],
            'items.*.notes' => ['nullable', 'string'],
            'items.*.iv_number' => ['nullable', 'string'],
            'items.*.quantity' => ['nullable', 'numeric'],
            'items.*.price' => ['nullable', 'numeric'],
        ]);

        $updatedOrder = DB::transaction(function () use ($order, $validated, $request) {
            $oldCustomerId = $order->customer_id;
            $customerId = $validated['customer_id'] ?? $order->customer_id;

            // Sync or update customer if details provided
            if (!empty($validated['customer_name']) || !empty($validated['customer_phone']) || !empty($validated['bd_number'])) {
                if ($customerId) {
                    $customer = Customer::find($customerId);
                    if ($customer) {
                        $cData = [];
                        if (!empty($validated['customer_name'])) {
                            $cData['name'] = $validated['customer_name'];
                        }
                        if (isset($validated['customer_phone'])) {
                            $cData['phone'] = $validated['customer_phone'];
                        }
                        if (!empty($validated['bd_number'])) {
                            $cData['bd_number'] = $validated['bd_number'];
                        }
                        $customer->update($cData);
                    }
                } elseif (!empty($validated['bd_number'])) {
                    $customer = Customer::firstOrCreate(
                        ['bd_number' => $validated['bd_number']],
                        [
                            'name' => $validated['customer_name'] ?? 'Unknown',
                            'phone' => $validated['customer_phone'] ?? '-',
                        ]
                    );
                    $customerId = $customer->id;
                }
            }

            // Prepare order attributes
            $orderAttributes = [];
            if ($customerId) {
                $orderAttributes['customer_id'] = $customerId;
            }
            if (isset($validated['bd_number'])) {
                $orderAttributes['bd_number'] = $validated['bd_number'];
            }
            if (isset($validated['order_date'])) {
                $orderAttributes['order_date'] = $validated['order_date'];
            }
            if (array_key_exists('delivery_date', $validated)) {
                $orderAttributes['delivery_date'] = $validated['delivery_date'];
            }
            if (isset($validated['total_amount'])) {
                $orderAttributes['total_amount'] = $validated['total_amount'];
            }
            if (isset($validated['advance'])) {
                $orderAttributes['advance'] = $validated['advance'];
            }
            if (isset($validated['due'])) {
                $orderAttributes['due'] = $validated['due'];
            }
            if (array_key_exists('measurement_notes', $validated)) {
                $orderAttributes['measurement_notes'] = $validated['measurement_notes'];
            }
            if (array_key_exists('extra_notes', $validated)) {
                $orderAttributes['extra_notes'] = $validated['extra_notes'];
            }
            if (isset($validated['status'])) {
                $orderAttributes['status'] = $validated['status'];
            }
            if (array_key_exists('delivered_date', $validated)) {
                $orderAttributes['delivered_date'] = $validated['delivered_date'];
            }
            if (array_key_exists('receiver_name', $validated)) {
                $orderAttributes['receiver_name'] = $validated['receiver_name'];
            }
            if (array_key_exists('receiver_phone', $validated)) {
                $orderAttributes['receiver_phone'] = $validated['receiver_phone'];
            }
            if (isset($validated['order_category'])) {
                $orderAttributes['order_category'] = $validated['order_category'];
            }
            if (array_key_exists('branch', $validated)) {
                $orderAttributes['branch'] = $validated['branch'];
            }
            if (array_key_exists('ribon', $validated)) {
                $orderAttributes['ribon'] = $validated['ribon'];
            }
            if (array_key_exists('running_year', $validated)) {
                $orderAttributes['running_year'] = $validated['running_year'];
            }
            if (array_key_exists('trade', $validated)) {
                $orderAttributes['trade'] = $validated['trade'];
            }
            if (array_key_exists('office', $validated)) {
                $orderAttributes['office'] = $validated['office'];
            }
            if (isset($validated['downloaded_categories'])) {
                $orderAttributes['downloaded_categories'] = $validated['downloaded_categories'];
            }

            $oldAdvance = (float) $order->advance;
            $order->update($orderAttributes);

            // Re-sync items if provided in request
            if ($request->has('items') && is_array($validated['items'])) {
                $order->items()->delete();
                foreach ($validated['items'] as $itemData) {
                    $order->items()->create([
                        'item_type' => $itemData['item_type'] ?? 'shirt',
                        'item_name' => $itemData['item_name'] ?? null,
                        'measurements' => $itemData['measurements'] ?? [],
                        'notes' => $itemData['notes'] ?? null,
                        'iv_number' => $itemData['iv_number'] ?? null,
                    ]);
                }
            }

            // Sync payment history for advance
            if (isset($validated['advance'])) {
                $newAdvance = (float) $validated['advance'];
                if ($newAdvance !== $oldAdvance) {
                    $advRecord = PaymentHistory::where('order_id', $order->id)
                        ->where('payment_type', 'advance')
                        ->first();
                    if ($newAdvance > 0) {
                        if ($advRecord) {
                            $advRecord->update(['amount' => $newAdvance]);
                        } else {
                            PaymentHistory::create([
                                'order_id' => $order->id,
                                'amount' => $newAdvance,
                                'payment_type' => 'advance',
                                'note' => 'Advance payment',
                                'created_at' => now(),
                            ]);
                        }
                    } elseif ($advRecord) {
                        $advRecord->delete();
                    }
                }
            }

            // Recalculate customer due
            if ($order->customer_id) {
                $this->financialService->recalculateCustomerDue($order->customer_id);
            }
            if ($oldCustomerId && $oldCustomerId !== $order->customer_id) {
                $this->financialService->recalculateCustomerDue($oldCustomerId);
            }

            return $order->load(['customer', 'items', 'paymentHistory']);
        });

        return response()->json($updatedOrder);
    }

    public function deliver(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'delivered_date' => ['nullable', 'date'],
            'receiver_name' => ['nullable', 'string'],
            'receiver_phone' => ['nullable', 'string'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'cash_receive' => ['nullable', 'numeric', 'min:0'],
            'due' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cash = (float) ($validated['cash_receive'] ?? 0);
        $discount = (float) ($validated['discount'] ?? 0);

        $deliveredOrder = DB::transaction(function () use ($order, $validated, $cash, $discount) {
            $newDue = isset($validated['due'])
                ? (float) $validated['due']
                : max(0, (float) $order->due - $cash - $discount);

            $order->update([
                'status' => OrderStatus::DELIVERED,
                'delivered_date' => $validated['delivered_date'] ?? now()->toDateString(),
                'receiver_name' => $validated['receiver_name'] ?? $order->receiver_name,
                'receiver_phone' => $validated['receiver_phone'] ?? $order->receiver_phone,
                'due' => $newDue,
            ]);

            if ($cash > 0) {
                PaymentHistory::create([
                    'order_id' => $order->id,
                    'amount' => $cash,
                    'payment_type' => $newDue <= 0 ? 'full' : 'partial',
                    'note' => 'Delivery cash collection',
                    'created_at' => now(),
                ]);
            }

            if ($discount > 0) {
                PaymentHistory::create([
                    'order_id' => $order->id,
                    'amount' => $discount,
                    'payment_type' => 'discount',
                    'note' => 'Discount at delivery',
                    'created_at' => now(),
                ]);
            }

            if ($order->customer_id) {
                $this->financialService->recalculateCustomerDue($order->customer_id);
            }

            return $order->fresh()->load(['customer', 'items', 'paymentHistory']);
        });

        return response()->json([
            'message' => 'Order marked as delivered successfully.',
            'data' => $deliveredOrder,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $customerId = $order->customer_id;
        $order->delete();

        if ($customerId) {
            $this->financialService->recalculateCustomerDue($customerId);
        }

        return response()->json(['message' => 'Order deleted successfully.']);
    }
}
