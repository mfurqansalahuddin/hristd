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
        Schema::table('users', function (Blueprint $table) {
            // Ganti is_admin (boolean tunggal) jadi role granular: SUPER_ADMIN (IT & Kepala
            // Bagian Umum, nanti satu-satunya lihat nominal gaji), ADMIN_KEPEGAWAIAN (HRD),
            // STAFF (default, semua pegawai biasa termasuk Direksi/Kabag/Kasi, akses cuma mobile).
            $table->string('role')->default('STAFF')->after('is_admin');
        });

        // is_admin=true dibackfill sementara jadi ADMIN_KEPEGAWAIAN — HRD bisa promosikan
        // manual siapa saja jadi SUPER_ADMIN lewat form pegawai setelahnya.
        DB::table('users')->where('is_admin', true)->update(['role' => 'ADMIN_KEPEGAWAIAN']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('leave_balance');
        });

        DB::table('users')->whereIn('role', ['ADMIN_KEPEGAWAIAN', 'SUPER_ADMIN'])->update(['is_admin' => true]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
