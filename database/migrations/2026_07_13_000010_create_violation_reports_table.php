<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();

            $table->string('category'); // PAKAIAN_DINAS, INTEGRITAS
            $table->text('description')->nullable(); // wajib jika kategori Integritas
            $table->string('photo_path')->nullable(); // wajib jika kategori Pakaian Dinas
            $table->date('incident_date');

            $table->string('status')->default('PENDING'); // PENDING, VALIDATED, REJECTED
            $table->integer('deduction_point')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_reports');
    }
};
