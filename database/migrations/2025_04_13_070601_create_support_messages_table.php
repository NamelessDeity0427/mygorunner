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
        Schema::create('support_messages', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for foreign keys
            $table->foreignUuid('ticket_id')->constrained('support_tickets')->onDelete('cascade'); // [cite: 152]
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // User who wrote (Customer or Staff) [cite: 152]
            $table->text('message'); // [cite: 152]
            $table->timestamp('created_at')->useCurrent(); // [cite: 152]

            // Index [cite: 152]
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};