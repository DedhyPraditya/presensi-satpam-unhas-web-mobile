<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'pengaturan';
    protected $fillable = [
        'jam_masuk_non_shift_pagi', 'jam_pulang_non_shift_pagi',
        'jam_masuk_shift_pagi', 'jam_pulang_shift_pagi',
        'jam_masuk_shift_malam', 'jam_pulang_shift_malam'
    ];
    public $timestamps = false; // Legacy table uses updated_at timestamp but not created_at
}
