<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class MonthlySheetExport implements WithMultipleSheets
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Determine days in month
        $startOfMonth = Carbon::createFromDate($this->year, $this->month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($this->year, $this->month, $day)->format('Y-m-d');
            $sheetTitle = Carbon::createFromDate($this->year, $this->month, $day)->format('d M'); // Check exact format fit? Max 31 chars. "24 Jan" is fine.

            $sheets[] = new DailyShiftExport($date, $sheetTitle);
        }

        return $sheets;
    }
}
