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
        $data = [];

        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($this->year, $m, 1)->startOfMonth();
            $end = Carbon::create($this->year, $m, 1)->endOfMonth();

            // Aggregates
            // Income
            $incomeMembership = Transaction::whereBetween('created_at', [$start, $end])
                ->where('transaction_type', 'membership')
                ->sum('total_amount');

            // Product (Includes 'mix' type partially? Or just sum non-membership? Let's assume mix is product+memb, but backend stores simplified. User usually separates by type.)
            // If type can be 'product', 'membership', 'mix'.
            // For now, let's group anything NOT membership as product for simplicity, OR specifically.
            $incomeProduct = Transaction::whereBetween('created_at', [$start, $end])
                ->whereIn('transaction_type', ['product', 'mix']) // Add mix here? Or handle separately?
                ->sum('total_amount'); // Simplified. If 'mix' exists, maybe split logic needed?
            // Assuming 'mix' is rare or handled elsewhere. Let's put in 'Product/Other'.

            $totalIncome = $incomeMembership + $incomeProduct;

            $trxCount = Transaction::whereBetween('created_at', [$start, $end])->count();

            // Expense
            $totalExpense = Expense::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->sum('amount');

            $data[] = [
                'month_name' => $start->translatedFormat('F'),
                'trx_count' => $trxCount,
                'income_membership' => $incomeMembership,
                'income_product' => $incomeProduct,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_profit' => $totalIncome - $totalExpense
            ];
        }

        return view('reports.yearly_summary', [
            'year' => $this->year,
            'data' => $data
        ]);
    }
}
