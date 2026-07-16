<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kpi_component_weights', function (Blueprint $table) {
            $table->text('description')->nullable()->after('component');
        });

        // Backfill dengan ringkasan cara hitung tiap komponen (dipindah dari
        // konstanta KpiComponentWeight::DESCRIPTIONS, sekarang jadi editable manual di admin).
        $descriptions = [
            'KINERJA' => 'Rata-rata tertimbang skor rencana kerja dari 3 penilai (atasan & rekan sejawat)',
            'KEHADIRAN' => 'Rasio hari hadir terhadap hari kerja dalam periode',
            'APEL' => 'Rasio kehadiran apel pagi Senin dalam periode',
            'PAKAIAN_DINAS' => 'Dikurangi tiap aduan pelanggaran pakaian dinas yang tervalidasi',
            'INTEGRITAS' => 'Dikurangi tiap pelanggaran integritas yang tervalidasi, per kategori',
        ];

        foreach ($descriptions as $component => $description) {
            DB::table('kpi_component_weights')->where('component', $component)->update(['description' => $description]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_component_weights', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
