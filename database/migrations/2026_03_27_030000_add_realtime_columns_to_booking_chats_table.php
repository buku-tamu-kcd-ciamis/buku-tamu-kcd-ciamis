<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_chats', function (Blueprint $table) {
            $table->timestamp('staff_last_seen_at')->nullable()->after('last_message_at');
            $table->timestamp('piket_last_seen_at')->nullable()->after('staff_last_seen_at');
            $table->timestamp('staff_typing_at')->nullable()->after('piket_last_seen_at');
            $table->timestamp('piket_typing_at')->nullable()->after('staff_typing_at');
        });
    }

    public function down(): void
    {
        Schema::table('booking_chats', function (Blueprint $table) {
            $table->dropColumn([
                'staff_last_seen_at',
                'piket_last_seen_at',
                'staff_typing_at',
                'piket_typing_at',
            ]);
        });
    }
};
