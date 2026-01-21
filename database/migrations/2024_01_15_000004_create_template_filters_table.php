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
        if (!Schema::hasTable('template_filters')) {
            Schema::create('template_filters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_template_id')->constrained('report_templates')->cascadeOnDelete();

                // Filter Definition
                $table->string('column');
                $table->string('label');
                $table->string('type'); // text, select, date, daterange, number, etc.
                $table->json('options')->nullable(); // For select/multi-select: [{"value":"","label":""}]
                $table->string('operator')->default('='); // =, !=, >, <, >=, <=, in, like, between
                $table->boolean('is_required')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);

                // Default Value
                $table->string('default_value')->nullable();

                $table->timestamps();

                $table->index('report_template_id');
                $table->index('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_filters');
    }
};
