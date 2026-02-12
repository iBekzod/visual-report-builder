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
        if (!Schema::hasTable('report_templates')) {
            Schema::create('report_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('model'); // Eloquent model class

                // Template Configuration
                $table->json('dimensions'); // Available dimensions
                $table->json('metrics'); // Available metrics
                $table->json('filters')->nullable()->default(null); // Filter definitions (optional)
                $table->json('default_view'); // Default visualization type (table, line, bar, pie)
                $table->json('chart_config')->nullable()->default(null); // Chart.js / ApexCharts config

                // Display Settings
                $table->string('icon')->nullable();
                $table->string('category')->nullable();
                $table->integer('sort_order')->default(0);

                // Visibility & Access
                $table->boolean('is_active')->default(true);
                $table->boolean('is_public')->default(false);

                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index('category');
                $table->index('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
