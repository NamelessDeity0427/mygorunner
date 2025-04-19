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
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for foreign keys
            $table->foreignUuid('booking_id')->constrained('bookings')->onDelete('cascade'); // [cite: 147]
            $table->foreignUuid('customer_id')->constrained('customers')->onDelete('cascade'); // [cite: 147]
            $table->foreignUuid('rider_id')->constrained('riders')->onDelete('cascade'); // [cite: 147] - Cascade ok? Maybe set null?
            $table->unsignedTinyInteger('rating'); // 1-5 rating [cite: 147]
            $table->text('comments')->nullable(); // [cite: 147]
            $table->timestamps(); // [cite: 147]

            // Indexes [cite: 148]
            $table->index('rating');
            $table->index('created_at');
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