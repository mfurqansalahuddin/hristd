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
        Schema::create('kpi_mandatory_event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_mandatory_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->nullable(); // HADIR, TELAT, TIDAK_HADIR — null = belum ditandai (tidak dapat kredit)
            $table->text('reason')->nullable(); // wajib kalau TIDAK_HADIR di UI, opsional/anotasi kalau TELAT
            $table->string('approval_status')->nullable(); // PENDING, APPROVED, REJECTED
            $table->timestamps();

            $table->unique(['kpi_mandatory_event_id', 'user_id'], 'kpi_mandatory_event_participants_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_mandatory_event_participants');
    }
};
