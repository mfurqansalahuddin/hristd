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
        Schema::table('violation_reports', function (Blueprint $table) {
            // Wajib diisi kalau category=INTEGRITAS (§15.5 mobile-app.md) — kolom ini
            // belum ada di migration awal walau sudah didokumentasikan sebagai request field.
            $table->foreignId('integrity_category_id')->nullable()->after('category')
                ->constrained('kpi_integrity_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violation_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('integrity_category_id');
        });
    }
};
