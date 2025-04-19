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
        Schema::create('remittance_details', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for foreign keys
            $table->foreignUuid('remittance_id')->constrained('remittances')->onDelete('cascade'); // Link to parent remittance [cite: 136]
            $table->foreignUuid('booking_id')->constrained('bookings')->onDelete('cascade'); // Link to the specific booking being remitted [cite: 136] - Cascade ok? Maybe set null if booking deleted?
            $table->decimal('amount', 10, 2); // The portion of the booking payment included in this remittance [cite: 136]
            $table->timestamps(); // No need for timestamps? Maybe just created_at [cite: 136] - Kept for now
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remittance_details');
    }
};