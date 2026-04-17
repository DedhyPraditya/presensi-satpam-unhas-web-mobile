<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nip')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'anggota'])->default('anggota');
            $table->enum('jenis_kerja', ['non_shift', 'shift'])->default('non_shift');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->foreignId('id_pos')->nullable()->constrained('pos_lokasi')->nullOnDelete();
            $table->json('last_read_notifications')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
