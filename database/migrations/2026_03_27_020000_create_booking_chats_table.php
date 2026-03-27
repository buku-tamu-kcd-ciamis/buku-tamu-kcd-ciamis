<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_chats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('buku_tamu_id');
            $table->uuid('staff_user_id');
            $table->uuid('piket_user_id')->nullable();
            $table->uuid('created_by_user_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('buku_tamu_id')->references('id')->on('buku_tamu')->cascadeOnDelete();
            $table->foreign('staff_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('piket_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->unique(['buku_tamu_id', 'staff_user_id'], 'booking_chats_unique_booking_staff');
            $table->index(['staff_user_id', 'last_message_at'], 'booking_chats_staff_last_message_idx');
            $table->index(['piket_user_id', 'last_message_at'], 'booking_chats_piket_last_message_idx');
        });

        Schema::create('booking_chat_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('booking_chat_id');
            $table->uuid('sender_user_id')->nullable();
            $table->text('message');
            $table->boolean('is_system')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('booking_chat_id')->references('id')->on('booking_chats')->cascadeOnDelete();
            $table->foreign('sender_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['booking_chat_id', 'created_at'], 'booking_chat_messages_chat_created_idx');
            $table->index(['sender_user_id', 'read_at'], 'booking_chat_messages_sender_read_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_chat_messages');
        Schema::dropIfExists('booking_chats');
    }
};
