<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_chat_messages', function (Blueprint $table) {
            $table->uuid('reply_to_message_id')->nullable()->after('sender_user_id');
            $table->foreign('reply_to_message_id')
                ->references('id')
                ->on('booking_chat_messages')
                ->nullOnDelete();
            $table->index(['booking_chat_id', 'reply_to_message_id'], 'booking_chat_messages_reply_idx');
        });
    }

    public function down(): void
    {
        Schema::table('booking_chat_messages', function (Blueprint $table) {
            $table->dropIndex('booking_chat_messages_reply_idx');
            $table->dropForeign(['reply_to_message_id']);
            $table->dropColumn('reply_to_message_id');
        });
    }
};
