<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('dropdown_options', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('category'); // jenis_id, keperluan, kabupaten_kota
      $table->string('value');
      $table->string('label');
      $table->json('metadata')->nullable(); // extra config like digits, icon, placeholder
      $table->integer('sort_order')->default(0);
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->index(['category', 'is_active', 'sort_order']);
      $table->unique(['category', 'value']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dropdown_options');
  }
};
