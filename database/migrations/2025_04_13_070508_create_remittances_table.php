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
            $table->id();
            $table->foreignId('rider_id')->constrained('riders');
            $table->foreignId('staff_id')->constrained('staff'); // Staff who processed
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'gcash', 'other']);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'verified', 'discrepancy'])->default('pending');
            $table->timestamps();

            // Indexes
            $table->index('status', 'idx_remittances_status');
            $table->index('payment_method', 'idx_remittances_payment_method');
            $table->index('reference_number', 'idx_remittances_reference_number');
            $table->index('created_at', 'idx_remittances_created_at');
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