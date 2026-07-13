<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_evaluation_id')->constrained('kpi_evaluations')->cascadeOnDelete();

            $table->text('reason');
            $table->string('evidence_file_path')->nullable();

            $table->string('status')->default('PENDING'); // PENDING, ACCEPTED, REJECTED
            $table->text('resolution_note')->nullable();
            $table->unsignedTinyInteger('revised_score')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_disputes');
    }
};
