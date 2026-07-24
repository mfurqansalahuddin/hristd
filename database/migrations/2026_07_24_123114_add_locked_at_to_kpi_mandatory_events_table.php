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
        Schema::table('kpi_mandatory_events', function (Blueprint $table) {
            $table->timestamp('locked_at')->nullable()->after('grouping_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_mandatory_events', function (Blueprint $table) {
            $table->dropColumn('locked_at');
        });
    }
};
