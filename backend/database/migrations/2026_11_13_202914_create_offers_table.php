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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);  // trade | donation
            $table->foreignId('requester_id')->index()->constrained('users');
            $table->foreignId('book_requested_id')->index()->constrained('books');
            $table->foreignId('book_offered_id')->nullable()->index()->constrained('books');  // null in donations
            $table->foreignId('offer_status_id')->index()->constrained();
            $table->text('message')->nullable();
            $table->boolean('counteroffer_made')->default(false);  // only one counteroffer (RN-09)
            $table->timestamp('owner_confirmed_at')->nullable();
            $table->timestamp('requester_confirmed_at')->nullable();
            $table->timestamp('owner_last_read_at')->nullable();
            $table->timestamp('requester_last_read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
