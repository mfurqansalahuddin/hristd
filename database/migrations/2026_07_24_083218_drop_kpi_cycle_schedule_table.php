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
        Schema::dropIfExists('kpi_cycle_schedule');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('kpi_cycle_schedule', function (Blueprint $table) {
            $table->id();
            $table->string('phase')->unique();
            $table->unsignedTinyInteger('start_day');
            $table->unsignedTinyInteger('end_day');
            $table->timestamps();
        });
    }
};
