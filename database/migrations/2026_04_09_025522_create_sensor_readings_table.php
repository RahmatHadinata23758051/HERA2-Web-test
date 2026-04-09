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
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->float('ec');
            $table->float('tds');
            $table->float('ph');
            $table->float('suhu_air');
            $table->float('suhu_lingkungan');
            $table->float('kelembapan');
            $table->float('tegangan');
            $table->float('cr_estimated')->nullable();
            $table->string('status')->default('normal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
