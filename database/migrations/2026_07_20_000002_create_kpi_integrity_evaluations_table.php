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
        Schema::create('kpi_integrity_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();
            $table->foreignId('kpi_integrity_category_id')->nullable()->constrained('kpi_integrity_categories')->nullOnDelete();
            $table->string('evaluator_role'); // PENILAI_1, PENILAI_2, PENILAI_3
            $table->string('decision'); // BIARIN, KURANGIN
            $table->text('description')->nullable(); // wajib kalau decision=KURANGIN
            $table->string('photo_path')->nullable(); // wajib kalau decision=KURANGIN
            $table->timestamps();

            $table->unique(['evaluator_id', 'reported_user_id', 'period_id', 'kpi_integrity_category_id'], 'kpi_integrity_eval_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_integrity_evaluations');
    }
};
