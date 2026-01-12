<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\TransactionExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // Fungsi Download Excel
    public function exportExcel()
    {
        return Excel::download(new TransactionExport, 'laporan-king-gym.xlsx');
    }

    // Fungsi Download PDF (Simple)
    public function exportPdf()
    {
        $transactions = Transaction::with('member')->get();
        $pdf = Pdf::loadView('reports.transactions', compact('transactions'));
        return $pdf->download('laporan-king-gym.pdf');
    }
}
