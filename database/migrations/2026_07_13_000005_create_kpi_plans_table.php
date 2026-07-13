<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();
            $table->text('target_description');
            $table->decimal('weight', 5, 2); // akumulasi bobot per user maks 50

            $table->unsignedTinyInteger('self_assessment_score')->nullable();
            $table->text('self_assessment_note')->nullable();
            $table->string('status')->default('DRAFT'); // DRAFT, APPROVED, REJECTED

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_plans');
    }
};
