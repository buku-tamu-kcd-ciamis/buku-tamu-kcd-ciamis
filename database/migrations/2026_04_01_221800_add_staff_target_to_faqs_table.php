<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE `faqs` MODIFY `target` ENUM('semua','admin','piket','staff') NOT NULL DEFAULT 'semua'");
    }

    public function down(): void
    {
        DB::statement("UPDATE `faqs` SET `target` = 'semua' WHERE `target` = 'staff'");
        DB::statement("ALTER TABLE `faqs` MODIFY `target` ENUM('semua','admin','piket') NOT NULL DEFAULT 'semua'");
    }
};
