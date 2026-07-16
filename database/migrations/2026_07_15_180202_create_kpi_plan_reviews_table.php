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
        Schema::create('kpi_plan_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // pemilik rencana kinerja yang direview
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete(); // atasan pertama, §5.1 Penilai 1
            $table->foreignId('kpi_plan_id')->nullable()->constrained('kpi_plans')->cascadeOnDelete(); // null = komentar utk seluruh putaran, terisi = komentar per-item (§5.3 mobile-app.md)
            $table->string('action'); // APPROVED, REVISION_REQUESTED
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_plan_reviews');
    }
};
