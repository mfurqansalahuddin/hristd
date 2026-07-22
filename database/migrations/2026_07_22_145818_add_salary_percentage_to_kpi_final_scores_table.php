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
        Schema::table('kpi_final_scores', function (Blueprint $table) {
            // Persentase gaji hasil pemetaan grand_total_score -> kpi_salary_bands
            // (dari snapshot band di periode ini), disimpan permanen sebagai jejak audit.
            $table->unsignedTinyInteger('salary_percentage')->nullable()->after('grand_total_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_final_scores', function (Blueprint $table) {
            $table->dropColumn('salary_percentage');
        });
    }
};
