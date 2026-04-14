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
        Schema::create('field_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->float('suhu_air')->nullable();
            $table->float('suhu_lingkungan')->nullable();
            $table->float('kelembapan')->nullable();
            $table->float('ec')->nullable();
            $table->float('tds')->nullable();
            $table->float('ph')->nullable();
            $table->float('tegangan')->nullable();
            $table->float('cr_estimated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_tests');
    }
};
