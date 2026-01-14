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
        Schema::create('visual_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('model'); // Eloquent model class
            $table->json('configuration'); // Report config
            $table->json('view_options')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('template_id')->nullable()->constrained('visual_report_templates')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('template_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visual_reports');
    }
};
