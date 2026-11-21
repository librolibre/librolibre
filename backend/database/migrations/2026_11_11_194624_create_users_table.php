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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Google
            $table->string('google_id')->unique();
            $table->string('email')->unique();

            // User Profile
            $table->string('username', 60)->unique();
            $table->string('photo_path')->nullable();
            $table->text('description')->nullable();

            // Location
            $table->string('postal_code', 5)->nullable()
                ->comment('Show "Tu zona: [postal_code]"');
            $table->decimal('latitude', 8, 2)->nullable();
            $table->decimal('longitude', 8, 2)->nullable();

            // Roles
            $table->foreignId('profile_id')->constrained();
            $table->string('status', 20)->default('active');
            $table->unsignedTinyInteger('strikes')->default(0);

            // GDPR / Protección de datos
            $table->timestamp('privacy_accepted_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
