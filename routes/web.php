<?php

use App\Http\Controllers\BukuTamuController;
use App\Http\Controllers\PegawaiIzinController;
use App\Http\Controllers\Product\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.index');
})->name('index');

Route::post('/', [BukuTamuController::class, 'store'])->name('buku-tamu.store');
Route::get('/api/guest-by-nik', [BukuTamuController::class, 'getByNik'])->name('buku-tamu.get-by-nik');
Route::get('/print/buku-tamu/{id}', [BukuTamuController::class, 'print'])->name('buku-tamu.print');
Route::get('/print/buku-tamu-bulk', [BukuTamuController::class, 'printBulk'])->name('buku-tamu.print-bulk');

Route::get('/piket/pegawai-izin/{id}/print', [PegawaiIzinController::class, 'print'])->name('piket.pegawai-izin.print');

Route::prefix('/product')->name('product.')->controller(ProductController::class)->group(function () {
    Route::get('', 'index')->name('index');
});

require __DIR__ . '/auth.php';
