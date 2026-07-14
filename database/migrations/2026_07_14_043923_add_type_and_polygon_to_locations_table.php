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
        Schema::table('locations', function (Blueprint $table) {
            $table->string('type')->default('RADIUS')->after('name'); // RADIUS, POLYGON
            $table->json('polygon')->nullable()->after('radius_meters'); // array of {lat,lng}, dipakai kalau type=POLYGON
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->integer('radius_meters')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->integer('radius_meters')->nullable(false)->change();
            $table->dropColumn(['type', 'polygon']);
        });
    }
};
