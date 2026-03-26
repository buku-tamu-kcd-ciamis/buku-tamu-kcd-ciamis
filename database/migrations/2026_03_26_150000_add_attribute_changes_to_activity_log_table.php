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
        $connection = config('activitylog.database_connection', config('database.default'));
        $tableName = config('activitylog.table_name', 'activity_log');

        if (!Schema::connection($connection)->hasColumn($tableName, 'attribute_changes')) {
            Schema::connection($connection)->table($tableName, function (Blueprint $table) {
                $table->json('attribute_changes')->nullable()->after('log_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('activitylog.database_connection', config('database.default'));
        $tableName = config('activitylog.table_name', 'activity_log');

        if (Schema::connection($connection)->hasColumn($tableName, 'attribute_changes')) {
            Schema::connection($connection)->table($tableName, function (Blueprint $table) {
                $table->dropColumn('attribute_changes');
            });
        }
    }
};
