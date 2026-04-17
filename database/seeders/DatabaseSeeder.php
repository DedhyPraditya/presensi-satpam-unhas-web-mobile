<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Data POS Awal (Contoh: Rektorat UNHAS)
        $pos = \App\Models\PosLokasi::create([
            'nama_pos' => 'Kantor Pusat / Rektorat',
            'latitude' => -5.131552,
            'longitude' => 119.489155,
            'radius' => 100, // 100 meter
        ]);

        // 2. Buat Pengaturan Default Sistem
        \App\Models\AppSetting::create([
            'jam_masuk_non_shift_pagi' => '07:30:00',
            'jam_pulang_non_shift_pagi' => '17:00:00',
            'jam_masuk_shift_pagi' => '07:00:00',
            'jam_pulang_shift_pagi' => '19:00:00',
            'jam_masuk_shift_malam' => '19:00:00',
            'jam_pulang_shift_malam' => '07:00:00'
        ]);


        // 2. Buat User Admin Utama
        \App\Models\User::create([
            'nama' => 'Administrator Satpam',
            'nip' => 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'verified'
        ]);

        // 3. Buat User Personel Contoh (Untuk Tes Login Mobile)
        \App\Models\User::create([
            'nama' => 'Andi Personel',
            'nip' => '12345',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'anggota',
            'status' => 'verified',
            'id_pos' => $pos->id,
            'jenis_kerja' => 'shift'
        ]);
    }
}
