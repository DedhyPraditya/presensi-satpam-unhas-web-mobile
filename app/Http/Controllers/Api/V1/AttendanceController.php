<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\PosLokasi;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'required|string|max:10485760', // Max ~7.5MB-10MB base64 string
        ]);

        $user = $request->user();
        $date = Carbon::now()->toDateString();
        $time = Carbon::now()->toTimeString();
        $datetime = Carbon::now()->toDateTimeString();

        // Check distance
        if ($user->id_pos) {
            $pos = PosLokasi::find($user->id_pos);
            if ($pos) {
                $distance = $this->attendanceService->calculateDistance(
                    $request->latitude, $request->longitude,
                    $pos->latitude, $pos->longitude
                );
                
                if ($distance > $pos->radius) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda berada di luar radius POS (' . round($distance) . 'm). Silakan dekati area POS.'
                    ], 403);
                }
            }
        }

        // Check if already checked in
        $existing = Attendance::where('user_id', $user->id)
            ->where('tanggal', $date)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah ceklok masuk hari ini.'
            ], 400);
        }

        // Handle Image
        $imageName = $user->id . '_masuk_' . time() . '.jpg';
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->foto));
        Storage::disk('public')->put('attendances/' . $imageName, $imageData);

        // Logic
        $shift = $this->attendanceService->determineShift($time, $user->jenis_kerja);
        $resMasuk = $this->attendanceService->evaluateStatus('masuk', $time, $shift, $user->jenis_kerja);
        $terlambat = $resMasuk['status'];
        $menitTerlambat = $resMasuk['duration'];

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'tanggal' => $date,
            'jam_masuk' => $time,
            'ceklog_masuk' => $datetime,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'foto_masuk' => 'attendances/' . $imageName,
            'jenis_kerja' => $shift,
            'terlambat' => $terlambat,
            'menit_terlambat' => $menitTerlambat
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '✔ Ceklok MASUK berhasil.',
            'data' => $attendance
        ]);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'required',
        ]);

        $user = $request->user();
        $date = Carbon::now()->toDateString();
        $time = Carbon::now()->toTimeString();
        $datetime = Carbon::now()->toDateTimeString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('tanggal', $date)
            ->first();

        if (!$attendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda belum ceklok masuk.'
            ], 400);
        }

        if ($attendance->jam_pulang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah ceklok pulang hari ini.'
            ], 400);
        }

        // Handle Image
        $imageName = $user->id . '_pulang_' . time() . '.jpg';
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->foto));
        Storage::disk('public')->put('attendances/' . $imageName, $imageData);

        // Logic
        $resPulang = $this->attendanceService->evaluateStatus('pulang', $time, $attendance->jenis_kerja, $user->jenis_kerja);
        $cepatPulang = $resPulang['status'];

        $attendance->update([
            'jam_pulang' => $time,
            'ceklog_pulang' => $datetime,
            'foto_pulang' => 'attendances/' . $imageName,
            'cepat_pulang' => $cepatPulang,
            // We update location for checkout too
            'latitude_pulang' => $request->latitude, 
            'longitude_pulang' => $request->longitude,
        ]);

        // Note: I noticed some discrepancy in field names for checkout lat/lng in my model earlier.
        // Let's stick to the base absensi table which only had one lat/lng column.
        // Legacy only updated 'latitude' and 'longitude' on checkout too.
        
        $attendance->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '✔ Ceklok PULANG berhasil.',
            'data' => $attendance
        ]);
    }

    public function history(Request $request)
    {
        $history = Attendance::where('user_id', $request->user()->id)
            ->orderBy('tanggal', 'desc')
            ->limit(30)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }
}
