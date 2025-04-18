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
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'gcash', 'other']);
            $table->string('reference_number', 100)->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->foreignId('collected_by')->nullable()->constrained('users'); // User (likely rider) who collected
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('status', 'idx_payments_status');
            $table->index('payment_method', 'idx_payments_payment_method');
            $table->index('reference_number', 'idx_payments_reference_number');
            $table->index('collected_at', 'idx_payments_collected_at');
            $table->index('created_at', 'idx_payments_created_at');

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