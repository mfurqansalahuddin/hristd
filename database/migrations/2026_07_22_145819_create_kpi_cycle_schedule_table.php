<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kpi_cycle_schedule', function (Blueprint $table) {
            $table->id();
            $table->string('phase')->unique(); // PENGINGAT_DIBUKA, PENILAIAN, REVIEW_VALIDASI, FINALISASI
            $table->unsignedTinyInteger('start_day'); // tanggal dalam bulan (1-31)
            $table->unsignedTinyInteger('end_day');
            $table->timestamps();
        });

        $defaults = [
            ['phase' => 'PENGINGAT_DIBUKA', 'start_day' => 10, 'end_day' => 10],
            ['phase' => 'PENILAIAN', 'start_day' => 11, 'end_day' => 15],
            ['phase' => 'REVIEW_VALIDASI', 'start_day' => 16, 'end_day' => 20],
            ['phase' => 'FINALISASI', 'start_day' => 21, 'end_day' => 22],
        ];

        foreach ($defaults as $row) {
            DB::table('kpi_cycle_schedule')->insert([
                'phase' => $row['phase'],
                'start_day' => $row['start_day'],
                'end_day' => $row['end_day'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_cycle_schedule');
    }
};
