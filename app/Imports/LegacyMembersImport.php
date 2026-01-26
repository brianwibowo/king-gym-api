<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LegacyMembersImport implements ToModel, WithStartRow
{
    /**
     * Skip header
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Helper: Parse tanggal LEGACY
     * Aturan:
     * - Excel Serial (>30000) -> Excel Date
     * - Slash (/) -> MDY (US) -> 12/08 = Des 8
     * - Dash (-) -> DMY (Indo) -> 26-08 = Agt 26
     */
    private function parseIndoDate($val): ?Carbon
    {
        if (!$val)
            return null;
        $val = trim($val);

        // 1. Excel Serial
        if (is_numeric($val) && $val > 30000 && !str_contains($val, '-') && !str_contains($val, '/')) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject($val));
            } catch (\Exception $e) {
                return null;
            }
        }

        // 2. Manual Explode Logic
        if (str_contains($val, '/')) {
            // SLASH: Prioritas MDY (Bulan/Tanggal/Tahun)
            $parts = explode('/', $val);
            if (count($parts) !== 3)
                return null;

            $p1 = (int) $parts[0]; // M atau D?
            $p2 = (int) $parts[1]; // D atau M?
            $y = (int) $parts[2]; // Year

            // Fix 2-digit Year
            if ($y < 100)
                $y += 2000;

            // Coba MDY (p1=Month, p2=Day)
            // checkdate(month, day, year)
            if (checkdate($p1, $p2, $y)) {
                return Carbon::create($y, $p1, $p2)->startOfDay();
            }

            // Jika gagal (misal p1=15), Coba DMY (p1=Day, p2=Month)
            if (checkdate($p2, $p1, $y)) {
                return Carbon::create($y, $p2, $p1)->startOfDay();
            }

        } elseif (str_contains($val, '-')) {
            // DASH: Prioritas DMY (Tanggal-Bulan-Tahun) - Format Indo Pure
            $parts = explode('-', $val);
            if (count($parts) !== 3)
                return null;

            $d = (int) $parts[0];
            $m = (int) $parts[1];
            $y = (int) $parts[2];

            if ($y < 100)
                $y += 2000;

            if (checkdate($m, $d, $y)) {
                return Carbon::create($y, $m, $d)->startOfDay();
            }
        }

        return null;
    }

    public function model(array $row)
    {
        // 0: Member Code, 1: Nama, 2: Alamat, 3: Telp, 4: Foto, 5+: Bulanan

        $memberCode = $row[0] ?? null;
        $name = $row[1] ?? null;

        if (!$name) {
            return null;
        }

        $address = $row[2] ?? null;
        $phone = $row[3] ?? null;

        /**
         * ==============================
         * 1. TENTUKAN MAX EXPIRY DATE
         * ==============================
         */
        $maxDate = null;

        for ($i = 5; $i < count($row); $i++) {
            $date = $this->parseIndoDate($row[$i]);

            if ($date) {
                if (!$maxDate || $date->gt($maxDate)) {
                    $maxDate = $date;
                }
            }
        }

        // Jika tidak ada tanggal sama sekali
        if (!$maxDate) {
            $maxDate = Carbon::yesterday();
        }

        $status = $maxDate->isFuture() || $maxDate->isToday()
            ? 'active'
            : 'inactive';

        /**
         * ==============================
         * 2. UPDATE / CREATE MEMBER
         * ==============================
         */
        $member = null;

        if ($memberCode) {
            $member = Member::where('member_code', $memberCode)->first();
        }

        if (!$member) {
            $member = Member::where('name', $name)->first();
        }

        if ($member) {
            $member->update([
                'member_code' => $memberCode ?: $member->member_code,
                'address' => $address ?: $member->address,
                'phone' => $phone ?: $member->phone,
                'current_expiry_date' => $maxDate,
                'status' => $status,
                'category' => 'Umum',
            ]);
        } else {
            $member = Member::create([
                'member_code' => $memberCode ?: 'OLD-' . strtoupper(uniqid()),
                'name' => $name,
                'address' => $address,
                'phone' => $phone,
                'category' => 'Umum',
                'status' => $status,
                'current_expiry_date' => $maxDate,
            ]);
        }

        /**
         * ==============================
         * 3. GENERATE RIWAYAT TRANSAKSI
         * ==============================
         */
        for ($i = 5; $i < count($row); $i++) {
            $expiryDate = $this->parseIndoDate($row[$i]);

            if (!$expiryDate) {
                continue;
            }

            $exists = Transaction::where('member_id', $member->id)
                ->whereDate('membership_end_date', $expiryDate->format('Y-m-d'))
                ->exists();

            if ($exists) {
                continue;
            }

            $trx = Transaction::create([
                'user_id' => 1,
                'member_id' => $member->id,
                'customer_name' => $member->name,
                'total_amount' => 0,
                'payment_method' => 'cash',
                'transaction_type' => 'membership',
                'membership_start_date' => null,
                'membership_end_date' => $expiryDate,
                'created_at' => $expiryDate,
                'updated_at' => $expiryDate,
            ]);

            TransactionDetail::create([
                'transaction_id' => $trx->id,
                'item_name' => 'Membership (' . $expiryDate->format('M Y') . ')',
                'price' => 0,
                'qty' => 1,
                'subtotal' => 0,
            ]);
        }

        return $member;
    }
}
