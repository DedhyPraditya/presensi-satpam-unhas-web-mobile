<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    protected $table = 'laporan';
    protected $fillable = [
        'user_id', 'judul', 'tanggal', 'jam', 'deskripsi', 'foto', 'latitude', 'longitude'
    ];

    public $timestamps = false; // Memakai created_at bawaan DB atau manual
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
