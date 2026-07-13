<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // CUTI, SAKIT, DINAS_LUAR
            $table->date('start_date');
            $table->date('end_date');
            $table->string('attachment_path')->nullable();
            $table->string('hr_final_status')->default('PENDING'); // PENDING, APPROVED, REJECTED

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
