<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_chat_messages', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('message');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            $table->string('attachment_mime', 191)->nullable()->after('attachment_name');
            $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_mime');
            $table->index('attachment_path', 'booking_chat_messages_attachment_path_idx');
        });
    }

    public function down(): void
    {
        Schema::table('booking_chat_messages', function (Blueprint $table) {
            $table->dropIndex('booking_chat_messages_attachment_path_idx');
            $table->dropColumn([
                'attachment_path',
                'attachment_name',
                'attachment_mime',
                'attachment_size',
            ]);
        });
    }
};
