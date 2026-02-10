<?php

use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.index');
})->name('index');

Route::get('/loket', function () {
    return view('loket.index');
})->name('loket');

Route::get('/admin-panel', function () {
    return view('admin.index');
})->name('admin.panel');

Route::get('/superadmin', function () {
    return view('superadmin.index');
})->name('superadmin');

Route::prefix('/product')->name('product.')->controller(ProductController::class)->group(function () {
    Route::get('', 'index')->name('index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
