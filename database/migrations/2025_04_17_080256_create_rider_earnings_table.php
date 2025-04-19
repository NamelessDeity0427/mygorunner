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
        Schema::create('rider_earnings', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for foreign keys
            $table->foreignUuid('rider_id')->constrained('riders')->onDelete('cascade'); // [cite: 168]
            $table->foreignUuid('booking_id')->constrained('bookings')->onDelete('cascade'); // Earning linked to a specific booking [cite: 168]
            $table->decimal('amount', 10, 2); // Amount earned (e.g., rider_fee from booking) [cite: 168]
            $table->string('type'); // e.g., 'delivery_fee', 'tip', 'adjustment', 'bonus' [cite: 168]
            $table->enum('status', ['pending', 'cleared', 'paid_out', 'cancelled'])->default('pending'); // Added 'cancelled' if booking is cancelled [cite: 168]
            $table->timestamp('cleared_at')->nullable(); // When earning is confirmed/available for payout [cite: 168]
            $table->timestamps(); // [cite: 168]

            $table->index('status'); // [cite: 168]
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rider_earnings');
    }
};