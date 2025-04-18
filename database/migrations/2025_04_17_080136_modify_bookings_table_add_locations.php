<?php
// [Generated Migration File Name]
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('pickup_address')->nullable()->after('service_type');
            $table->point('pickup_location')->nullable()->after('pickup_address'); // Added spatial column
            $table->text('delivery_address')->nullable()->after('pickup_location');
            $table->point('delivery_location')->nullable()->after('delivery_address'); // Added spatial column

            // Add spatial indexes
            // $table->spatialIndex('pickup_location');
            // $table->spatialIndex('delivery_location');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop indexes first (use specific index names if known, otherwise use column names)
            // $table->dropSpatialIndex(['delivery_location']); // Or $table->dropSpatialIndex('bookings_delivery_location_spatialindex');
            // $table->dropSpatialIndex(['pickup_location']);   // Or $table->dropSpatialIndex('bookings_pickup_location_spatialindex');

            $table->dropColumn(['delivery_location', 'delivery_address', 'pickup_location', 'pickup_address']);
        });
    }
};