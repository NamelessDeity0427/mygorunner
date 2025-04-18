<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_bookings_table.php

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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade'); // Keep customer link
            $table->foreignId('rider_id')->nullable()->constrained('riders'); // Keep rider link (assigned later)
            $table->foreignId('tie_up_partner_id')->nullable()->constrained('tie_up_partners'); // Keep partner link (for tie-up type)

            $table->enum('booking_type', ['tie_up', 'direct']);
            $table->enum('service_type', ['food_delivery', 'grocery', 'laundry', 'bills_payment', 'other']);

            // REMOVED: Pickup/Delivery Address and Location Columns
            // $table->text('pickup_address');
            // $table->point('pickup_location');
            // $table->text('delivery_address');
            // $table->point('delivery_location');

            $table->text('special_instructions')->nullable();
            $table->string('reference_number', 100)->nullable(); // Keep for reference if applicable
            $table->dateTime('scheduled_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_pattern', 50)->nullable();
            $table->enum('status', ['pending', 'assigned', 'picked_up', 'in_progress', 'completed', 'cancelled'])->default('pending');

            // Keep estimate/actual/fee fields - these are calculated, not input by customer
            $table->decimal('estimated_distance', 10, 2)->nullable();
            $table->integer('estimated_duration')->nullable(); // Duration in minutes/seconds? Assuming minutes
            $table->integer('actual_duration')->nullable(); // Duration in minutes/seconds? Assuming minutes
            $table->decimal('service_fee', 10, 2)->default(0.00);
            $table->decimal('rider_fee', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00); // Base cost/items cost might be calculated later

            $table->timestamps(); // created_at, updated_at
            $table->timestamp('completed_at')->nullable();

            // Indexes (Removed spatial indexes)
            $table->index('status'); // Renamed for consistency
            $table->index('booking_type');
            $table->index('service_type');
            $table->index('scheduled_at');
            $table->index('created_at');
            $table->index('completed_at');
            $table->index('reference_number');
            // $table->spatialIndex('pickup_location'); // Removed
            // $table->spatialIndex('delivery_location'); // Removed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};