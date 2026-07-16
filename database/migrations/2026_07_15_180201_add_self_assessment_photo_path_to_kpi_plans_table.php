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
        Schema::table('kpi_plans', function (Blueprint $table) {
            $table->string('self_assessment_photo_path')->nullable()->after('self_assessment_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_plans', function (Blueprint $table) {
            $table->dropColumn('self_assessment_photo_path');
        });
    }
};
