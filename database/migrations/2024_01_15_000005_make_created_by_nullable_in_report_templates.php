<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (Schema::hasColumn('report_templates', 'created_by')) {
            // Drop foreign key constraint first
            Schema::table('report_templates', function (Blueprint $table) {
                try {
                    $table->dropForeign(['created_by']);
                } catch (\Exception $e) {
                    // Foreign key might not exist with expected name, try alternative
                    try {
                        $table->dropForeign('report_templates_created_by_foreign');
                    } catch (\Exception $e2) {
                        // Continue
                    }
                }
            });

            // Modify column using raw SQL based on driver
            if ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement('ALTER TABLE report_templates MODIFY COLUMN created_by BIGINT UNSIGNED NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE report_templates ALTER COLUMN created_by DROP NOT NULL');
            } elseif ($driver === 'sqlite') {
                // SQLite doesn't support ALTER COLUMN, would need table recreation
                // For simplicity, we'll skip this for SQLite as it's rarely used in production
            }

            // Re-add foreign key constraint with ON DELETE SET NULL
            Schema::table('report_templates', function (Blueprint $table) {
                $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Note: Rolling back will fail if there are NULL values in created_by
        Schema::table('report_templates', function (Blueprint $table) {
            try {
                $table->dropForeign(['created_by']);
            } catch (\Exception $e) {
                try {
                    $table->dropForeign('report_templates_created_by_foreign');
                } catch (\Exception $e2) {
                    // Continue
                }
            }
        });

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('ALTER TABLE report_templates MODIFY COLUMN created_by BIGINT UNSIGNED NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE report_templates ALTER COLUMN created_by SET NOT NULL');
        }

        Schema::table('report_templates', function (Blueprint $table) {
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
        });
    }
};
