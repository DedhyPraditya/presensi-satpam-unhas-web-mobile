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
        Schema::create('pos_lokasi', function (Blueprint $结构) {
            $结构->id();
            $结构->string('nama_pos');
            $结构->double('latitude');
            $结构->double('longitude');
            $结构->integer('radius')->default(50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_lokasi');
    }
};
