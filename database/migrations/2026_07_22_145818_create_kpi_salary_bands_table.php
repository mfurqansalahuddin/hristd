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
        Schema::create('kpi_salary_bands', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_score', 5, 2)->nullable(); // null = catch-all (band terendah, skor <= ambang band di atasnya)
            $table->unsignedTinyInteger('percentage');
            $table->timestamps();
        });

        // Urut dari ambang tertinggi ke terendah, band terakhir (null) jadi catch-all.
        $bands = [
            ['min_score' => 95, 'percentage' => 100],
            ['min_score' => 85, 'percentage' => 95],
            ['min_score' => 80, 'percentage' => 85],
            ['min_score' => 60, 'percentage' => 75],
            ['min_score' => null, 'percentage' => 60],
        ];

        foreach ($bands as $band) {
            DB::table('kpi_salary_bands')->insert([
                'min_score' => $band['min_score'],
                'percentage' => $band['percentage'],
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
        Schema::dropIfExists('kpi_salary_bands');
    }
};
