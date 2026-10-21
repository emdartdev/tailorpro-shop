<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderSequenceService
{
    /**
     * Atomically calculate the next sequential order number for a given category and running year.
     */
    public function getNextOrderNumber(string $category = 'officer', ?string $runningYear = null): int
    {
        return DB::transaction(function () use ($category, $runningYear) {
            $query = Order::where('order_category', $category);
            if (!empty($runningYear)) {
                $query->where('running_year', $runningYear);
            }
            $maxNumber = $query->lockForUpdate()->max('order_number');

            if ($maxNumber === null || $maxNumber === 0) {
                return $category === 'officer' ? 1001 : 1;
            }

            return (int) $maxNumber + 1;
        });
    }
}
