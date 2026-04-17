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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->dateTime('ceklog_masuk')->nullable();
            $table->dateTime('ceklog_pulang')->nullable();
            $table->enum('jenis_kerja', ['non_shift_pagi', 'shift_pagi', 'shift_malam'])->default('non_shift_pagi');
            $table->string('terlambat', 20)->nullable();
            $table->string('cepat_pulang', 20)->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->text('foto_masuk')->nullable(); // Menggunakan text karena base64 bisa panjang
            $table->text('foto_pulang')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
