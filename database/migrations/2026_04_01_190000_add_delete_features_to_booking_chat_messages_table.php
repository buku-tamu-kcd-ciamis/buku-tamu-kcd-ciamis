<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_chat_messages', function (Blueprint $table) {
            $table->timestamp('deleted_for_everyone_at')->nullable()->after('attachment_size');
            $table->uuid('deleted_for_everyone_by')->nullable()->after('deleted_for_everyone_at');

            $table->foreign('deleted_for_everyone_by', 'booking_chat_messages_deleted_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index('deleted_for_everyone_at', 'booking_chat_messages_deleted_everyone_at_idx');
        });

        Schema::create('booking_chat_message_deletions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('booking_chat_message_id');
            $table->uuid('user_id');
            $table->timestamps();

            $table->foreign('booking_chat_message_id', 'booking_chat_msg_deletions_message_fk')
                ->references('id')
                ->on('booking_chat_messages')
                ->cascadeOnDelete();
            $table->foreign('user_id', 'booking_chat_msg_deletions_user_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique(['booking_chat_message_id', 'user_id'], 'booking_chat_msg_deletions_unique');
            $table->index(['user_id', 'created_at'], 'booking_chat_msg_deletions_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_chat_message_deletions');

        Schema::table('booking_chat_messages', function (Blueprint $table) {
            $table->dropIndex('booking_chat_messages_deleted_everyone_at_idx');
            $table->dropForeign('booking_chat_messages_deleted_by_fk');
            $table->dropColumn(['deleted_for_everyone_at', 'deleted_for_everyone_by']);
        });
    }
};
