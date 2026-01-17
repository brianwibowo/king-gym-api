<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::orderBy('name', 'asc')->get();
        return response()->json($members);
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_code' => 'required|unique:members',
            'name' => 'required',
            'package_id' => 'required|exists:packages,id', // Must select a package
            'payment_method' => 'required', // qris, cash, transfer
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $package = \App\Models\Package::findOrFail($request->package_id);

            // 1. Create Member
            $member = Member::create([
                'member_code' => $request->member_code,
                'name' => $request->name,
                'address' => $request->address,
                'category' => $package->category, // Auto-set category from package
                'status' => 'active',
                'current_expiry_date' => now()->addDays($package->duration_days),
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
            'package_id' => 'sometimes|exists:packages,id'
        ]);

        // If package changes, maybe update category or extend date? 
        // For now simple update logic as per requirement "edit details".
        if ($request->has('package_id')) {
            $package = \App\Models\Package::find($request->package_id);
            if ($package) {
                $member->category = $package->category;
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

        $member->delete();

        return response()->json([
            'message' => 'Member deleted successfully'
        ]);
    }
}