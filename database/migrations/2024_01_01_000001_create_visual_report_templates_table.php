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
        Schema::create('visual_report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('model');
            $table->json('default_config');
            $table->json('allowed_metrics')->nullable();
            $table->json('allowed_dimensions')->nullable();
            $table->string('category')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index('model');
            $table->index('category');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visual_report_templates');
    }
};
