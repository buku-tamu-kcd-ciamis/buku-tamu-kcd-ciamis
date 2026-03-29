<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BukuTamuController;
use App\Http\Controllers\PegawaiIzinController;
use App\Http\Controllers\UserManagementController;
use App\Models\DropdownOption;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Get list of staff (pegawai linked to users with Staff role)
    $staffList = \App\Models\User::whereHas('role_user', function ($q) {
        $q->where('name', 'Staff');
    })->whereNotNull('pegawai_id')
        ->with('pegawai')
        ->get()
        ->filter(fn($u) => $u->pegawai && $u->pegawai->is_active)
        ->map(fn($u) => [
            'value' => $u->pegawai->nama,
            'label' => $u->pegawai->nama . ($u->pegawai->jabatan ? ' — ' . $u->pegawai->jabatan : ''),
        ])
        ->values()
        ->toArray();

    return view('public.index', [
        'jenisIdOptions' => DropdownOption::getFullOptions(DropdownOption::CATEGORY_JENIS_ID),
        'keperluanOptions' => DropdownOption::getFullOptions(DropdownOption::CATEGORY_KEPERLUAN),
        'kabupatenKotaOptions' => DropdownOption::getFullOptions(DropdownOption::CATEGORY_KABUPATEN_KOTA),
        'staffList' => $staffList,
    ]);
})->name('index');

Route::get('/api/dropdown-options/{category}', function (string $category) {
    if (!array_key_exists($category, DropdownOption::CATEGORY_LABELS)) {
        return response()->json(['error' => 'Invalid category'], 404);
    }
    return response()->json(DropdownOption::getFullOptions($category));
})->name('dropdown-options');

Route::post('/', [BukuTamuController::class, 'store'])->name('buku-tamu.store');
Route::get('/api/guest-by-nik', [BukuTamuController::class, 'getByNik'])->name('buku-tamu.get-by-nik');
// Print routes — dilindungi auth middleware agar data sensitif tidak diakses publik
Route::middleware('auth')->group(function () {
    Route::get('/print/buku-tamu/{id}', [BukuTamuController::class, 'print'])->name('buku-tamu.print');
    Route::get('/print/buku-tamu-bulk', [BukuTamuController::class, 'printBulk'])->name('buku-tamu.print-bulk');
    Route::get('/print/dropdown-options', [BukuTamuController::class, 'printDropdownOptions'])->name('dropdown-options.print');
    Route::get('/print/pegawai-piket', [BukuTamuController::class, 'printPegawaiPiket'])->name('pegawai-piket.print');
    Route::get('/print/data-pegawai', [BukuTamuController::class, 'printDataPegawai'])->name('data-pegawai.print');
    Route::get('/print/activity-logs', [ActivityLogController::class, 'print'])->name('activity-logs.print');
    Route::get('/print/users-bulk', [UserManagementController::class, 'printBulk'])->name('users.print-bulk');

    Route::get('/export/pegawai-excel', [UserManagementController::class, 'exportPegawaiExcel'])->name('pegawai.export-excel');

    Route::get('/piket/pegawai-izin/{id}/print', [PegawaiIzinController::class, 'print'])->name('piket.pegawai-izin.print');
    Route::get('/admin/pegawai-izin/{id}/print', [PegawaiIzinController::class, 'print'])->name('admin.pegawai-izin.print');
});

require __DIR__ . '/auth.php';
