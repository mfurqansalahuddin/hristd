<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_final_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();

            $table->decimal('score_kinerja', 5, 2);    // Bobot 50%
            $table->decimal('score_kehadiran', 5, 2);  // Bobot 20%
            $table->decimal('score_apel', 5, 2);       // Bobot 5%
            $table->decimal('score_pakaian', 5, 2);    // Bobot 5%
            $table->decimal('score_integritas', 5, 2); // Bobot 20%

            $table->decimal('grand_total_score', 5, 2);

            $table->timestamps();

            $table->unique(['user_id', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_final_scores');
    }
};
