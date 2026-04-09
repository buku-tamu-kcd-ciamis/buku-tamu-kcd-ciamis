<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Api\DropdownOptionController;
use App\Http\Controllers\Api\WebPushSubscriptionController;
use App\Http\Controllers\BukuTamuController;
use App\Http\Controllers\PegawaiIzinController;
use App\Http\Controllers\UserManagementController;
use App\Models\DropdownOption;
use App\Models\Pegawai;
use App\Models\PegawaiIzin;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', function () {
    $urls = collect([
        [
            'loc' => route('index'),
            'lastmod' => Carbon::now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ],
        [
            'loc' => route('developer.about'),
            'lastmod' => Carbon::now()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ],
    ]);

    $xml = view('seo.sitemap', ['urls' => $urls])->render();

    return response($xml, 200, [
        'Content-Type' => 'application/xml; charset=UTF-8',
    ]);
})->name('sitemap');

Route::get('/', function () {
    $apkCandidates = [
        storage_path('app/public/apk/buku-tamu-kcd.apk'),
        base_path('android/app/build/outputs/apk/release/app-release.apk'),
        base_path('android/app/build/outputs/apk/debug/app-debug.apk'),
    ];

    $apkDownloadAvailable = collect($apkCandidates)
        ->contains(fn(string $path): bool => File::exists($path));

    // Primary source: pegawai linked to users with Staff role.
    // Fallback: all active pegawai to avoid empty public dropdown when linkage is incomplete.
    $staffPegawai = \App\Models\User::whereHas('role_user', function ($q) {
        $q->where('name', 'Staff');
    })->whereNotNull('pegawai_id')
        ->with('pegawai')
        ->get()
        ->map(fn($u) => $u->pegawai)
        ->filter(fn($pegawai) => $pegawai && $pegawai->is_active)
        ->values();

    if ($staffPegawai->isEmpty()) {
        $staffPegawai = Pegawai::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();
    }

    $staffNips = $staffPegawai
        ->map(fn($pegawai) => trim((string) ($pegawai->nip ?? '')))
        ->filter()
        ->values();

    $staffNames = $staffPegawai
        ->map(fn($pegawai) => trim((string) ($pegawai->nama ?? '')))
        ->filter()
        ->values();

    $izinByNip = collect();
    $izinByName = collect();

    if ($staffNips->isNotEmpty() || $staffNames->isNotEmpty()) {
        $izinStaff = PegawaiIzin::query()
            ->select(['nip', 'nama_pegawai', 'status', 'tanggal_mulai', 'tanggal_selesai'])
            ->whereIn('status', [PegawaiIzin::STATUS_DISETUJUI, PegawaiIzin::STATUS_AKTIF])
            ->whereDate('tanggal_selesai', '>=', now()->toDateString())
            ->where(function ($query) use ($staffNips, $staffNames): void {
                if ($staffNips->isNotEmpty()) {
                    $query->whereIn('nip', $staffNips->all());
                }

                if ($staffNames->isNotEmpty()) {
                    $method = $staffNips->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('nama_pegawai', $staffNames->all());
                }
            })
            ->orderByDesc('tanggal_selesai')
            ->get();

        $izinByNip = $izinStaff
            ->filter(fn(PegawaiIzin $izin): bool => filled($izin->nip))
            ->keyBy(fn(PegawaiIzin $izin): string => trim((string) $izin->nip));

        $izinByName = $izinStaff
            ->filter(fn(PegawaiIzin $izin): bool => filled($izin->nama_pegawai))
            ->keyBy(fn(PegawaiIzin $izin): string => mb_strtolower(trim((string) $izin->nama_pegawai)));
    }

    $staffList = $staffPegawai
        ->map(function ($pegawai) use ($izinByNip, $izinByName): array {
            $pegawaiNip = trim((string) ($pegawai->nip ?? ''));
            $pegawaiName = trim((string) ($pegawai->nama ?? ''));

            $izin = filled($pegawaiNip)
                ? $izinByNip->get($pegawaiNip)
                : $izinByName->get(mb_strtolower($pegawaiName));

            if (!$izin && filled($pegawaiName)) {
                $izin = $izinByName->get(mb_strtolower($pegawaiName));
            }

            $isUnavailable = (bool) $izin;

            return [
                'value' => $pegawaiName,
                'label' => $pegawaiName . ($pegawai->jabatan ? ' — ' . $pegawai->jabatan : ''),
                'is_unavailable' => $isUnavailable,
                'availability_note' => $isUnavailable ? 'Tidak Masuk' : null,
            ];
        })
        ->unique('value')
        ->values()
        ->toArray();

    return view('public.index', [
        'jenisIdOptions' => DropdownOption::getFullOptions(DropdownOption::CATEGORY_JENIS_ID),
        'keperluanOptions' => DropdownOption::getFullOptions(DropdownOption::CATEGORY_KEPERLUAN),
        'kabupatenKotaOptions' => DropdownOption::getFullOptions(DropdownOption::CATEGORY_KABUPATEN_KOTA),
        'staffList' => $staffList,
        'apkDownloadAvailable' => $apkDownloadAvailable,
    ]);
})->name('index');

Route::get('/download/apk', function () {
    $apkCandidates = [
        storage_path('app/public/apk/buku-tamu-kcd.apk'),
        base_path('android/app/build/outputs/apk/release/app-release.apk'),
        base_path('android/app/build/outputs/apk/debug/app-debug.apk'),
    ];

    foreach ($apkCandidates as $path) {
        if (!File::exists($path)) {
            continue;
        }

        return response()->download(
            $path,
            'Buku-Tamu-KCD.apk',
            ['Content-Type' => 'application/vnd.android.package-archive']
        );
    }

    abort(404, 'File APK belum tersedia di server.');
})->name('apk.download');

Route::get('/api/dropdown-options/{category}', [DropdownOptionController::class, 'index'])
    ->name('dropdown-options');

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

    $viewData = [
        'teamMembers' => $teamMembers,
    ];

    foreach (['developer-about'] as $viewName) {
        if (!view()->exists($viewName)) {
            continue;
        }

        try {
            return response(view($viewName, $viewData)->render());
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    $memberItemsHtml = collect($teamMembers)
        ->map(function (array $member): string {
            $name = e((string) ($member['name'] ?? 'Developer'));
            $role = e((string) ($member['role'] ?? '-'));
            $github = e((string) ($member['github_url'] ?? '#'));

            return "<li><strong>{$name}</strong> <span>({$role})</span> - <a href=\"{$github}\" target=\"_blank\" rel=\"noopener\">GitHub</a></li>";
        })
        ->implode('');

    return response(
        '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tentang Developer</title><style>body{font-family:Arial,sans-serif;background:#f6f8f7;color:#13231a;padding:24px}main{max-width:860px;margin:auto;background:#fff;border:1px solid #d6e2da;border-radius:12px;padding:20px}h1{margin-top:0}ul{padding-left:20px}a{color:#0f8a50;text-decoration:none}a:hover{text-decoration:underline}</style></head><body><main><h1>Tentang Developer</h1><p>Mode fallback aktif karena view utama tidak dapat diakses.</p><ul>' . $memberItemsHtml . '</ul><p><a href="/">Kembali ke Beranda</a></p></main></body></html>'
    );
})->name('developer.about');

Route::post('/', [BukuTamuController::class, 'store'])->name('buku-tamu.store');
Route::get('/api/guest-by-nik', [BukuTamuController::class, 'getByNik'])->name('buku-tamu.get-by-nik');
Route::get('/preview/pegawai-izin/{id}', [PegawaiIzinController::class, 'preview'])
    ->middleware('signed')
    ->name('pegawai-izin.preview');

// Print routes — dilindungi auth middleware agar data sensitif tidak diakses publik
Route::middleware('auth')->group(function () {
    Route::post('/api/web-push/subscriptions', [WebPushSubscriptionController::class, 'store'])->name('web-push.subscribe');
    Route::delete('/api/web-push/subscriptions', [WebPushSubscriptionController::class, 'destroy'])->name('web-push.unsubscribe');

    Route::get('/print/buku-tamu/{id}', [BukuTamuController::class, 'print'])->name('buku-tamu.print');
    Route::get('/print/buku-tamu-bulk', [BukuTamuController::class, 'printBulk'])->name('buku-tamu.print-bulk');
    Route::get('/print/dropdown-options', [BukuTamuController::class, 'printDropdownOptions'])->name('dropdown-options.print');
    Route::get('/print/pegawai-piket', [BukuTamuController::class, 'printPegawaiPiket'])->name('pegawai-piket.print');
    Route::get('/print/data-pegawai', [BukuTamuController::class, 'printDataPegawai'])->name('data-pegawai.print');
    Route::get('/print/activity-logs', [ActivityLogController::class, 'print'])->name('activity-logs.print');
    Route::get('/print/users-bulk', [UserManagementController::class, 'printBulk'])->name('users.print-bulk');

    Route::get('/export/pegawai-excel', [UserManagementController::class, 'exportPegawaiExcel'])->name('pegawai.export-excel');

    Route::get('/print/pegawai-izin-bulk', [PegawaiIzinController::class, 'printBulk'])->name('admin.pegawai-izin.print-bulk');
    Route::get('/piket/pegawai-izin/{id}/print', [PegawaiIzinController::class, 'print'])->name('piket.pegawai-izin.print');
    Route::get('/admin/pegawai-izin/{id}/print', [PegawaiIzinController::class, 'print'])->name('admin.pegawai-izin.print');
});

if (app()->environment('testing')) {
    Route::get('/__error-preview/{status}', function (int $status) {
        abort($status);
    })->whereIn('status', ['500', '501', '503']);
}

require __DIR__ . '/auth.php';
