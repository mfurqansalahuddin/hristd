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
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('direct_supervisor_id');
            $table->dropConstrainedForeignId('final_supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('direct_supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('final_supervisor_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
