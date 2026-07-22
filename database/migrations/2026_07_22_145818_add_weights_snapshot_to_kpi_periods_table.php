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
        Schema::table('kpi_periods', function (Blueprint $table) {
            // Salinan kpi_component_weights + kpi_evaluator_weights + kpi_integrity_source_weights
            // + kpi_salary_bands saat periode dibuka — supaya periode yang sudah berjalan/CLOSED
            // tidak ikut berubah kalau master data diedit belakangan (plan.md §13 riwayat bukti
            // bobot ini sering berubah).
            $table->json('weights_snapshot')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_periods', function (Blueprint $table) {
            $table->dropColumn('weights_snapshot');
        });
    }
};
