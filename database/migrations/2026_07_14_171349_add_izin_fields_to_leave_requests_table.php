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
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->renameColumn('hr_final_status', 'status');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('end_date');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->foreignId('approved_by_id')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            $table->string('source')->default('HR_MANUAL')->after('approved_by_id'); // APP, HR_MANUAL
            $table->string('dl_batch_uuid')->nullable()->after('source')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by_id']);
            $table->dropColumn(['reason', 'rejection_reason', 'approved_by_id', 'source', 'dl_batch_uuid']);
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->renameColumn('status', 'hr_final_status');
        });
    }
};
