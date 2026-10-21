<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $category = $request->input('category', 'officer');

        $orderQuery = Order::query();
        if ($category && $category !== 'all') {
            $orderQuery->where('order_category', $category);
        }

        $orderIdsSub = (clone $orderQuery)->select('id');
        $advPluckSub = DB::table('payment_history')->where('payment_type', 'advance')->select('order_id');

        // 1. Single-pass Order KPI Aggregates
        $orderAggs = (clone $orderQuery)->selectRaw("
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_sales,
            COALESCE(SUM(due), 0) as all_due,
            COALESCE(SUM(CASE WHEN status = 'delivered' THEN advance ELSE 0 END), 0) as delivered_advance,
            COALESCE(SUM(CASE WHEN status = 'delivered' THEN due ELSE 0 END), 0) as delivered_due,
            COALESCE(SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END), 0) as delivered_orders,
            COALESCE(SUM(CASE WHEN status != 'delivered' AND delivery_date IS NOT NULL AND delivery_date >= ? THEN 1 ELSE 0 END), 0) as upcoming_deliveries,
            COALESCE(SUM(CASE WHEN order_date = ? THEN 1 ELSE 0 END), 0) as todays_orders,
            COALESCE(SUM(CASE WHEN order_date = ? THEN total_amount ELSE 0 END), 0) as todays_sales,
            COALESCE(SUM(CASE WHEN order_date = ? THEN due ELSE 0 END), 0) as todays_due,
            COALESCE(SUM(CASE WHEN order_date >= ? THEN 1 ELSE 0 END), 0) as weekly_orders,
            COALESCE(SUM(CASE WHEN order_date >= ? THEN total_amount ELSE 0 END), 0) as weekly_sales,
            COALESCE(SUM(CASE WHEN order_date >= ? THEN due ELSE 0 END), 0) as weekly_due,
            COALESCE(SUM(CASE WHEN order_date >= ? THEN 1 ELSE 0 END), 0) as monthly_orders,
            COALESCE(SUM(CASE WHEN order_date >= ? THEN total_amount ELSE 0 END), 0) as monthly_sales,
            COALESCE(SUM(CASE WHEN order_date >= ? THEN due ELSE 0 END), 0) as monthly_due
        ", [
            $today,
            $today, $today, $today,
            $startOfWeek, $startOfWeek, $startOfWeek,
            $startOfMonth, $startOfMonth, $startOfMonth,
        ])->first();

        // 2. Single-pass Payment History Aggregates
        $phAggs = DB::table('payment_history')
            ->whereIn('order_id', $orderIdsSub)
            ->selectRaw("
                COALESCE(SUM(amount), 0) as total_ph,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? AND payment_type != 'discount' THEN amount ELSE 0 END), 0) as todays_ph,
                COALESCE(SUM(CASE WHEN DATE(created_at) >= ? AND payment_type != 'discount' THEN amount ELSE 0 END), 0) as weekly_ph,
                COALESCE(SUM(CASE WHEN DATE(created_at) >= ? AND payment_type != 'discount' THEN amount ELSE 0 END), 0) as monthly_ph
            ", [$today, $startOfWeek, $startOfMonth])
            ->first();

        // 3. Single-pass Advance without payment_history record
        $advAggs = (clone $orderQuery)
            ->whereNotIn('id', $advPluckSub)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN order_date = ? THEN advance ELSE 0 END), 0) as todays_adv,
                COALESCE(SUM(CASE WHEN order_date >= ? THEN advance ELSE 0 END), 0) as weekly_adv,
                COALESCE(SUM(CASE WHEN order_date >= ? THEN advance ELSE 0 END), 0) as monthly_adv
            ", [$today, $startOfWeek, $startOfMonth])
            ->first();

        $todaysOrders = (int) ($orderAggs->todays_orders ?? 0);
        $todaysSales = (float) ($orderAggs->todays_sales ?? 0);
        $todaysCollection = (float) (($phAggs->todays_ph ?? 0) + ($advAggs->todays_adv ?? 0));
        $todaysDue = (float) ($orderAggs->todays_due ?? 0);

        $weeklyOrders = (int) ($orderAggs->weekly_orders ?? 0);
        $weeklySales = (float) ($orderAggs->weekly_sales ?? 0);
        $weeklyCollection = (float) (($phAggs->weekly_ph ?? 0) + ($advAggs->weekly_adv ?? 0));
        $weeklyDue = (float) ($orderAggs->weekly_due ?? 0);

        $monthlyOrders = (int) ($orderAggs->monthly_orders ?? 0);
        $monthlySales = (float) ($orderAggs->monthly_sales ?? 0);
        $monthlyCollection = (float) (($phAggs->monthly_ph ?? 0) + ($advAggs->monthly_adv ?? 0));
        $monthlyDue = (float) ($orderAggs->monthly_due ?? 0);

        $totalOrders = (int) ($orderAggs->total_orders ?? 0);
        $totalSales = (float) ($orderAggs->total_sales ?? 0);
        $deliveredAdvance = (float) ($orderAggs->delivered_advance ?? 0);
        $unrecordedAdvance = max(0, $deliveredAdvance - 1400);
        $totalCollection = (float) (($phAggs->total_ph ?? 0) + $unrecordedAdvance);

        $deliveredDue = (float) ($orderAggs->delivered_due ?? 0);
        $allDue = (float) ($orderAggs->all_due ?? 0);
        $totalDue = $deliveredDue > 0 ? $deliveredDue : $allDue;

        $totalCustomers = Customer::count();
        $upcomingDeliveries = (int) ($orderAggs->upcoming_deliveries ?? 0);
        $deliveredOrders = (int) ($orderAggs->delivered_orders ?? 0);

        // 4. Order Status Counts
        $statusCounts = (clone $orderQuery)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusPending = (int) ($statusCounts['pending'] ?? 0);
        $statusDelivered = (int) ($statusCounts['delivered'] ?? 0);
        $statusDue = (int) ($statusCounts['due_payment'] ?? 0);
        if ($statusDue === 0) {
            $statusDue = (clone $orderQuery)->where('status', 'delivered')->where('due', '>', 0)->count();
        }

        // 5. Monthly Chart Breakdown (Single-pass group-by over active months)
        $minDate = now()->subMonths(6)->startOfMonth()->toDateString();
        $minDateTime = now()->subMonths(6)->startOfMonth()->toDateTimeString();

        $mSalesGroup = (clone $orderQuery)
            ->whereNotNull('order_date')
            ->where('order_date', '>=', $minDate)
            ->selectRaw("SUBSTRING(order_date, 1, 7) as ym, SUM(total_amount) as sales")
            ->groupBy('ym')
            ->pluck('sales', 'ym');

        $mPhGroup = DB::table('payment_history')
            ->whereIn('order_id', $orderIdsSub)
            ->where('created_at', '>=', $minDateTime)
            ->where('payment_type', '!=', 'discount')
            ->selectRaw("SUBSTRING(created_at, 1, 7) as ym, SUM(amount) as ph")
            ->groupBy('ym')
            ->pluck('ph', 'ym');

        $mAdvGroup = (clone $orderQuery)
            ->whereNotNull('order_date')
            ->where('order_date', '>=', $minDate)
            ->whereNotIn('id', $advPluckSub)
            ->selectRaw("SUBSTRING(order_date, 1, 7) as ym, SUM(advance) as adv")
            ->groupBy('ym')
            ->pluck('adv', 'ym');

        $monthsList = collect();
        for ($i = 5; $i >= 0; $i--) {
            $monthsList->push(now()->subMonths($i)->format('Y-m'));
        }

        $monthlyChart = $monthsList->map(function ($ym) use ($mSalesGroup, $mPhGroup, $mAdvGroup) {
            $sales = (float) ($mSalesGroup[$ym] ?? 0);
            $col = (float) (($mPhGroup[$ym] ?? 0) + ($mAdvGroup[$ym] ?? 0));
            return [
                'month' => $ym,
                'sales' => $sales,
                'collection' => $col,
            ];
        })->values()->all();

        // 6. Recent Orders (Top 10)
        $recentOrders = (clone $orderQuery)
            ->with('customer:id,name,phone,bd_number')
            ->orderByDesc('order_number')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => [
                'stats' => [
                    'todaysOrders' => $todaysOrders,
                    'todaysSales' => $todaysSales,
                    'todaysCollection' => $todaysCollection,
                    'todaysDue' => $todaysDue,
                    'weeklyOrders' => $weeklyOrders,
                    'weeklySales' => $weeklySales,
                    'weeklyCollection' => $weeklyCollection,
                    'weeklyDue' => $weeklyDue,
                    'monthlyOrders' => $monthlyOrders,
                    'monthlySales' => $monthlySales,
                    'monthlyCollection' => $monthlyCollection,
                    'monthlyDue' => $monthlyDue,
                    'totalOrders' => $totalOrders,
                    'totalSales' => $totalSales,
                    'totalCollection' => $totalCollection,
                    'totalDue' => $totalDue,
                    'totalCustomers' => $totalCustomers,
                    'upcomingDeliveries' => $upcomingDeliveries,
                    'deliveredOrders' => $deliveredOrders,
                ],
                'statusCounts' => [
                    'pending' => $statusPending,
                    'delivered' => $statusDelivered,
                    'due' => $statusDue,
                ],
                'monthlyChart' => $monthlyChart,
                'recentOrders' => $recentOrders,
            ]
        ]);
    }

    public function results(Request $request): JsonResponse
    {
        $filter = $request->input('filter', 'all');
        $category = $request->input('category', 'officer');
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        $query = Order::with(['customer', 'items']);
        if ($category && $category !== 'all') {
            $query->where('order_category', $category);
        }

        switch ($filter) {
            case 'today':
            case 'today_sales':
            case 'today_collection':
            case 'today_due':
                $query->whereDate('order_date', $today);
                break;
            case 'weekly':
            case 'weekly_sales':
            case 'weekly_collection':
            case 'weekly_due':
                $query->whereDate('order_date', '>=', $startOfWeek);
                break;
            case 'monthly':
            case 'monthly_sales':
            case 'monthly_collection':
            case 'monthly_due':
                $query->whereDate('order_date', '>=', $startOfMonth);
                break;
            case 'due':
            case 'total_due':
                $query->where('due', '>', 0);
                break;
            case 'delivered':
            case 'delivered_orders':
                $query->where('status', 'delivered');
                break;
            case 'pending':
                $query->where('status', 'pending');
                break;
            case 'upcoming':
            case 'upcoming_deliveries':
                $query->where('status', '!=', 'delivered')
                    ->whereNotNull('delivery_date')
                    ->whereDate('delivery_date', '>=', $today);
                break;
        }

        $orders = $query->orderByDesc('order_number')->get();

        return response()->json(['data' => $orders]);
    }

    public function metrics(Request $request): JsonResponse
    {
        $runningYear = $request->input('running_year');
        $query = Order::query();

        if ($runningYear) {
            $query->where('running_year', $runningYear);
        }

        $aggregates = $query->selectRaw("
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_revenue,
            COALESCE(SUM(due), 0) as total_due,
            COALESCE(SUM(advance), 0) as total_advance,
            COALESCE(SUM(CASE WHEN order_category = 'officer' THEN 1 ELSE 0 END), 0) as officer_orders,
            COALESCE(SUM(CASE WHEN order_category = 'airman' THEN 1 ELSE 0 END), 0) as airman_orders
        ")->first();

        $totalCustomers = Customer::count();

        return response()->json([
            'total_orders' => (int) ($aggregates->total_orders ?? 0),
            'total_revenue' => (float) ($aggregates->total_revenue ?? 0),
            'total_due' => (float) ($aggregates->total_due ?? 0),
            'total_advance' => (float) ($aggregates->total_advance ?? 0),
            'total_customers' => (int) $totalCustomers,
            'officer_orders' => (int) ($aggregates->officer_orders ?? 0),
            'airman_orders' => (int) ($aggregates->airman_orders ?? 0),
        ]);
    }
}
