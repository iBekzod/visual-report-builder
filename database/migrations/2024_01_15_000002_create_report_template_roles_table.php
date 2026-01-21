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
        if (!Schema::hasTable('report_template_roles')) {
            Schema::create('report_template_roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_template_id')->constrained('report_templates')->cascadeOnDelete();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();

                // Permissions
                $table->boolean('can_view')->default(true);
                $table->boolean('can_export')->default(true);
                $table->boolean('can_save')->default(false);
                $table->boolean('can_edit_filters')->default(true);

                $table->timestamps();

                $table->unique(['report_template_id', 'role_id']);
                $table->index('role_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_template_roles');
    }
};
