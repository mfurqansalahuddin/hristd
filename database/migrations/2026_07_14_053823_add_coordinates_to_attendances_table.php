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
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('clock_in_lat', 10, 8)->nullable()->after('clock_in');
            $table->decimal('clock_in_long', 11, 8)->nullable()->after('clock_in_lat');
            $table->decimal('clock_out_lat', 10, 8)->nullable()->after('clock_out');
            $table->decimal('clock_out_long', 11, 8)->nullable()->after('clock_out_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['clock_in_lat', 'clock_in_long', 'clock_out_lat', 'clock_out_long']);
        });
    }
};
