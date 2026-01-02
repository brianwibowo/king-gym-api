<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Member;
use App\Models\Product;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'user_id' => 'required',
            'total_amount' => 'required|integer',
            'payment_method' => 'required|in:cash,qris,transfer',
            'transaction_type' => 'required|in:membership,product,mix',
            'items' => 'required|array', // Daftar belanjaan
        ]);

        // 2. Gunakan DB Transaction agar jika satu gagal, semua batal (Data tetap konsisten)
        return DB::transaction(function () use ($request) {
            
            // Simpan Header Transaksi
            $transaction = Transaction::create([
                'user_id' => $request->user_id,
                'member_id' => $request->member_id,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'transaction_type' => $request->transaction_type,
            ]);

            foreach ($request->items as $item) {
                // Simpan Detail Transaksi
                $transaction->details()->create([
                    'item_name' => $item['name'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

                // LOGIKA A: Jika yang dibeli adalah Produk (Minuman)
                $product = Product::where('name', $item['name'])->first();
                if ($product) {
                    $product->decrement('stock', $item['qty']); // Stok otomatis berkurang
                }

                // LOGIKA B: Jika yang dibeli adalah Membership
                $package = Package::where('name', $item['name'])->first();
                if ($package && $request->member_id) {
                    $member = Member::find($request->member_id);
                    
                    // Cek apakah member masih aktif atau sudah expired
                    $startDate = Carbon::now();
                    if ($member->current_expiry_date && Carbon::parse($member->current_expiry_date)->isFuture()) {
                        // Jika masih aktif, perpanjang dari tanggal kadaluarsa terakhir
                        $startDate = Carbon::parse($member->current_expiry_date);
                    }

                    $member->update([
                        'status' => 'active',
                        'current_expiry_date' => $startDate->addDays($package->duration_days)
                    ]);
                }
            }

            return response()->json([
                'message' => 'Transaksi Berhasil Dicatat!',
                'data' => $transaction->load('details')
            ], 201);
        });
    }
}