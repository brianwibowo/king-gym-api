<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('attendances');
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();

            // Location In
            $table->string('lat_in')->nullable();
            $table->string('long_in')->nullable();

            // Location Out
            $table->string('lat_out')->nullable();
            $table->string('long_out')->nullable();

            // Photos
            $table->string('photo_in')->nullable();
            $table->string('photo_out')->nullable();

            $table->string('work_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
