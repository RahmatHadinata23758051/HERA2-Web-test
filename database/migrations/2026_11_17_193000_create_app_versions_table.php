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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 30); // e.g. "1.0.0"
            $table->integer('build_number')->unsigned()->index(); // e.g. 1, 2
            $table->text('download_url'); // Direct APK link or GitHub Release asset link
            $table->text('changelog')->nullable(); // Markdown/plain text list of updates
            $table->string('file_size', 50)->nullable(); // e.g. "28.5 MB"
            $table->boolean('is_force_update')->default(false); // If true, blocks old versions
            $table->boolean('is_active')->default(true)->index(); // Only active versions are served
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
