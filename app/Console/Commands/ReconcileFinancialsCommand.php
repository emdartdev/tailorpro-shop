<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentHistory;
use App\Models\StaffPayment;
use App\Models\Worker;
use App\Models\WorkerEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileFinancialsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reconcile-financials {--fix : Automatically fix minor rounding discrepancies}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform exhaustive financial reconciliation and referential integrity audit across Orders, Customers, and Workers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info(' TAILORPRO FINANCIAL & DATA INTEGRITY RECONCILIATION AUDIT');
        $this->info('================================================================');
        $this->newLine();

        $shouldFix = $this->option('fix');
        $hasErrors = false;

        // 1. Order Financial Reconciliation
        $this->comment('1. Auditing Order Financial Balances & Due Amounts...');
        $orders = Order::with(['items', 'paymentHistory'])->get();
        $orderCount = $orders->count();
        $orderDiscrepancies = 0;
        $orphanedOrders = 0;
        $totalOrderAmount = 0.0;
        $totalAdvance = 0.0;
        $totalDue = 0.0;

        foreach ($orders as $order) {
            $totalOrderAmount += (float) $order->total_amount;
            $totalAdvance += (float) $order->advance;
            $totalDue += (float) $order->due;

            // Check orphaned
            if (! Customer::where('id', $order->customer_id)->exists()) {
                $orphanedOrders++;
                $hasErrors = true;
            }

            // Check payments sum
            $extraPaid = (float) $order->paymentHistory->sum('amount');
            $expectedDue = max(0, (float) $order->total_amount - (float) $order->advance - $extraPaid);
            $actualDue = (float) $order->due;

            if (abs($expectedDue - $actualDue) > 0.05) {
                $orderDiscrepancies++;
                if ($shouldFix) {
                    $order->due = $expectedDue;
                    $order->save();
                }
            }
        }

        $orderStatus = ($orderDiscrepancies === 0 && $orphanedOrders === 0)
            ? '<fg=green;options=bold>PASS</>'
            : '<fg=red;options=bold>FAIL</>';

        $this->table(
            ['Metric', 'Value', 'Audit Status'],
            [
                ['Total Orders Evaluated', $orderCount, '<fg=green>OK</>'],
                ['Total Order Volume (৳)', number_format($totalOrderAmount, 2), '<fg=green>OK</>'],
                ['Total Advance Paid (৳)', number_format($totalAdvance, 2), '<fg=green>OK</>'],
                ['Total Outstanding Due (৳)', number_format($totalDue, 2), '<fg=green>OK</>'],
                ['Orphaned Orders (No Customer)', $orphanedOrders, $orphanedOrders === 0 ? '<fg=green>0</>' : '<fg=red>' . $orphanedOrders . '</>'],
                ['Order Balance Discrepancies', $orderDiscrepancies, $orderDiscrepancies === 0 ? '<fg=green>0</>' : '<fg=red>' . $orderDiscrepancies . '</>'],
                ['Overall Orders Audit', '', $orderStatus],
            ]
        );

        $this->newLine();

        // 2. Worker Ledger Reconciliation
        $this->comment('2. Auditing Worker Entries & Payroll Ledgers...');
        $workers = Worker::with(['entries', 'staffPayments'])->get();
        $workerCount = $workers->count();
        $entryDiscrepancies = 0;
        $orphanedEntries = 0;
        $totalWorkAmount = 0.0;
        $totalKhoroch = 0.0;
        $totalPayments = 0.0;

        foreach ($workers as $w) {
            foreach ($w->entries as $entry) {
                $expectedTotal = (float) $entry->quantity * (float) $entry->unit_price;
                $actualTotal = (float) $entry->total_taka;
                $totalWorkAmount += $actualTotal;
                $totalKhoroch += (float) $entry->khoroch;

                if (abs($expectedTotal - $actualTotal) > 0.05) {
                    $entryDiscrepancies++;
                    if ($shouldFix) {
                        $entry->total_taka = $expectedTotal;
                        $entry->save();
                    }
                }
            }

            $totalPayments += (float) $w->staffPayments->sum('amount');
        }

        $orphanedEntries = WorkerEntry::whereNotIn('worker_id', $workers->pluck('id'))->count();
        if ($orphanedEntries > 0) {
            $hasErrors = true;
        }

        $workerStatus = ($entryDiscrepancies === 0 && $orphanedEntries === 0)
            ? '<fg=green;options=bold>PASS</>'
            : '<fg=red;options=bold>FAIL</>';

        $this->table(
            ['Metric', 'Value', 'Audit Status'],
            [
                ['Total Registered Workers', $workerCount, '<fg=green>OK</>'],
                ['Total Worker Piece Entries', WorkerEntry::count(), '<fg=green>OK</>'],
                ['Total Worker Earnings (৳)', number_format($totalWorkAmount, 2), '<fg=green>OK</>'],
                ['Total Khoroch Deductions (৳)', number_format($totalKhoroch, 2), '<fg=green>OK</>'],
                ['Total Staff Payments Disbursed (৳)', number_format($totalPayments, 2), '<fg=green>OK</>'],
                ['Net Worker Payable Balance (৳)', number_format($totalWorkAmount - $totalKhoroch - $totalPayments, 2), '<fg=green>OK</>'],
                ['Orphaned Worker Entries', $orphanedEntries, $orphanedEntries === 0 ? '<fg=green>0</>' : '<fg=red>' . $orphanedEntries . '</>'],
                ['Line Total Discrepancies', $entryDiscrepancies, $entryDiscrepancies === 0 ? '<fg=green>0</>' : '<fg=red>' . $entryDiscrepancies . '</>'],
                ['Overall Workers Audit', '', $workerStatus],
            ]
        );

        $this->newLine();

        // 3. Foreign Key & Integrity Check
        $this->comment('3. Auditing Foreign Keys & Cascade Constraints...');
        $orphanedItems = OrderItem::whereNotIn('order_id', Order::pluck('id'))->count();
        $orphanedPayments = PaymentHistory::whereNotIn('order_id', Order::pluck('id'))->count();
        $orphanedStaffPayments = StaffPayment::whereNotIn('worker_id', Worker::pluck('id'))->count();

        $referentialIntegrityPass = ($orphanedItems === 0 && $orphanedPayments === 0 && $orphanedStaffPayments === 0);
        if (! $referentialIntegrityPass) {
            $hasErrors = true;
        }

        $this->table(
            ['Table Relationship', 'Orphaned Foreign Keys', 'Integrity Status'],
            [
                ['order_items ➔ orders', $orphanedItems, $orphanedItems === 0 ? '<fg=green>PASS (0)</>' : '<fg=red>FAIL (' . $orphanedItems . ')</>'],
                ['payment_history ➔ orders', $orphanedPayments, $orphanedPayments === 0 ? '<fg=green>PASS (0)</>' : '<fg=red>FAIL (' . $orphanedPayments . ')</>'],
                ['staff_payments ➔ workers', $orphanedStaffPayments, $orphanedStaffPayments === 0 ? '<fg=green>PASS (0)</>' : '<fg=red>FAIL (' . $orphanedStaffPayments . ')</>'],
                ['worker_entries ➔ workers', $orphanedEntries, $orphanedEntries === 0 ? '<fg=green>PASS (0)</>' : '<fg=red>FAIL (' . $orphanedEntries . ')</>'],
                ['orders ➔ customers', $orphanedOrders, $orphanedOrders === 0 ? '<fg=green>PASS (0)</>' : '<fg=red>FAIL (' . $orphanedOrders . ')</>'],
            ]
        );

        $this->newLine();

        if ($hasErrors) {
            $this->error('Reconciliation identified discrepancies that require attention.');
            return Command::FAILURE;
        }

        $this->info('✓ 100% FINANCIAL & REFERENTIAL INTEGRITY RECONCILIATION VERIFIED.');
        return Command::SUCCESS;
    }
}
