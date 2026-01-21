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
        if (!Schema::hasTable('report_results')) {
            Schema::create('report_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_template_id')->constrained('report_templates')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

                // Report Name & Description
                $table->string('name');
                $table->text('description')->nullable();

                // Saved State
                $table->json('applied_filters'); // Applied filter values
                $table->json('view_config'); // View type, chart options
                $table->string('view_type'); // table, line, bar, pie, etc.

                // Report Data (Cached)
                $table->json('data')->nullable(); // Cached report data
                $table->timestamp('executed_at')->nullable();
                $table->integer('execution_time_ms')->nullable(); // How long it took

                // Favorite/Pin
                $table->boolean('is_favorite')->default(false);
                $table->integer('view_count')->default(0);
                $table->timestamp('last_viewed_at')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index('report_template_id');
                $table->index('user_id');
                $table->index('is_favorite');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_results');
    }
};
