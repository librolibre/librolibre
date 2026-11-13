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
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            // Owner (no cascade: a user who still has books cannot be deleted)
            $table->foreignId('user_id')->index()->constrained();

            // Main data (title and author obligatorios)
            $table->string('title');
            $table->string('author');
            $table->string('isbn', 13)->nullable()->index();
            $table->foreignId('language_id')->index()->constrained();

            // Edition details (Open Library fills them when it can, so all optional)
            $table->string('publisher')->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('publication_place')->nullable();
            $table->integer('page_count')->nullable();
            $table->string('cover_url')->nullable();           // portada de Open Library; NULL si no hay cover disponible
            $table->string('edition_name')->nullable();

            // Physical description (o como es el libro)
            $table->foreignId('binding_type_id')->index()->constrained();
            $table->foreignId('condition_id')->index()->constrained('book_conditions');
            $table->string('dimensions')->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->text('notes')->nullable();

            // Trade/intercambio/trueque
            $table->boolean('is_donation')->default(false);    // false = solo trueque, true = trueque y donación
            $table->string('status', 20)->default('available')->index();
            // available | locked | reserved | delivered | paused | hidden | withdrawn

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
