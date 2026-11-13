<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_blocker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_blocked_id')->index()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_blocker_id', 'user_blocked_id']);
        });

        DB::statement('ALTER TABLE blocks ADD CONSTRAINT blocks_not_self_check CHECK (user_blocker_id <> user_blocked_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
