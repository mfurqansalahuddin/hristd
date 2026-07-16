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
        Schema::create('kpi_evaluator_weights', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('job_level'); // 2=Kabag-setara, 3=Kasi, 4=Staf
            $table->string('slot'); // ENUM: PENILAI_1, PENILAI_2, PENILAI_3
            $table->unsignedTinyInteger('weight'); // dari 50% Kinerja, sum per job_level = 100
            $table->timestamps();

            $table->unique(['job_level', 'slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_evaluator_weights');
    }
};
