<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();

            $table->boolean('is_apel')->default(false); // auto TRUE jika clock_in <= 08:00
            $table->string('status'); // HADIR, TELAT, PULANG_CEPAT, ALPA, CUTI, DINAS_LUAR, SAKIT
            $table->text('late_reason')->nullable();
            $table->string('supervisor_approval')->default('PENDING'); // PENDING, APPROVED, REJECTED

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
