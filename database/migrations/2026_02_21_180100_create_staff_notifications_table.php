<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');           // Staff user who receives the notification
            $table->unsignedBigInteger('buku_tamu_id'); // Guest record
            $table->string('type')->default('tamu_baru');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->string('response')->nullable(); // diterima / ditolak
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('buku_tamu_id')->references('id')->on('buku_tamu')->cascadeOnDelete();
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_notifications');
    }
};
