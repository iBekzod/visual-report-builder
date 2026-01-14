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
        Schema::create('visual_report_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('visual_reports')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_share')->default(false);
            $table->timestamps();

            $table->unique(['report_id', 'user_id']);
            $table->index('user_id');
            $table->index('can_edit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visual_report_shares');
    }
};
