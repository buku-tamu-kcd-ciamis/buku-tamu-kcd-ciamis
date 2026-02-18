<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix duplicate entries in dropdown_options table by removing all and letting seeder repopulate.
     */
    public function up(): void
    {
        // Delete all jenis_id records to fix the duplicates
        // The seeder will repopulate them correctly using updateOrCreate
        DB::table('dropdown_options')->where('category', 'jenis_id')->delete();

        echo "Deleted all jenis_id records. Run 'php artisan db:seed --class=DropdownOptionSeeder' to repopulate.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed - seeder handles the data
    }
};
