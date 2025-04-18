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
        Schema::create('rider_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('riders');
            $table->timestamp('check_in_time');
            $table->enum('status', ['waiting', 'assigned', 'completed'])->default('waiting');
            $table->timestamps();

            // Indexes
            $table->index('status', 'idx_rider_queue_status');
            $table->index('check_in_time', 'idx_rider_queue_check_in_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rider_queue');
    }
};