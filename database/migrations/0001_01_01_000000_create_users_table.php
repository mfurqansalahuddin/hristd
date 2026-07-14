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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('job_level'); // 1=Direksi, 2=Kabag/Kacab/Kanit/StafAhli, 3=Kasi, 4=Staf
            $table->foreignId('direct_supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('final_supervisor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('instansi'); // PERUMDAM_TD, KOPKARTIRDA
            $table->string('employment_status'); // MAGANG, KONTRAK, TETAP
            $table->integer('leave_balance')->default(12);

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
