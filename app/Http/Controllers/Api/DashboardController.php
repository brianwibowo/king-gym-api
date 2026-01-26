<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Member;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();

        // Date Filter (Default: Today if not provided, or handle "all" logic if needed)
        // Frontend sends start_date & end_date. 
        // If "All Time" selected, frontend sends null/empty.
        // If params exist -> Filter. If not -> All Time.

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // 1. KPI Cards (Global / All Time? Or Filtered?)
        // Usually "Total Members" is current state (not filtered by date).
        // "Total Income" usually follows the filter in an Income Summary context.
        // Let's make "Total Quantity" (Members) global, but "Revenue" filtered.

        $totalMembers = Member::count();
        $activeMembers = Member::where('status', 'active')->count();
        $lowStockProducts = Product::where('stock', '<', 5)->get();

        // 2. Income Summary (Breakdown)
        // We need to split income into 'membership' and 'product' based on the items sold.
        // Join transaction_details with transactions.
        // Left join packages to identify if an item is a membership package.

        $incomeQuery = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->leftJoin('packages', 'transaction_details.item_name', '=', 'packages.name'); // Match by name

        // Apply Date Filter to transactions if provided
        if ($startDate && $endDate) {
            $incomeQuery->whereBetween('transactions.created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        }

        $incomeStats = $incomeQuery->select(
            DB::raw("SUM(CASE WHEN packages.id IS NOT NULL THEN transaction_details.subtotal ELSE 0 END) as membership_income"),
            DB::raw("SUM(CASE WHEN packages.id IS NULL THEN transaction_details.subtotal ELSE 0 END) as product_income")
        )->first();

        $membershipIncome = (int) ($incomeStats->membership_income ?? 0);
        $productIncome = (int) ($incomeStats->product_income ?? 0);
        $totalIncome = $membershipIncome + $productIncome;

        // 3. Analytics Data (Charts) - Keep existing logic or adjust to filter?
        // The user asked for "Income Summary" to have the date filter.
        // It's best if the analytics also reflect the filter if appropriate, OR we keep analytics separate (Daily/Weekly/Yearly).
        // The previous analytics implementation was fixed ranges (This Week, This Month, This Year).
        // Let's keep the standard Analytics for the "Revenue Analytics" chart as is (it has its own tabs), 
        // BUT the "Income Summary" component will use the data we just calculated above.

        // HOWEVER, we need to make sure the "Summary" block in frontend uses `totalIncome` from the filter.

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_members' => $totalMembers,
                    'active_members' => $activeMembers,
                    'total_revenue' => $totalIncome, // This effectively becomes "Revenue for selected period"
                    'low_stock_count' => $lowStockProducts->count(),
                ],
                'income_breakdown' => [
                    'total' => $totalIncome,
                    'membership' => $membershipIncome,
                    'product' => $productIncome,
                    'membership_percentage' => $totalIncome > 0 ? round(($membershipIncome / $totalIncome) * 100, 1) : 0,
                    'product_percentage' => $totalIncome > 0 ? round(($productIncome / $totalIncome) * 100, 1) : 0,
                ],
                // Keep Analytics for the separate chart component if needed, or we might hide it if filtering "Today" makes it redundant?
                // For now, return it so the chart still works if the user scrolls down.
                'analytics' => $this->getAnalyticsData(),
                'low_stock_items' => $lowStockProducts
            ]
        ]);
    }

    private function getAnalyticsData()
    {
        $now = Carbon::now();

        // A. Daily (This Week)
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $dailyStats = Transaction::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->groupBy('date')->orderBy('date')->get();

        // B. Weekly (Month)
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $weeklyRaw = Transaction::select(DB::raw('WEEK(created_at, 1) as week_num'), DB::raw('SUM(total_amount) as total'))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('week_num')->orderBy('week_num')->get();

        $weeklyStats = [];
        $firstWeekNum = $startOfMonth->weekOfYear; // Approx
        if ($weeklyRaw->isNotEmpty())
            $firstWeekNum = $weeklyRaw->first()->week_num;
        foreach ($weeklyRaw as $item) {
            $weeklyStats[] = ['week' => 'Week ' . ($item->week_num - $firstWeekNum + 1), 'total' => (int) $item->total];
        }

        // C. Yearly
        $yearlyStats = Transaction::select(DB::raw("DATE_FORMAT(created_at, '%b') as month_name"), DB::raw('MONTH(created_at) as month_num'), DB::raw('SUM(total_amount) as total'))
            ->whereYear('created_at', $now->year)
            ->groupBy('month_name', 'month_num')->orderBy('month_num')->get();

        return [
            'daily' => $dailyStats,
            'weekly' => $weeklyStats,
            'yearly' => $yearlyStats
        ];
    }
    public function insights(Request $request)
    {
        $period = $request->input('period', 'month'); // day, week, month, all
        $now = Carbon::now();

        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->leftJoin('products', 'transaction_details.item_name', '=', 'products.name') // Join to get stock
            ->select(
                'transaction_details.item_name',
                DB::raw('SUM(transaction_details.qty) as total_qty'),
                DB::raw('SUM(transaction_details.subtotal) as total_revenue'),
                DB::raw('MAX(products.stock) as current_stock') // Use MAX (since grouped by name, stock should be same)
            )
            ->where('transactions.total_amount', '>', 0) // Fix: Exclude legacy imports (0 value)
            ->groupBy('transaction_details.item_name');

        // Apply Date Filter
        if ($period === 'day') {
            $query->whereDate('transactions.created_at', $now->toDateString());
        } elseif ($period === 'week') {
            $query->whereBetween('transactions.created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth('transactions.created_at', $now->month)
                ->whereYear('transactions.created_at', $now->year);
            // Optionally: Use startOfMonth and endOfMonth for better index usage if needed, but whereMonth is fine here.
        }
        // 'all' means no filter

        // Get Aggregated Data
        $results = $query->get();

        // Sort for Top 3 (or User defined limit)
        $limit = $request->input('limit', 3);

        if ($limit == 'all') {
            $topSelling = $results->sortByDesc('total_qty')->values();
        } else {
            $topSelling = $results->sortByDesc('total_qty')->take((int) $limit)->values();
        }

        // Sort for Bottom 3 (Least Selling)
        // Only consider items that have at least 1 sale in this period (since we query transaction_details).
        // If we wanted absolutely 0 sales items, we'd need to right join with products/packages, but that's complex.
        // Product insights usually implies "of things that sold".
        // However, user might want to know what ISN'T selling.
        // For simplicity and typical "Least Selling" charts, we usually show lowest non-zero numbers or joining with inventory.
        // Given the prompt "Produk tersepi", let's show the lowest sales count from the transaction list.
        $leastSelling = $results->sortBy('total_qty')->take(3)->values();

        return response()->json([
            'period' => $period,
            'top' => $topSelling,
            'bottom' => $leastSelling
        ]);
    }
}
