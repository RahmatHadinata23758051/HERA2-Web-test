<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('field_tests', function (Blueprint $table) {
            if (!Schema::hasColumn('field_tests', 'altitude')) {
                $table->decimal('altitude', 10, 2)->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('field_tests', function (Blueprint $table) {
            if (Schema::hasColumn('field_tests', 'altitude')) {
                $table->dropColumn('altitude');
            }
        });
    }
};
