<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'absensi';
    protected $fillable = [
        'user_id', 'tanggal', 'jam_masuk', 'jam_pulang', 
        'ceklog_masuk', 'ceklog_pulang', 'jenis_kerja', 
        'terlambat', 'cepat_pulang', 'latitude', 'longitude',
        'foto_masuk', 'foto_pulang',
    ];

    const UPDATED_AT = null; // Legacy table doesn't have updated_at

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
