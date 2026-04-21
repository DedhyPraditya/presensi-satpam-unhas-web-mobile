<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'absensi';
    protected $fillable = [
        'user_id', 'tanggal', 'jam_masuk', 'jam_pulang', 
        'ceklog_masuk', 'ceklog_pulang', 'jenis_kerja', 
        'terlambat', 'menit_terlambat', 'cepat_pulang', 'latitude', 'longitude',
        'foto_masuk', 'foto_pulang',
    ];

    const UPDATED_AT = null; // Legacy table doesn't have updated_at

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFormattedTerlambatAttribute()
    {
        if (!$this->menit_terlambat || $this->menit_terlambat <= 0) return null;
        
        $hours = floor($this->menit_terlambat / 60);
        $minutes = $this->menit_terlambat % 60;
        
        if ($hours > 0) {
            return "{$hours}j {$minutes}m";
        }
        
        return "{$minutes}m";
    }
}
