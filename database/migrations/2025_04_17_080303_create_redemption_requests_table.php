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
        Schema::create('redemption_requests', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for foreign keys
            $table->foreignUuid('rider_id')->constrained('riders')->onDelete('cascade'); // [cite: 171]
            $table->decimal('requested_amount', 10, 2); // [cite: 171]
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending'); // [cite: 171]
            $table->foreignUuid('processed_by')->nullable()->constrained('staff')->onDelete('set null'); // Staff who processed [cite: 171]
            $table->timestamp('processed_at')->nullable(); // [cite: 171]
            $table->text('notes')->nullable(); // Admin/Staff notes (e.g., rejection reason) [cite: 171]
            $table->timestamps(); // requested_at is created_at [cite: 171]

            $table->index('status'); // [cite: 171]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redemption_requests');
    }
};