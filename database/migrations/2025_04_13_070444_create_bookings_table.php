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
        Schema::create('bookings', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            $table->string('booking_number', 50)->unique(); // [cite: 117]
            // Use foreignUuid for foreign keys
            $table->foreignUuid('customer_id')->constrained('customers')->onDelete('cascade'); // [cite: 117]
            $table->foreignUuid('rider_id')->nullable()->constrained('riders')->onDelete('set null'); // Set null on rider delete [cite: 117]
            // REMOVED: $table->foreignId('tie_up_partner_id')->nullable()->constrained('tie_up_partners'); // [cite: 117] Removed tie-up partner link
            // REMOVED: $table->enum('booking_type', ['tie_up', 'direct']); // [cite: 117] Removed booking type, assuming 'direct' only now
            $table->enum('service_type', ['food_delivery', 'grocery', 'laundry', 'bills_payment', 'other']); // [cite: 117]
            // Added back address/location fields from modify migration [cite: 157, 158] - Make them nullable
            $table->text('pickup_address')->nullable();
            $table->point('pickup_location')->nullable(); // Spatial column
            $table->text('delivery_address')->nullable();
            $table->point('delivery_location')->nullable(); // Spatial column
            $table->text('special_instructions')->nullable(); // [cite: 117]
            $table->string('reference_number', 100)->nullable(); // [cite: 117]
            $table->dateTime('scheduled_at')->nullable(); // [cite: 117]
            // Removed recurring fields as they seem complex and maybe out of scope for now [cite: 118]
            // $table->boolean('is_recurring')->default(false);
            // $table->string('recurring_pattern', 50)->nullable();
            $table->enum('status', [ // [cite: 118]
                'pending', // Customer created, awaiting admin/dispatcher action
                'accepted', // Admin/Dispatcher accepted, finding rider
                'assigned', // Rider assigned
                'at_pickup', // Rider arrived at pickup (if applicable)
                'picked_up', // Goods collected
                'on_the_way', // En route to delivery
                'at_delivery', // Rider arrived at delivery
                'completed', // Delivery successful
                'cancelled', // Cancelled by customer or admin
                'failed' // Delivery attempted but failed
            ])->default('pending');
            $table->decimal('estimated_distance', 10, 2)->nullable(); // [cite: 118]
            $table->integer('estimated_duration')->nullable(); // In seconds [cite: 118]
            $table->integer('actual_duration')->nullable(); // In seconds [cite: 120]
            $table->decimal('service_fee', 10, 2)->default(0.00); // Base service fee [cite: 120]
            $table->decimal('rider_fee', 10, 2)->default(0.00); // Rider's earning from this booking [cite: 120]
            $table->decimal('items_cost', 10, 2)->default(0.00); // Cost of items purchased (for grocery, etc.)
            $table->decimal('total_amount', 10, 2)->default(0.00); // Total charged to customer (service_fee + items_cost + potentially other charges) [cite: 120]
            $table->timestamps(); // created_at, updated_at [cite: 121]
            $table->timestamp('completed_at')->nullable(); // [cite: 121]
            $table->timestamp('cancelled_at')->nullable(); // Add cancellation timestamp

            // Indexes [cite: 121, 122]
            $table->index('status');
            $table->index('service_type');
            $table->index('scheduled_at');
            $table->index('created_at');
            $table->index('completed_at');
            $table->index('cancelled_at');
            $table->index('reference_number');

            // Add spatial indexes if needed and supported [cite: 158]
            // $table->spatialIndex('pickup_location');
            // $table->spatialIndex('delivery_location');
        });

        // Add spatial indexes separately if needed
        // DB::statement('ALTER TABLE bookings ADD SPATIAL INDEX(pickup_location);');
        // DB::statement('ALTER TABLE bookings ADD SPATIAL INDEX(delivery_location);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};