<?php

use App\Http\Controllers\BukuTamuController;
use App\Http\Controllers\Product\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.index');
})->name('index');

Route::post('/', [BukuTamuController::class, 'store'])->name('buku-tamu.store');

Route::prefix('/product')->name('product.')->controller(ProductController::class)->group(function () {
    Route::get('', 'index')->name('index');
});

require __DIR__ . '/auth.php';
