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
        // Min Date: Could be start_date OR end_date (for legacy)
        $minStart = \App\Models\Transaction::where('transaction_type', 'membership')
            ->min('membership_start_date');
            
        $minEnd = \App\Models\Transaction::where('transaction_type', 'membership')
            ->min('membership_end_date');
            
        // Pick the earliest of the two
        $minTrx = $minStart;
        if ($minEnd && (!$minStart || $minEnd < $minStart)) {
            $minTrx = $minEnd;
        }

        $maxTrx = \App\Models\Transaction::where('transaction_type', 'membership')
            ->max('membership_end_date');

        if ($minTrx) {
            $parsedMin = Carbon::parse($minTrx)->startOfMonth();
            // Sanity check: Min date shouldn't be too old (e.g. < 2020)
            if ($parsedMin->year < 2020) $parsedMin = Carbon::create(2020, 1, 1);
            $this->minDate = $parsedMin;
        }

        if ($maxTrx) {
            $parsedMax = Carbon::parse($maxTrx)->endOfMonth();
            // Sanity check: Max date shouldn't be too far future (e.g. > 2030)
            if ($parsedMax->year > 2030) $parsedMax = Carbon::create(2030, 12, 31);
            $this->maxDate = $parsedMax;
        }

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
                    ->where(function($query) {
                        $query->whereNotNull('membership_start_date')
                              ->orWhereNotNull('membership_end_date');
                    })
                    ->orderBy('membership_end_date', 'asc');
            }
        ])
        ->orderByRaw('CAST(member_code AS UNSIGNED) ASC')
        ->get();
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
            // Case 1: Legacy (No Start Date) -> Only fill the End Date column
            if (!$trx->membership_start_date && $trx->membership_end_date) {
                 $end = Carbon::parse($trx->membership_end_date);
                 $columnKey = $end->format('M-y');
                 
                 if (isset($matrixData[$columnKey])) {
                     $matrixData[$columnKey] = $end->format('d/m/Y');
                 }
                 continue;
            }

            // Case 2: Normal (Has Range)
            if ($trx->membership_start_date && $trx->membership_end_date) {
                $start = Carbon::parse($trx->membership_start_date);
                $end = Carbon::parse($trx->membership_end_date);

                // ITERATE BY CALENDAR MONTH to ensure we cover every month in the range
                // Example: 26 Jan -> 25 Feb. We must hit Jan and Feb.
                
                $iterDate = $start->copy()->startOfMonth();
                $targetMonth = $end->copy()->startOfMonth();

                while ($iterDate->lte($targetMonth)) {
                    if ($iterDate->year > 2040) break;

                    $columnKey = $iterDate->format('M-y');
                    
                    if (isset($matrixData[$columnKey])) {
                        // Determine what date value to put in this cell
                        // Ideally: The 'billing date' for this month.
                        
                        // 1. Calculate the projected date: Same DAY as Start Date
                        // e.g. Start 26 Jan -> Candidate 26 Feb
                        try {
                            $candidate = $iterDate->copy()->day($start->day); 
                        } catch (\Exception $e) {
                            // Handle overflow (e.g. 30 Feb -> 1 Mar or 28 Feb)
                            // Carbon usually handles this via strict mode dependent, but safely:
                            $candidate = $iterDate->copy()->endOfMonth();
                        }
                        
                        // 2. If Candidate is beyond the End Date, use End Date instead
                        // Example: Renewal 26 Jan -> 25 Feb.
                        // Jan: Candidate 26 Jan <= 25 Feb? Yes. Write 26/01.
                        // Feb: Candidate 26 Feb > 25 Feb? Yes. Write 25/02 (End Date).
                        
                        if ($candidate->gt($end)) {
                            $valueDate = $end;
                        } else {
                            // Also check if candidate is BEFORE start date (rare, but for first month)
                            if ($candidate->lt($start)) {
                                $valueDate = $start;
                            } else {
                                $valueDate = $candidate;
                            }
                        }

                        $matrixData[$columnKey] = $valueDate->format('d/m/Y');
                    }

                    $iterDate->addMonth();
                }
            }
        }

        // Return fixed columns followed by matrix values in the headers' order
        $matrixValues = array_values($matrixData);
        return array_merge($fixedData, $matrixValues);
    }
}
