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
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('parent_department_id')->nullable()->after('type')->constrained('departments')->nullOnDelete();
            $table->string('directorate')->nullable()->after('parent_department_id'); // KEUANGAN, TEKNIK — hanya diisi di node job_level 2, SEKSI mewarisi dari parent
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_department_id');
            $table->dropColumn('directorate');
        });
    }
};
