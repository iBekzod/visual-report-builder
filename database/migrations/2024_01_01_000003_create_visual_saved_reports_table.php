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
        Schema::create('visual_saved_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('visual_reports')->cascadeOnDelete();
            $table->json('data'); // Cached results
            $table->timestamp('cached_at')->nullable();
            $table->integer('cache_duration')->default(3600); // In seconds
            $table->timestamps();

            $table->index('report_id');
            $table->index('cached_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visual_saved_reports');
    }
};
