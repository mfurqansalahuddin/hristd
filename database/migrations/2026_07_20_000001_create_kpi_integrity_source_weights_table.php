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
        Schema::create('kpi_integrity_source_weights', function (Blueprint $table) {
            $table->id();
            $table->string('source')->unique(); // PENILAI_1, PENILAI_2, PENILAI_3, ADUAN_PERUSAHAAN
            $table->unsignedTinyInteger('weight'); // sum harus 100
            $table->timestamps();
        });

        $defaults = ['PENILAI_1' => 25, 'PENILAI_2' => 25, 'PENILAI_3' => 25, 'ADUAN_PERUSAHAAN' => 25];

        foreach ($defaults as $source => $weight) {
            DB::table('kpi_integrity_source_weights')->insert([
                'source' => $source,
                'weight' => $weight,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_integrity_source_weights');
    }
};
