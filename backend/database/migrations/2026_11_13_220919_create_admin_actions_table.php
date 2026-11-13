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
        Schema::create('admin_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->index()->constrained('users');  // null = automatic (3 strikes)
            $table->string('action', 50);  // suspend_user, reactivate_user, hide_book, restore_book...
            $table->string('target_type', 50);  // user | book | report
            $table->unsignedBigInteger('target_id');
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index(['target_type', 'target_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_actions');
    }
};
