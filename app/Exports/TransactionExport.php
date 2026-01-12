<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Mengambil data transaksi beserta data member yang terkait
        return Transaction::with('member')->get();
    }

    public function headings(): array
    {
        return ["ID", "Nama Member", "Total", "Metode Bayar", "Tipe", "Tanggal"];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->member->name ?? 'Umum', // Jika tidak ada member, tampilkan 'Umum'
            $transaction->total_amount,
            strtoupper($transaction->payment_method),
            ucfirst($transaction->transaction_type),
            $transaction->created_at->format('d-m-Y H:i'),
        ];
    }
}
