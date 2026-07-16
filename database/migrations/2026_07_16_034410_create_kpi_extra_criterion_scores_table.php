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
        Schema::create('kpi_extra_criterion_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_extra_criterion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->string('evaluator_role'); // PENILAI_1, PENILAI_2, PENILAI_3 — sama pola kpi_evaluations
            $table->unsignedTinyInteger('score');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['kpi_extra_criterion_id', 'user_id', 'period_id', 'evaluator_id'], 'kpi_extra_score_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_extra_criterion_scores');
    }
};
