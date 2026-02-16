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
    Schema::create('profile_change_requests', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->uuid('user_id')->comment('User yang mengajukan');
      $table->json('old_data')->comment('Data lama sebelum perubahan');
      $table->json('new_data')->comment('Data baru yang diajukan');
      $table->string('status')->default('pending')->comment('pending, approved, rejected');
      $table->text('catatan')->nullable()->comment('Catatan dari pengaju');
      $table->text('alasan_reject')->nullable()->comment('Alasan jika ditolak');
      $table->uuid('reviewed_by')->nullable()->comment('Super Admin yang review');
      $table->timestamp('reviewed_at')->nullable();
      $table->timestamps();

      $table->index('user_id');
      $table->index('status');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('profile_change_requests');
  }
};
