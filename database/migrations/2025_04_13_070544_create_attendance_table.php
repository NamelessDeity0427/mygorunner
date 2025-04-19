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
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for foreign key
            $table->foreignUuid('rider_id')->constrained('riders')->onDelete('cascade'); // [cite: 145]
            $table->timestamp('check_in'); // Scan time [cite: 145]
            $table->timestamp('check_out')->nullable(); // Scan time [cite: 145]
            $table->decimal('total_hours', 5, 2)->nullable(); // Calculated on check-out [cite: 145]
            // Add location tracking for check-in/out?
            $table->point('check_in_location')->nullable();
            $table->point('check_out_location')->nullable();
            $table->timestamps(); // Record creation/update [cite: 145]

            // Indexes [cite: 145]
            $table->index('check_in');
            $table->index('check_out');
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