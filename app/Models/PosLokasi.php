<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosLokasi extends Model
{
    protected $table = 'pos_lokasi';
    protected $fillable = ['nama_pos', 'latitude', 'longitude', 'radius'];
    public $timestamps = false;
}
