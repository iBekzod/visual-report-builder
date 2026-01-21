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
        if (!Schema::hasTable('visual_data_sources')) {
            Schema::create('visual_data_sources', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('type'); // eloquent, database, api, csv
                $table->string('model_class')->nullable();
                $table->json('configuration')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->boolean('is_public')->default(false);
                $table->timestamps();

                $table->index('user_id');
                $table->index('type');
                $table->index('is_public');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visual_data_sources');
    }
};
