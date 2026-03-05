<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $today = now()->toDateString();

    // 1. Update 'disetujui' to 'aktif' if today is the start date
    \App\Models\PegawaiIzin::where('status', 'disetujui')
        ->where('tanggal_mulai', '<=', $today)
        ->update(['status' => 'aktif']);

    // 2. Update 'aktif' to 'selesai' if today is after the end date
    \App\Models\PegawaiIzin::where('status', 'aktif')
        ->where('tanggal_selesai', '<', $today)
        ->update(['status' => 'selesai']);
})->dailyAt('00:01')->name('update-izin-status')->withoutOverlapping();
