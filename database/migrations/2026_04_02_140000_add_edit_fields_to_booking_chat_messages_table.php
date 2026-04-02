<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_chat_messages', function (Blueprint $table) {
            $table->timestamp('edited_at')->nullable()->after('attachment_size');
            $table->uuid('edited_by')->nullable()->after('edited_at');

            $table->foreign('edited_by', 'booking_chat_messages_edited_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('edited_at', 'booking_chat_messages_edited_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('booking_chat_messages', function (Blueprint $table) {
            $table->dropIndex('booking_chat_messages_edited_at_idx');
            $table->dropForeign('booking_chat_messages_edited_by_fk');

            $table->dropColumn([
                'edited_by',
                'edited_at',
            ]);
        });
    }
};
