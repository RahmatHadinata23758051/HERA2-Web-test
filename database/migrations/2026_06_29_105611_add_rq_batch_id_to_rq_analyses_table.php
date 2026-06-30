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
        Schema::table('rq_analyses', function (Blueprint $table) {
            $table->foreignId('rq_batch_id')->nullable()->after('user_id')->constrained('rq_batches')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rq_analyses', function (Blueprint $table) {
            $table->dropForeign(['rq_batch_id']);
            $table->dropColumn('rq_batch_id');
        });
    }
};
