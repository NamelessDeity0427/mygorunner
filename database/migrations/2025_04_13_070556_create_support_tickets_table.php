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
        Schema::create('support_tickets', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            $table->string('ticket_number', 50)->unique(); // [cite: 150]
            // Use foreignUuid for foreign keys
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // User who created [cite: 150]
            $table->foreignUuid('booking_id')->nullable()->constrained('bookings')->onDelete('set null'); // Optional link to booking [cite: 150]
            $table->string('subject'); // [cite: 150]
            $table->text('description'); // [cite: 150]
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open'); // [cite: 150]
            $table->foreignUuid('assigned_to')->nullable()->constrained('staff')->onDelete('set null'); // Staff assigned [cite: 150]
            $table->timestamps(); // created_at, updated_at [cite: 150]
            $table->timestamp('resolved_at')->nullable(); // [cite: 150]

            // Indexes [cite: 150]
            $table->index('status');
            $table->index('created_at');
            $table->index('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};