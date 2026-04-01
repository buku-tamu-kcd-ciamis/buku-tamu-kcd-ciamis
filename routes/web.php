<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BukuTamuController;
use App\Http\Controllers\PegawaiIzinController;
use App\Http\Controllers\UserManagementController;
use App\Models\DropdownOption;
use Illuminate\Support\Facades\File;
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

Route::get('/tentang-developer', function () {
    $contributorsRaw = File::exists(base_path('CONTRIBUTORS.md'))
        ? File::get(base_path('CONTRIBUTORS.md'))
        : '';

    $contactDirectory = [
        'ptragaluhhh28' => [
            'instagram_url' => 'https://www.instagram.com/luhptraa28/',
            'email' => 'putragaluh28@email.com',
            'linkedin_url' => null,
        ],
        'fikrihaikal17' => [
            'instagram_url' => 'https://www.instagram.com/fikrii_haikalll17/',
            'email' => 'fikrihaikal170308@email.com',
            'linkedin_url' => 'https://www.linkedin.com/in/fikriihaikall',
        ],
    ];

    $teamMembers = [];

    $memberPattern = '/###\s+(.+?)\R\R\*\*Role\*\*:\s*(.+?)\R\*\*GitHub\*\*:\s*\[@([^\]]+)\]\((https:\/\/github\.com\/[^)]+)\)\s*\R\*\*Responsibilities\*\*:\R\R((?:- .+\R)+)\R\*\*Contributions\*\*:\R\R((?:- .+\R)+)/u';

    if (preg_match_all($memberPattern, $contributorsRaw, $memberMatches, PREG_SET_ORDER)) {
        $teamMembers = collect($memberMatches)
            ->map(function (array $memberMatch) use ($contactDirectory) {
                $githubUrl = trim($memberMatch[4]);
                $githubUsername = ltrim(parse_url($githubUrl, PHP_URL_PATH) ?? trim($memberMatch[3]), '/');
                $contacts = $contactDirectory[strtolower($githubUsername)] ?? [];

                $parseList = static fn(string $block): array => collect(preg_split('/\R/', trim($block)) ?: [])
                    ->map(fn(string $line) => trim((string) preg_replace('/^-\s*/', '', trim($line))))
                    ->filter(fn(string $line) => filled($line))
                    ->values()
                    ->all();

                return [
                    'name' => trim($memberMatch[1]),
                    'role' => trim($memberMatch[2]),
                    'github_username' => $githubUsername,
                    'github_url' => $githubUrl,
                    'avatar_url' => "https://github.com/{$githubUsername}.png?size=240",
                    'responsibilities' => $parseList($memberMatch[5]),
                    'contributions' => $parseList($memberMatch[6]),
                    'instagram_url' => $contacts['instagram_url'] ?? null,
                    'linkedin_url' => $contacts['linkedin_url'] ?? null,
                    'email' => $contacts['email'] ?? null,
                ];
            })
            ->values()
            ->all();

        $leadIndex = collect($teamMembers)->search(
            fn(array $member): bool => str_contains(strtolower($member['role'] ?? ''), 'lead')
        );

        if ($leadIndex !== false) {
            $leadMember = $teamMembers[$leadIndex];
            array_splice($teamMembers, $leadIndex, 1);

            $centerIndex = intdiv(count($teamMembers), 2);
            array_splice($teamMembers, $centerIndex, 0, [$leadMember]);
        }
    }

    return view('public.developer', [
        'teamMembers' => $teamMembers,
    ]);
})->name('developer.about');

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

if (app()->environment('testing')) {
    Route::get('/__error-preview/{status}', function (int $status) {
        abort($status);
    })->whereIn('status', ['500', '501', '503']);
}

require __DIR__ . '/auth.php';
