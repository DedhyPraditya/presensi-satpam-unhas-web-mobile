<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\PosLokasi;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Tentukan shift berdasarkan jam dan jenis kerja user.
     */
    public function determineShift(string $time, string $userJenis)
    {
        if ($userJenis === 'non_shift') {
            return 'non_shift_pagi';
        }

        $hour = Carbon::parse($time)->hour;
        
        // Logika shift UNHAS: Pagi (05-15), Malam (15-05)
        if ($hour >= 5 && $hour < 15) {
            return 'shift_pagi';
        } else {
            return 'shift_malam';
        }
    }

    /**
     * Hitung jarak antara dua koordinat (Haversine Formula).
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // dalam meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            
        return $angle * $earthRadius;
    }

    /**
     * Evaluasi apakah tepat waktu atau terlambat.
     */
    public function evaluateStatus(string $type, string $time, string $shift, string $userJenis)
    {
        $setting = AppSetting::first();
        
        $key = ($userJenis === 'non_shift') 
            ? "jam_{$type}_non_shift_pagi" 
            : "jam_{$type}_{$shift}";

        $targetTime = $setting->{$key};
        
        if ($type === 'masuk') {
            return Carbon::parse($time)->gt(Carbon::parse($targetTime)) ? 'Ya' : 'Tidak';
        } else {
            return Carbon::parse($time)->lt(Carbon::parse($targetTime)) ? 'Ya' : 'Tidak';
        }
    }
}
