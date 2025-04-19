<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('booking_number', 50)->unique();
            $table->foreignUuid('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignUuid('rider_id')->nullable()->constrained('riders')->onDelete('set null');
            $table->enum('service_type', ['food_delivery', 'grocery', 'laundry', 'bills_payment', 'other']);
            $table->text('pickup_address')->nullable();
            $table->point('pickup_location');
            $table->text('delivery_address')->nullable();
            $table->point('delivery_location');
            $table->text('special_instructions')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->enum('status', [
                'pending', 'accepted', 'assigned', 'at_pickup', 'picked_up',
                'on_the_way', 'at_delivery', 'completed', 'cancelled', 'failed'
            ])->default('pending');
            $table->decimal('estimated_distance', 10, 2)->nullable();
            $table->decimal('estimated_duration', 10, 2)->nullable();
            $table->decimal('actual_duration', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->spatialIndex('pickup_location');
            $table->spatialIndex('delivery_location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};