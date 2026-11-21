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
        Schema::create('book_defect', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('defect_id')->index()->constrained()->cascadeOnDelete();
            $table->primary(['book_id', 'defect_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_defect');
    }
};
