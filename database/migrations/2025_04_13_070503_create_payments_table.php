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
        Schema::create('payments', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for foreign keys
            $table->foreignUuid('booking_id')->constrained('bookings')->onDelete('cascade'); // Link to booking [cite: 130]
            $table->decimal('amount', 10, 2); // [cite: 130]
            $table->enum('payment_method', ['cash', 'gcash', 'card', 'other']); // Added 'card' [cite: 130]
            $table->string('reference_number', 100)->nullable(); // For GCash/Card/etc. [cite: 130]
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending'); // [cite: 130]
            // User (Rider or potentially Staff/Admin) who handled the payment record
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->onDelete('set null'); // Renamed from collected_by for clarity [cite: 130]
            $table->timestamp('paid_at')->nullable(); // Renamed from collected_at [cite: 130]
            $table->timestamps(); // created_at, updated_at [cite: 130]

            // Indexes [cite: 130]
            $table->index('status');
            $table->index('payment_method');
            $table->index('reference_number');
            $table->index('paid_at'); // Renamed index
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};