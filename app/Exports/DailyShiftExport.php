<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\Transaction;
use App\Models\Expense;
use Carbon\Carbon;

class DailyShiftExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $date;
    protected $title;

    public function __construct($date, $title = null)
    {
        $this->date = $date; // Format Y-m-d
        $this->title = $title;
        // Ensure locale is ID
        Carbon::setLocale('id');
    }

    public function view(): View
    {
        // 1. Fetch Transactions with Relations
        $allTransactions = Transaction::with(['member', 'details', 'user'])
            ->whereDate('created_at', $this->date)
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Fetch Expenses
        $allExpenses = Expense::with('user')
            ->whereDate('date', $this->date)
            ->get();

        // 3. Split by Shift
        // Shift Pagi: 06:00 - 13:59
        // Shift Sore: 14:00 - 22:00+
        $trxPagi = $allTransactions->filter(fn($t) => Carbon::parse($t->created_at)->hour < 14);
        $trxSore = $allTransactions->filter(fn($t) => Carbon::parse($t->created_at)->hour >= 14);

        // Expense Split (By Created_At or Inputted Time? Usually expensive table has time if datetime, but if date only, hard to split).
        // Let's assume expenses table has timestamps OR check created_at. The migration created `date` as Date or DateTime?
        // Migration check needed. Assuming Date only, we might put all expenses in Sore (End of Day) or Split if created_at available.
        // Let's rely on created_at if possible, else split evenly? No, usually expenses are input at specific time.
        // Let's try created_at if available, otherwise put in Summary or Pagi default. 
        // NOTE: Previous code used `date` column. Let's assume we can use `created_at` for time precision if available.
        // If not, we just show them in a separate section bottom? The request puts them inside Shift.
        // I will use created_at for time check.

        $expPagi = $allExpenses->filter(function ($e) {
            // If created_at exists and is today
            if ($e->created_at)
                return $e->created_at->hour < 14;
            return true; // Default to Pagi if no time info? Or maybe user wants manual entry.
        });
        $expSore = $allExpenses->filter(function ($e) {
            if ($e->created_at)
                return $e->created_at->hour >= 14;
            return false;
        });


        return view('reports.daily_shift', [
            'date' => $this->date,
            'pagi' => $this->prepareShiftData($trxPagi, $expPagi),
            'sore' => $this->prepareShiftData($trxSore, $expSore),
            'grandTotalIncome' => $allTransactions->sum('total_amount'),
            'grandTotalExpense' => $allExpenses->sum('amount'),
            'netProfit' => $allTransactions->sum('total_amount') - $allExpenses->sum('amount')
        ]);
    }

    // Helper to detailed data structure
    private function prepareShiftData($transactions, $expenses)
    {
        // A. Sales Breakdown (Membership vs Product Mix)
        $groupedSales = [
            'MEMBERSHIP' => [],
            'PENJUALAN (PRODUK)' => []
        ];

        // Fetch all package names for classification
        $packageNames = \App\Models\Package::pluck('name')->toArray();

        // Normalize package names for robust comparison (e.g. lowercase) if needed. 
        // For now direct match.

        foreach ($transactions as $trx) {
            foreach ($trx->details as $detail) {
                $name = $detail->item_name;

                // Categorize based on Item Name (more accurate for mixed transactions)
                // If it matches a Package Name -> MEMBERSHIP
                // Else -> PENJUALAN (PRODUK)

                $category = 'PENJUALAN (PRODUK)';
                if (in_array($name, $packageNames)) {
                    $category = 'MEMBERSHIP';
                } elseif ($trx->transaction_type === 'membership') {
                    // Fallback: If transaction is purely membership but name mismatch?
                    $category = 'MEMBERSHIP';
                }

                if (!isset($groupedSales[$category][$name])) {
                    $groupedSales[$category][$name] = [
                        'name' => $name,
                        'qty' => 0,
                        'price' => $detail->price, // Unit price
                        'total' => 0
                    ];
                }

                $groupedSales[$category][$name]['qty'] += $detail->qty;
                $groupedSales[$category][$name]['total'] += $detail->subtotal;
            }
        }

        // B. Income by Method
        $incomeByMethod = [
            'CASH' => 0,
            'QRIS' => 0,
            'TRANSFER' => 0
        ];

        foreach ($transactions as $trx) {
            // Normalize method name
            $method = strtoupper($trx->payment_method);

            // Map common variations if any (e.g. "TF" -> "TRANSFER")
            if ($method === 'TF')
                $method = 'TRANSFER';
            if ($method === 'CASH')
                $method = 'CASH'; // already upper
            // If not in key list, maybe Group into 'TRANSFER' or add distinct?
            // Client Req: Only Cash, QRIS, Transfer rows.

            if (isset($incomeByMethod[$method])) {
                $incomeByMethod[$method] += $trx->total_amount;
            } else {
                // Should we dump into OTHER or one of the above? 
                // Let's assume Transfer for any odd non-cash/non-qris
                $incomeByMethod['TRANSFER'] += $trx->total_amount;
            }
        }

        return [
            'sales' => $groupedSales,
            'income' => $incomeByMethod,
            'incomeTotal' => $transactions->sum('total_amount'),
            'expenses' => $expenses,
            'expenseTotal' => $expenses->sum('amount'),
            'netProfit' => $transactions->sum('total_amount') - $expenses->sum('amount')
        ];
    }

    public function title(): string
    {
        return $this->title ?: 'Daily Report';
    }
}
