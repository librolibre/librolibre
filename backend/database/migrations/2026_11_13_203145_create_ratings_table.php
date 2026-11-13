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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_rater_id')->index()->constrained('users');
            $table->foreignId('user_rated_id')->index()->constrained('users');
            $table->unsignedSmallInteger('score');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['offer_id', 'user_rater_id']);
        });

        DB::statement('ALTER TABLE ratings ADD CONSTRAINT ratings_score_check CHECK (score BETWEEN 1 AND 5)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
