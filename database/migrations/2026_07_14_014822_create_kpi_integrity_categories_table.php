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
        Schema::create('kpi_integrity_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('deduction_value'); // poin dikurangi dari skor Integritas per aduan tervalidasi
            $table->timestamps();
        });

        $defaults = [
            'Etika' => 10,
            'Kerjasama Tim' => 10,
            'Jujur dan Transparansi' => 20,
            'Loyalitas' => 15,
            'Kepatuhan dan Budaya Organisasi' => 10,
            'Ketepatan Waktu' => 15,
            'Inisiatif' => 10,
            'Tugas Tambahan' => 10,
        ];

        foreach ($defaults as $name => $value) {
            DB::table('kpi_integrity_categories')->insert([
                'name' => $name,
                'deduction_value' => $value,
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
        Schema::dropIfExists('kpi_integrity_categories');
    }
};
