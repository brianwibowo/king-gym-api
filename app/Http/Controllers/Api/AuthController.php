<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau Password salah!'], 401);
        }

        // Buat token untuk Flutter
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Berhasil',
            'access_token' => $token,
            'user' => [
                'name' => $user->name,
                'role' => $user->role,   
            ]
        ]);
    }
    // API UNTUK TAMBAH ADMIN
    public function registerAdmin(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin', // Dikunci agar hanya bisa nambah admin biasa
        ]);

        return response()->json(['message' => 'Admin baru berhasil dibuat!', 'user' => $user], 201);
    }

    // API UNTUK HAPUS ADMIN
    public function deleteAdmin($id) {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan!'], 404);
        }

        // MITIGASI: Jangan biarkan superadmin menghapus sesama superadmin atau dirinya sendiri lewat API ini
        if ($user->role === 'superadmin') {
            return response()->json(['message' => 'Tidak bisa menghapus akun Owner!'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'Admin berhasil dihapus!']);
    }
}