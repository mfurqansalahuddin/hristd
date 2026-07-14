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
        Schema::create('kpi_component_weights', function (Blueprint $table) {
            $table->id();
            $table->string('component')->unique(); // KINERJA, KEHADIRAN, APEL, PAKAIAN_DINAS, INTEGRITAS
            $table->unsignedTinyInteger('weight');
            $table->timestamps();
        });

        $defaults = [
            'KINERJA' => 50,
            'KEHADIRAN' => 20,
            'APEL' => 5,
            'PAKAIAN_DINAS' => 5,
            'INTEGRITAS' => 20,
        ];

        foreach ($defaults as $component => $weight) {
            DB::table('kpi_component_weights')->insert([
                'component' => $component,
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
        Schema::dropIfExists('kpi_component_weights');
    }
};
