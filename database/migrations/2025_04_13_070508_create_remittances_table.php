<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remittances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rider_id')->constrained('riders')->onDelete('cascade');
            $table->foreignUuid('staff_id')->nullable()->constrained('staff')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'gcash', 'other']);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'verified', 'discrepancy'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('payment_method');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remittances');
    }
};