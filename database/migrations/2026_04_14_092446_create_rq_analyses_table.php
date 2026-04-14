<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rq_analyses', function (Blueprint $table) {
            $table->id();

            // Jenis polutan: nitrat, pb, cd, ph, f
            $table->string('pollutant_type', 20);

            // === DATA RESPONDEN ===
            $table->integer('no_responden')->nullable();
            $table->string('nama');
            $table->float('umur');           // Tahun
            $table->float('wb');             // Berat Badan (kg)

            // === VARIABEL INPUT PAJANAN ===
            $table->float('f');              // Frekuensi pajanan (hari/tahun)
            $table->float('c');              // Konsentrasi polutan (mg/L)
            $table->float('r');              // Laju asupan (L/hari)
            $table->float('rfd');            // Reference Dose (mg/kg/hari)
            $table->float('tavg');           // Waktu rata-rata pajanan (hari)
            $table->float('dt_input');       // Durasi pajanan realtime/input (tahun)

            // === HASIL KALKULASI INTAKE (mg/kg/hari) ===
            $table->float('intake_realtime')->nullable();
            $table->float('intake_5th')->nullable();
            $table->float('intake_10th')->nullable();
            $table->float('intake_15th')->nullable();
            $table->float('intake_20th')->nullable();
            $table->float('intake_25th')->nullable();
            $table->float('intake_30th')->nullable();

            // === HASIL KALKULASI RISK QUOTIENT (RQ) ===
            $table->float('rq_realtime')->nullable();
            $table->float('rq_5th')->nullable();
            $table->float('rq_10th')->nullable();
            $table->float('rq_15th')->nullable();
            $table->float('rq_20th')->nullable();
            $table->float('rq_25th')->nullable();
            $table->float('rq_30th')->nullable();

            // Relasi ke user yang mengupload/menginput
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Sumber data: 'import' (dari Excel) atau 'manual' (form input)
            $table->enum('source', ['import', 'manual'])->default('manual');

            $table->timestamps();

            // Index untuk performa query filter per polutan
            $table->index(['pollutant_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rq_analyses');
    }
};
