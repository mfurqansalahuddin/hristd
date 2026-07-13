<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_current_locations', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->decimal('lat', 10, 8);
            $table->decimal('long', 11, 8);
            $table->dateTime('last_updated_at'); // > 3 menit dianggap offline
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_current_locations');
    }
};
