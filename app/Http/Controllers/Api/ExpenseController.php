<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        // Filter by date (default today)
        $date = $request->input('date', date('Y-m-d'));

        $query = Expense::with('user')
            ->whereDate('date', $date)
            ->orderBy('created_at', 'desc');

        $expenses = $query->get();
        $total = $query->sum('amount');

        return response()->json([
            'data' => $expenses,
            'summary' => [
                'total_expense' => $total,
                'count' => $expenses->count()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'date' => 'sometimes|date', // Optional, default now
            'category' => 'sometimes|string'
        ]);

        $expense = Expense::create([
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date ?? now(),
            'category' => $request->category ?? 'General',
            'user_id' => auth()->id() ?? 1
        ]);

        return response()->json([
            'message' => 'Expense added successfully',
            'data' => $expense
        ], 201);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully'
        ]);
    }
}
