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
    public function index(Request $request)
    {
        $query = Transaction::with(['details', 'member', 'user'])
            ->orderBy('created_at', 'desc');

        // Filter by Date Range (prioritize range if provided)
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            // Need to cover the whole day of end_date
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } else if ($request->has('date') && $request->date) {
            $query->whereDate('created_at', $request->date);
        }

        // Optional: Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('transaction_type', $request->type);
        }

        $transactions = $query->get();

        return response()->json([
            'data' => $transactions,
            'summary' => [
                'total_income' => $transactions->sum('total_amount'),
                'count' => $transactions->count()
            ]
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            // 'user_id' => 'required', // Auto-detected from token
            'total_amount' => 'required|integer',
            'payment_method' => 'required|in:cash,qris,transfer',
            'transaction_type' => 'required|in:membership,product,mix',
            'items' => 'required|array', // Daftar belanjaan
            'created_at' => 'nullable|date',
        ]);

        // 2. Gunakan DB Transaction agar jika satu gagal, semua batal (Data tetap konsisten)
        return DB::transaction(function () use ($request) {

            // Determine Customer Name
            $customerName = $request->customer_name;
            if (!$customerName && $request->member_id) {
                $memberData = Member::find($request->member_id);
                $customerName = $memberData ? $memberData->name : 'Guest';
            }

            // Simpan Header Transaksi
            // Simpan Header Transaksi
            $transaction = new Transaction();
            $transaction->fill([
                'user_id' => auth()->id(),
                'member_id' => $request->member_id,
                'customer_name' => $customerName ?: 'Guest',
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'transaction_type' => $request->transaction_type,
            ]);

            // Manually set timestamps to bypass fillable protection if needed
            $transaction->created_at = $request->created_at ? Carbon::parse($request->created_at) : Carbon::now();
            $transaction->updated_at = Carbon::now();
            $transaction->save();

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
                if ($package) {
                    // Check if Member ID is provided, IF NOT, Auto-Create "Pending" Member
                    $memberId = $request->member_id;
                    $isAutoCreated = false;

                    if (!$memberId) {
                        // Create Pending Member
                        $newMember = Member::create([
                            'name' => $request->customer_name ?: 'Guest Member',
                            'member_code' => 'PENDING-' . time() . rand(100, 999),
                            'status' => 'pending',
                            'phone' => '-',
                            'address' => '-'
                        ]);
                        $memberId = $newMember->id;
                        $isAutoCreated = true;

                        // Update transaction header to link to this new member
                        $transaction->update(['member_id' => $memberId]);
                    }

                    $member = Member::find($memberId);

                    // Cek apakah member masih aktif atau sudah expired
                    $startDate = Carbon::now();
                    if ($member->current_expiry_date && Carbon::parse($member->current_expiry_date)->isFuture()) {
                        // Jika masih aktif, perpanjang dari tanggal kadaluarsa terakhir
                        $startDate = Carbon::parse($member->current_expiry_date);
                    }

                    // Keep pending status if auto-created, otherwise active
                    $newStatus = $isAutoCreated ? 'pending' : 'active';

                    // Allow pending members to become active ONLY if manually edited later? 
                    // Or should they be active immediately for Gate Access? 
                    // User said: "statusnya pending dan belum ada nomor membership". 
                    // So we stick to 'pending'.

                    // Use existing status if it was already active/pending, don't revert to active if pending?
                    // Actually, if I buy renewal, it becomes active.
                    // If AutoCreated, force pending.

                    $member->update([
                        'status' => $isAutoCreated ? 'pending' : 'active',
                        'current_expiry_date' => $startDate->copy()->addDays((int) $package->duration_days)
                    ]);

                    // IMPORTANT: Update Transaction with Membership Dates for History/Rollback
                    $transaction->update([
                        'membership_start_date' => $startDate,
                        'membership_end_date' => $startDate->copy()->addDays((int) $package->duration_days)
                    ]);
                }
            }

            return response()->json([
                'message' => 'Transaksi Berhasil Dicatat!',
                'data' => $transaction->load('details')
            ], 201);
        });
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $request->validate([
            'customer_name' => 'nullable|string',
            'payment_method' => 'sometimes|in:cash,qris,transfer'
        ]);

        $transaction->update($request->only(['customer_name', 'payment_method']));

        return response()->json([
            'message' => 'Transaction updated successfully',
            'data' => $transaction
        ]);
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $memberId = $transaction->member_id;

        // Optional: Restore stock if needed, but for now simple delete
        $transaction->details()->delete();
        $transaction->delete();

        // RECALCULATE MEMBER STATUS if this was a membership transaction
        if ($memberId && $transaction->transaction_type === 'membership') {
            $member = Member::find($memberId);
            if ($member) {
                // Find the latest remaining membership transaction
                $latestTrx = Transaction::where('member_id', $memberId)
                    ->where('transaction_type', 'membership')
                    ->orderBy('membership_end_date', 'desc') // Use end_date as priority
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($latestTrx && $latestTrx->membership_end_date) {
                    $expiry = Carbon::parse($latestTrx->membership_end_date);

                    $member->update([
                        'current_expiry_date' => $latestTrx->membership_end_date, // Sync with latest end date
                        'status' => $expiry->isFuture() ? 'active' : 'inactive'
                    ]);
                } else {
                    // No membership transactions left
                    $member->update([
                        'current_expiry_date' => null,
                        'status' => 'inactive'
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Transaction deleted successfully, member status updated'
        ]);
    }
}