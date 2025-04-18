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
        Schema::create('customer_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('rider_id')->constrained('riders');
            $table->integer('rating'); // Assuming integer rating (e.g., 1-5)
            $table->text('comments')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('rating', 'idx_customer_feedback_rating');
            $table->index('created_at', 'idx_customer_feedback_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_feedback');
    }
};