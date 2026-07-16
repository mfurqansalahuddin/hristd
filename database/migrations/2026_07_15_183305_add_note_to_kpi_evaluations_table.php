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
        Schema::table('kpi_evaluations', function (Blueprint $table) {
            // "Alasan" per skor (§5.4/§15.4 mobile-app.md) — belum ada di migration awal.
            $table->text('note')->nullable()->after('score');
            $table->unique(['kpi_plan_id', 'evaluator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_evaluations', function (Blueprint $table) {
            $table->dropUnique(['kpi_plan_id', 'evaluator_id']);
            $table->dropColumn('note');
        });
    }
};
