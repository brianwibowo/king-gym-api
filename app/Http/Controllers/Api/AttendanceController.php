<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    // Histori Presensi User (Updated for Superadmin View)
    public function index(Request $request)
    {
        $query = Attendance::with('user'); // Eager load user data

        // Jika BUKAN Superadmin, hanya lihat data sendiri
        if ($request->user()->role !== 'superadmin') {
            $query->where('user_id', $request->user()->id)
                ->take(30); // Limit user history
        } else {
            // Jika Superadmin, filter berdasarkan tanggal (Default: Hari Ini)
            // User request: "melihat semua presensi yang ada, ditentukan oleh tanggal"
            $date = $request->input('date', Carbon::today()->toDateString());
            $query->whereDate('created_at', $date);
        }

        $attendances = $query->orderBy('created_at', 'desc')->get();

        return response()->json($attendances);
    }

    // Clock In
    public function clockIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'photo' => 'nullable|image|max:2048'
        ]);

        // Cek apakah user sedang Check In (belum Check Out)
        // Logic baru: Boleh berkali-kali sehari, asalkan sesi sebelumnya sudah selesai (Clock Out)
        $activeSession = Attendance::where('user_id', $request->user()->id)
            ->whereNull('clock_out')
            ->first();

        if ($activeSession) {
            return response()->json(['message' => 'Anda masih status Clock In! Silahkan Clock Out sesi sebelumnya dulu.'], 400);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance_photos', 'public');
        }

        $attendance = Attendance::create([
            'user_id' => $request->user()->id,
            'clock_in' => now(),
            'lat_in' => $request->latitude,
            'long_in' => $request->longitude,
            'work_description' => $request->work_description,
            'photo_in' => $photoPath
        ]);

        return response()->json([
            'message' => 'Berhasil Clock In!',
            'data' => $attendance
        ]);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'photo' => 'nullable|image|max:2048'
        ]);

        // Cari sesi yang aktif (belum clock out)
        $attendance = Attendance::where('user_id', $request->user()->id)
            ->whereNull('clock_out')
            ->latest() // Ambil yang paling baru jika ada multiple (harusnya cuma 1 sih)
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Anda belum melakukan clock in (Tidak ada sesi aktif)!'], 404);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance_photos', 'public');
        }

        $attendance->update([
            'clock_out' => now(),
            'lat_out' => $request->latitude,
            'long_out' => $request->longitude,
            'photo_out' => $photoPath
        ]);

        return response()->json([
            'message' => 'Berhasil Clock Out!',
            'data' => $attendance
        ]);
    }
}
