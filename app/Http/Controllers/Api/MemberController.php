<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function importLegacy(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\LegacyMembersImport, $request->file('file'));
            return response()->json(['message' => 'Legacy data imported successfully!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    public function index()
    {
        // Natural Sort for "1 U", "2 U", "10 U"
        // Extract number from string and sort by it
        // MySQL: CAST(member_code AS UNSIGNED) -> picks up leading number
        $members = Member::orderByRaw('CAST(member_code AS UNSIGNED) ASC')->get();
        return response()->json($members);
    }

    public function show($id)
    {
        $member = Member::findOrFail($id);
        return response()->json(['data' => $member]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_code' => 'required|unique:members',
            'name' => 'required',
            'package_id' => 'required|exists:packages,id', // Must select a package
            'payment_method' => 'required', // qris, cash, transfer
            'phone' => 'nullable|string'
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $package = \App\Models\Package::findOrFail($request->package_id);

            // 1. Create Member
            $member = Member::create([
                'member_code' => $request->member_code,
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'category' => $package->category, // Auto-set category from package
                'status' => 'active',
                'current_expiry_date' => now()->addDays((int) $package->duration_days),
            ]);

            // 2. Create Transaction (Income)
            // Note: Assuming we have a logged in user or a default 'system' user. 
            // Since we are mocking auth often, we might use ID 1 (Owner) if auth()->id() is null.
            $userId = auth()->id() ?? 1;

            $transaction = \App\Models\Transaction::create([
                'user_id' => $userId,
                'member_id' => $member->id,
                'customer_name' => $member->name, // Fix: Save member name as customer name
                'total_amount' => $package->price,
                'payment_method' => $request->payment_method,
                'transaction_type' => 'membership',
                'membership_start_date' => now(),
                'membership_end_date' => now()->addDays((int) $package->duration_days),
            ]);

            // 3. Create Transaction Detail
            \App\Models\TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'item_name' => $package->name,
                'price' => $package->price,
                'qty' => 1,
                'subtotal' => $package->price
            ]);

            return response()->json([
                'message' => 'Member registered & Transaction recorded successfully',
                'data' => $member
            ]);
        });
    }

    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $request->validate([
            'member_code' => 'sometimes|required|unique:members,member_code,' . $id,
            'name' => 'sometimes|required',
            'status' => 'sometimes|required',
            'current_expiry_date' => 'sometimes|date',
            'package_id' => 'sometimes|exists:packages,id',
            'phone' => 'nullable|string'
        ]);

        // If package changes, maybe update category or extend date? 
        // For now simple update logic as per requirement "edit details".
        if ($request->has('package_id')) {
            $package = \App\Models\Package::find($request->package_id);
            if ($package) {
                $member->category = $package->category;
            }
        }

        // If member was PENDING and we are changing the Member Code to something real (not PENDING-...)
        // Then auto-activate the member.
        if ($member->status === 'pending' && $request->has('member_code')) {
            $newCode = $request->member_code;
            if ($newCode !== $member->member_code && !str_starts_with($newCode, 'PENDING-')) {
                // Auto Activate
                $request->merge(['status' => 'active']);
            }
        }

        $member->update($request->all());

        return response()->json([
            'message' => 'Member updated successfully',
            'data' => $member
        ]);
    }

    public function destroy($id)
    {
        $member = Member::findOrFail($id);

        // Check if there are related transactions? 
        // Usually we might want to soft delete or prevent delete if transactions exist.
        // But for "functionality delete", we will do simple delete or cascade.
        // Assuming simple delete for now.

        // Fix: Delete related transactions first to avoid Foreign Key Constraint Violation
        $transactions = \App\Models\Transaction::where('member_id', $id)->get();
        foreach ($transactions as $transaction) {
            $transaction->details()->delete();
            $transaction->delete();
        }

        $member->delete();

        return response()->json([
            'message' => 'Member deleted successfully'
        ]);
    }

    public function exportExcel()
    {
        try {
            $filename = "members-rekap-" . date('d-m-Y') . ".xlsx";
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MembersExport, $filename);
        } catch (\Exception $e) {
            \Log::error('Member Export Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to generate export',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    public function history($id)
    {
        $transactions = \App\Models\Transaction::where('member_id', $id)
            ->orderBy('created_at', 'desc')
            ->with(['details', 'user']) // Load details and cashier
            ->get();

        return response()->json([
            'data' => $transactions
        ]);
    }

    public function renew(Request $request, $id)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'payment_method' => 'required', // qris, cash, transfer
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
            $member = Member::findOrFail($id);
            $package = \App\Models\Package::findOrFail($request->package_id);

            // 1. Calculate New Expiry Date
            $now = now();
            $currentExpiry = \Carbon\Carbon::parse($member->current_expiry_date);

            // Logic for Start Date and End Date
            $startDate = null;

            if ($currentExpiry->gt($now)) {
                $startDate = $currentExpiry->copy(); // Starts when old one ends
                $newExpiry = $currentExpiry->addDays((int) $package->duration_days);
            } else {
                $startDate = $now->copy(); // Starts today
                $newExpiry = $now->copy()->addDays((int) $package->duration_days);
            }

            // 2. Update Member
            $member->update([
                'current_expiry_date' => $newExpiry,
                'status' => 'active', // Ensure active
                'category' => $package->category // Update category if package changes
            ]);

            // 3. Create Transaction
            $userId = auth()->id() ?? 1;

            $transaction = \App\Models\Transaction::create([
                'user_id' => $userId,
                'member_id' => $member->id,
                'customer_name' => $member->name,
                'total_amount' => $package->price,
                'payment_method' => $request->payment_method,
                'transaction_type' => 'membership',
                'membership_start_date' => $startDate,
                'membership_end_date' => $newExpiry,
            ]);

            \App\Models\TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'item_name' => $package->name, // e.g., "1 Bulan"
                'price' => $package->price,
                'qty' => 1,
                'subtotal' => $package->price
            ]);

            return response()->json([
                'message' => 'Membership renewed successfully',
                'data' => $member,
                'new_expiry' => $newExpiry->format('Y-m-d')
            ]);
        });
    }
}