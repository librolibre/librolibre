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
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_user_id')->index()->constrained('users');
            $table->foreignId('reported_user_id')->index()->constrained('users');
            $table->foreignId('offer_id')->nullable()->index()->constrained('offers');
            $table->foreignId('reason_id')->index()->constrained('report_reasons');
            $table->text('description')->nullable(); 
            $table->string('status', 20)->default('open')->index();  // open | valid | dismissed
            $table->boolean('strike_added')->default(false);
            $table->foreignId('resolved_by')->nullable()->index()->constrained('users');
            $table->text('resolution_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
