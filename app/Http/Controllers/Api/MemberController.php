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
        ]);

        $member = Member::create($request->all());
        return response()->json(['message' => 'Member berhasil ditambah', 'data' => $member]);
    }
}