<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class MembersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $headers = [];
    protected $minDate;
    protected $maxDate;

    public function __construct()
    {
        // 1. Calculate Date Range for Headers (Global)
        // We need to scan all transactions first to determine the columns.
        // Doing this in constructor or a separate query is fine.

        $this->minDate = Carbon::now()->startOfMonth();
        $this->maxDate = Carbon::now()->endOfMonth();

        // Optimized way: Get min/max from DB directly
        $minTrx = \App\Models\Transaction::where('transaction_type', 'membership')
            ->whereNotNull('membership_start_date')
            ->min('membership_start_date');

        $maxTrx = \App\Models\Transaction::where('transaction_type', 'membership')
            ->whereNotNull('membership_end_date')
            ->max('membership_end_date');

        if ($minTrx)
            $this->minDate = Carbon::parse($minTrx)->startOfMonth();
        if ($maxTrx)
            $this->maxDate = Carbon::parse($maxTrx)->endOfMonth();

        // Generate Headers
        $current = $this->minDate->copy();
        while ($current->lte($this->maxDate)) {
            $this->headers[] = $current->format('M-y');
            $current->addMonth();
        }
    }

    public function collection()
    {
        return Member::with([
            'transactions' => function ($q) {
                $q->where('transaction_type', 'membership')
                    ->whereNotNull('membership_start_date')
                    ->orderBy('created_at', 'asc');
            }
        ])->orderBy('name', 'asc')->get();
    }

    public function headings(): array
    {
        return array_merge(
            ['No Member', 'Nama', 'Alamat', 'Telp', 'Foto'],
            $this->headers
        );
    }

    public function map($member): array
    {
        $fixedData = [
            $member->member_code,
            $member->name,
            $member->address,
            $member->phone ?? '-',
            '', // Foto column
        ];

        // Prepare Matrix Row
        $matrixData = array_fill_keys($this->headers, '');

        foreach ($member->transactions as $trx) {
            if (!$trx->membership_start_date || !$trx->membership_end_date)
                continue;

            $start = Carbon::parse($trx->membership_start_date);
            $end = Carbon::parse($trx->membership_end_date);

            // Loop through each month covered by this transaction
            // Logic: We start from the exact start date (e.g., 24/01/2026)
            // And we increment by 1 month until we pass the end date.

            $curr = $start->copy();

            // Limit loop to avoid infinite in bad data cases, though DB dates are standard
            while ($curr->lte($end)) {
                // Determine which column this date falls into
                $columnKey = $curr->format('M-y');

                // Only fill if this column exists in our headers (it should)
                if (isset($matrixData[$columnKey])) {
                    // Logic: "Adaptive" date. 
                    // If start is 24/01, next month becomes 24/02, etc.
                    // $curr is already doing this via addMonth()!
                    $matrixData[$columnKey] = $curr->format('d/m/Y');
                }

                $curr->addMonth();
            }
        }

        return array_merge($fixedData, array_values($matrixData));
    }
}
