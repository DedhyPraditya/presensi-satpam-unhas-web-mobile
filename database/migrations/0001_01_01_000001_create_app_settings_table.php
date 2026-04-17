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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->time('jam_masuk_non_shift_pagi')->default('07:30:00');
            $table->time('jam_pulang_non_shift_pagi')->default('17:00:00');
            $table->time('jam_masuk_shift_pagi')->default('07:00:00');
            $table->time('jam_pulang_shift_pagi')->default('19:00:00');
            $table->time('jam_masuk_shift_malam')->default('19:00:00');
            $table->time('jam_pulang_shift_malam')->default('07:00:00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
