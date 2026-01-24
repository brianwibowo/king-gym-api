<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\TransactionExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Fungsi Download Excel
    public function exportExcel(Request $request)
    {
        $type = $request->query('type', 'daily'); // daily, monthly, yearly
        $date = $request->query('date', date('Y-m-d')); // For daily
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        try {
            if ($type === 'monthly') {
                $filename = "Laporan_Bulanan_{$month}_{$year}.xlsx";
                return Excel::download(new \App\Exports\MonthlySheetExport($month, $year), $filename);
            }

            if ($type === 'yearly') {
                $filename = "Laporan_Tahunan_{$year}.xlsx";
                return Excel::download(new \App\Exports\YearlySummaryExport($year), $filename);
            }

            // Default: Daily
            $filename = "Laporan_Harian_{$date}.xlsx";
            return Excel::download(new \App\Exports\DailyShiftExport($date), $filename);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Fungsi Download PDF (Simple)
    public function exportPdf()
    {
        $transactions = Transaction::with('member')->get();
        $pdf = Pdf::loadView('reports.transactions', compact('transactions'));
        return $pdf->download('laporan-king-gym.pdf');
    }
}
