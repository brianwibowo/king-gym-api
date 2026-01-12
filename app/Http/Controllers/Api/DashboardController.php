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
        $range = $request->query('range', 'weekly'); // Default ke mingguan
        $now = Carbon::now();

        // 1. Ambil Data Ringkasan (KPI Cards)
        $totalMembers = Member::count();
        $activeMembers = Member::where('status', 'active')->count();
        $totalRevenue = Transaction::sum('total_amount');
        $lowStockProducts = Product::where('stock', '<', 5)->get();

        // 2. Logika Grafik Berdasarkan Range
        $query = Transaction::select(
            DB::raw('SUM(total_amount) as total'),
            DB::raw('DATE(created_at) as date')
        );

        if ($range == 'daily') {
            // 7 Hari Terakhir
            $query->where('created_at', '>=', $now->subDays(7));
        } elseif ($range == 'monthly') {
            // 30 Hari Terakhir
            $query->where('created_at', '>=', $now->subDays(30));
        } elseif ($range == 'yearly') {
            // Per Bulan di Tahun Ini
            $query = Transaction::select(
                DB::raw('SUM(total_amount) as total'),
                DB::raw("DATE_FORMAT(created_at, '%M') as date") // Nama Bulan
            )->whereYear('created_at', $now->year)
             ->groupBy('date');
        } else { // Weekly
            $query->where('created_at', '>=', $now->startOfWeek());
        }

        // Grouping untuk grafik (kecuali yearly yang sudah di-group di atas)
        if ($range != 'yearly') {
            $query->groupBy('date')->orderBy('date', 'ASC');
        }

        $chartData = $query->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_members' => $totalMembers,
                    'active_members' => $activeMembers,
                    'total_revenue' => (int)$totalRevenue,
                    'low_stock_count' => $lowStockProducts->count(),
                ],
                'chart' => $chartData,
                'low_stock_items' => $lowStockProducts
            ]
        ]);
    }
}
