<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nama', 'nip', 'password', 'role', 'jenis_kerja', 'status', 'id_pos', 'last_read_notifications'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];


    /**
     * Get the password for the user.
     * 
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getAuthIdentifierName()
    {
        return 'nip';
    }

    /**
     * Get the identifier for the user (NIP instead of email).
     */
    public function getEmailAttribute()
    {
        return $this->nip;
    }

    public function absensi()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function pos()
    {
        return $this->belongsTo(PosLokasi::class, 'id_pos');
    }

    public function posLokasi()
    {
        return $this->belongsTo(PosLokasi::class, 'id_pos');
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
        ];
    }
}
