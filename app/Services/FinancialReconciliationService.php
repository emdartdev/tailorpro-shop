<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\DB;

class FinancialReconciliationService
{
    /**
     * Record a payment transaction and update the order due and customer total due balance.
     */
    public function recordPayment(Order $order, float $amount, string $type = 'partial', ?string $notes = null): PaymentHistory
    {
        return DB::transaction(function () use ($order, $amount, $type, $notes) {
            $payment = PaymentHistory::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'payment_type' => $type,
                'note' => $notes,
                'created_at' => now(),
            ]);

            $order->due = max(0, (float) $order->due - $amount);
            if ($order->due <= 0 && $order->status->value === 'due_payment') {
                $order->status = OrderStatus::DELIVERED;
            }
            $order->save();

            $this->recalculateCustomerDue($order->customer_id);

            return $payment;
        });
    }

    /**
     * Recalculate customer aggregate financials (total_orders, total_spent, due_amount) from active orders.
     */
    public function recalculateCustomerDue(string $customerId): float
    {
        $orders = Order::where('customer_id', $customerId);
        $totalOrders = (int) $orders->count();
        $totalSpent = (float) (clone $orders)->sum('total_amount');
        $totalDue = (float) (clone $orders)->sum('due');

        Customer::where('id', $customerId)->update([
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'due_amount' => $totalDue,
        ]);

        return $totalDue;
    }
}
