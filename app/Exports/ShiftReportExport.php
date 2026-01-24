<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        // 1. Fetch all attendances for the month
        // We need user name, so we join users table.
        // We will process the counts in PHP (easier time comparison) or raw SQL.
        // Raw SQL for shifts is faster.

        /*
        Shift Pagi: 06.00 - 14.00 (TIME(clock_in) >= 06:00 AND TIME(clock_in) < 14:00)
        Shift Sore: 14.00 - 22.00 (TIME(clock_in) >= 14:00 AND TIME(clock_in) < 22:00)
        */

        // Group by User
        $users = \App\Models\User::where('role', '!=', 'member') // Assuming staff/admin/owner only
            ->orderBy('name', 'asc')
            ->get();

        $data = collect();

        foreach ($users as $user) {
            $attendances = Attendance::where('user_id', $user->id)
                ->whereMonth('created_at', $this->month)
                ->whereYear('created_at', $this->year)
                ->get();

            $pagi = 0;
            $sore = 0;

            foreach ($attendances as $att) {
                if (!$att->clock_in)
                    continue;

                $time = Carbon::parse($att->clock_in);
                $hour = $time->hour;

                // Logic Shift
                // Pagi: 6 - 14 (Strictly < 14:00?) -> Let's say if clock in is 13:59 it's Pagi.
                // Sore: 14 - 22

                // User says: "Shift ini menentukan rekap harian" "Owner ingin tracking beberapa karyawan... menurut shift"

                if ($hour >= 6 && $hour < 14) {
                    $pagi++;
                } elseif ($hour >= 14 && $hour <= 22) { // 22:00 is technically late/closing? Allow up to 23?
                    // Usually gym closes late. Let's cover evening.
                    $sore++;
                }
                // What if someone clocks in at 23:00? Or 05:00?
                // For now stick to strict ranges user provided.
            }

            if ($pagi > 0 || $sore > 0) {
                $data->push([
                    'name' => $user->name,
                    'role' => $user->role,
                    'pagi' => $pagi,
                    'sore' => $sore,
                    'total' => $pagi + $sore
                ]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        $monthName = Carbon::createFromDate(null, $this->month, 1)->format('F');

        return [
            ['Rekap Shift Karyawan King Fitness Gym Semarang'],
            ['Bulan: ' . $monthName . ', Tahun: ' . $this->year],
            [''], // Spacer
            ['Nama Karyawan', 'Jumlah Shift Pagi', 'Jumlah Shift Sore', 'Jumlah Total Shift']
        ];
    }

    public function map($row): array
    {
        return [
            $row['name'],
            $row['pagi'],
            $row['sore'],
            $row['total']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merger Header
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']]],
        ];
    }
}
