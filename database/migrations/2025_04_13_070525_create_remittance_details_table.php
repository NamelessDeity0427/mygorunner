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
            $table->id();
            $table->foreignId('remittance_id')->constrained('remittances')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('bookings'); // Link remittance to specific booking payment remitted
            $table->decimal('amount', 10, 2); // The portion of the booking amount included in this remittance
            $table->timestamps();
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