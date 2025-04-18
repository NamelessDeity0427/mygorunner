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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('riders');
            $table->timestamp('check_in');
            $table->timestamp('check_out')->nullable();
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('check_in', 'idx_attendance_check_in');
            $table->index('check_out', 'idx_attendance_check_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};