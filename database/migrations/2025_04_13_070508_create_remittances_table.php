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
        Schema::create('remittances', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for foreign keys
            $table->foreignUuid('rider_id')->constrained('riders')->onDelete('cascade'); // Rider remitting [cite: 133]
            $table->foreignUuid('staff_id')->constrained('staff')->onDelete('cascade'); // Staff verifying [cite: 133] - Changed to cascade, consider set null?
            $table->decimal('amount', 10, 2); // Total amount remitted [cite: 133]
            $table->enum('payment_method', ['cash', 'gcash', 'other']); // How rider paid staff [cite: 133]
            $table->string('reference_number', 100)->nullable(); // [cite: 133]
            $table->text('notes')->nullable(); // Staff notes [cite: 133]
            $table->enum('status', ['pending', 'verified', 'discrepancy'])->default('pending'); // [cite: 133]
            $table->timestamps(); // remittance_date is created_at, verified_at is updated_at? [cite: 133]

            // Indexes [cite: 133]
            $table->index('status');
            $table->index('payment_method');
            $table->index('reference_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remittances');
    }
};