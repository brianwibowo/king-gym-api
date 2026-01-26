<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\Transaction;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class YearlySummaryExport implements FromView, ShouldAutoSize
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function view(): View
    {
        // 1. Fetch Year Data
        $startYear = Carbon::create($this->year, 1, 1)->startOfYear();
        $endYear = Carbon::create($this->year, 12, 31)->endOfYear();

        $transactions = Transaction::with(['details'])
            ->whereBetween('created_at', [$startYear, $endYear])
            ->get();

        $expenses = Expense::whereBetween('date', [$startYear->format('Y-m-d'), $endYear->format('Y-m-d')])
            ->get();

        // 2. Aggregate Sales (Membership vs Products)
        $groupedSales = [
            'MEMBERSHIP' => [],
            'PENJUALAN (PRODUK)' => []
        ];

        $packageNames = \App\Models\Package::pluck('name')->toArray();

        foreach ($transactions as $trx) {
            foreach ($trx->details as $detail) {
                $name = $detail->item_name;

                // Filter out Zero-value legacy imports (Dummy Data)
                // We only want REAL sales in the sales report.
                if ($detail->subtotal <= 0) {
                    continue;
                }

                // Categorize
                $category = 'PENJUALAN (PRODUK)';
                if (in_array($name, $packageNames)) {
                    $category = 'MEMBERSHIP';
                } elseif ($trx->transaction_type === 'membership') {
                    $category = 'MEMBERSHIP';
                }

                if (!isset($groupedSales[$category][$name])) {
                    $groupedSales[$category][$name] = [
                        'name' => $name,
                        'qty' => 0,
                        'total' => 0
                    ];
                }

                $groupedSales[$category][$name]['qty'] += $detail->qty;
                $groupedSales[$category][$name]['total'] += $detail->subtotal;
            }
        }

        // 3. Aggregate Monthly Income (Optional but useful) or Just Year Totals?
        // User asked for "Overview products sold".
        // Let's provide Totals.

        return view('reports.yearly_summary', [
            'year' => $this->year,
            'sales' => $groupedSales,
            'totalIncome' => $transactions->sum('total_amount'),
            'totalExpense' => $expenses->sum('amount'),
            'netProfit' => $transactions->sum('total_amount') - $expenses->sum('amount')
        ]);
    }
}
