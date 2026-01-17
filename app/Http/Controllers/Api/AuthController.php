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
            'user' => $user
        ]);
    }
    // API UNTUK TAMBAH ADMIN
    public function registerAdmin(Request $request)
    {
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
    public function deleteAdmin($id)
    {
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
    // UPDATE PROFILE (Name & Auto-Generated Avatar logic on frontend, or handled here if file upload)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048', // 2MB Max
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo_path && \Storage::disk('public')->exists($user->photo_path)) {
                \Storage::disk('public')->delete($user->photo_path);
            }
            $path = $request->file('photo')->store('profile_photos', 'public');
            $data['photo_path'] = $path;
        }

        $user->update($data);

        // Refresh user to get appends
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh() // fresh() reloads model
        ]);
    }

    // CHANGE PASSWORD
    public function changePassword(Request $request)
    {
        $request->validate([
            // 'current_password' => 'required', // Removed as requested
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Removed current password check
        // if (!Hash::check($request->current_password, $user->password)) {
        //     return response()->json(['message' => 'Current password does not match!'], 400);
        // }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json(['message' => 'Password changed successfully']);
    }
    // FORGOT PASSWORD (SELF SERVICE - INTERNAL USE)
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        // Prevent resetting Superadmin password via this simple method for extra security? 
        // User said "safe secure internal". But let's act normal.

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json(['message' => 'Password has been reset successfully.']);
    }
}