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
        Schema::create('kpi_final_score_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_final_score_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_extra_criterion_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_final_score_extras');
    }
};
