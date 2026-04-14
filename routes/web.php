<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Api\DropdownOptionController;
use App\Http\Controllers\Api\WebPushSubscriptionController;
use App\Http\Controllers\BukuTamuController;
use App\Http\Controllers\PegawaiIzinController;
use App\Http\Controllers\UserManagementController;
use App\Models\BukuTamu;
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
        public_path('apk/buku-tamu-kcd.apk'),
        public_path('apk/app-release.apk'),
        public_path('apk/app-debug.apk'),
        base_path('android/app/build/outputs/apk/release/app-release.apk'),
        base_path('android/app/build/outputs/apk/debug/app-debug.apk'),
    ];

    $apkDownloadAvailable = collect($apkCandidates)
        ->contains(fn(string $path): bool => File::exists($path));

    $extractStaffName = static function (string $selected): string {
        $selected = trim($selected);

        if ($selected === '') {
            return '';
        }

        $parts = preg_split('/\s+[—-]\s+/', $selected, 2);
        $name = trim((string) ($parts[0] ?? $selected));

        return trim((string) preg_replace('/\s+#\d+$/', '', $name));
    };

    $visitCountByRawStaff = BukuTamu::query()
        ->selectRaw('staff_dituju, count(*) as total')
        ->whereNotNull('staff_dituju')
        ->where('staff_dituju', '!=', '')
        ->groupBy('staff_dituju')
        ->pluck('total', 'staff_dituju');

    $visitCountByStaffName = collect($visitCountByRawStaff)
        ->reduce(function (array $carry, int|string $count, string $staff) use ($extractStaffName): array {
            $staffName = $extractStaffName((string) $staff);

            if ($staffName === '') {
                return $carry;
            }

            $staffNameKey = mb_strtolower($staffName);
            $carry[$staffNameKey] = (int) ($carry[$staffNameKey] ?? 0) + (int) $count;

            return $carry;
        }, []);

    $bagianDitujuOptions = DropdownOption::query()
        ->where('category', DropdownOption::CATEGORY_STAFF_DITUJU)
        ->where('is_active', true)
        ->select(['value', 'label', 'sort_order'])
        ->get()
        ->map(function (DropdownOption $option) use ($extractStaffName, $visitCountByRawStaff, $visitCountByStaffName): array {
            $value = trim((string) ($option->value ?? ''));
            $label = trim((string) ($option->label ?? ''));
            $staffName = $extractStaffName($value);
            $staffNameKey = mb_strtolower($staffName);

            $visitCountExact = (int) ($visitCountByRawStaff[$value] ?? 0);
            $visitCountByName = (int) ($visitCountByStaffName[$staffNameKey] ?? 0);
            $visitCount = max($visitCountExact, $visitCountByName);

            if ($value === '' && $label !== '') {
                $value = $label;
            }

            if ($label === '' && $value !== '') {
                $label = $value;
            }

            return [
                'value' => $value,
                'label' => $label,
                'visit_count' => $visitCount,
                'sort_order' => (int) ($option->sort_order ?? 0),
            ];
        })
        ->filter(fn(array $option): bool => $option['value'] !== '' && $option['label'] !== '')
        ->unique('value')
        ->sort(function (array $a, array $b): int {
            $visitCompare = ((int) ($b['visit_count'] ?? 0)) <=> ((int) ($a['visit_count'] ?? 0));

            if ($visitCompare !== 0) {
                return $visitCompare;
            }

            $sortCompare = ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0));

            if ($sortCompare !== 0) {
                return $sortCompare;
            }

            return strnatcasecmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        })
        ->values();

    if ($bagianDitujuOptions->isEmpty()) {
        $bagianDitujuOptions = Pegawai::query()
            ->where('is_active', true)
            ->get(['nama', 'jabatan'])
            ->map(function (Pegawai $pegawai) use ($visitCountByRawStaff, $visitCountByStaffName): array {
                $nama = trim((string) ($pegawai->nama ?? ''));
                $jabatan = trim((string) ($pegawai->jabatan ?? ''));
                $display = $jabatan !== '' ? ($nama . ' — ' . $jabatan) : $nama;
                $staffNameKey = mb_strtolower($nama);

                $visitCountExact = (int) ($visitCountByRawStaff[$display] ?? 0);
                $visitCountByName = (int) ($visitCountByStaffName[$staffNameKey] ?? 0);
                $visitCount = max($visitCountExact, $visitCountByName);

                return [
                    'value' => $display,
                    'label' => $display,
                    'visit_count' => $visitCount,
                ];
            })
            ->filter(fn(array $option): bool => trim((string) $option['value']) !== '')
            ->unique('value')
            ->sort(function (array $a, array $b): int {
                $visitCompare = ((int) ($b['visit_count'] ?? 0)) <=> ((int) ($a['visit_count'] ?? 0));

                if ($visitCompare !== 0) {
                    return $visitCompare;
                }

                return strnatcasecmp((string) ($a['value'] ?? ''), (string) ($b['value'] ?? ''));
            })
            ->values();
    }

    $staffNames = $bagianDitujuOptions
        ->map(fn(array $option): string => $extractStaffName((string) $option['value']))
        ->filter()
        ->values();

    $pegawaiByName = Pegawai::query()
        ->select(['nama', 'nip'])
        ->whereIn('nama', $staffNames->all())
        ->get()
        ->groupBy(fn(Pegawai $pegawai): string => mb_strtolower(trim((string) ($pegawai->nama ?? ''))));

    $staffNips = $pegawaiByName
        ->flatMap(fn($items) => $items->pluck('nip'))
        ->map(fn($nip) => trim((string) $nip))
        ->filter()
        ->unique()
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

    $staffList = $bagianDitujuOptions
        ->values()
        ->map(function (array $option) use ($extractStaffName, $pegawaiByName, $izinByNip, $izinByName): array {
            $value = trim((string) ($option['value'] ?? ''));
            $label = trim((string) ($option['label'] ?? $value));
            $staffName = $extractStaffName($value);
            $staffNameKey = mb_strtolower($staffName);

            $pegawaiNips = $pegawaiByName
                ->get($staffNameKey, collect())
                ->pluck('nip')
                ->map(fn($nip): string => trim((string) $nip))
                ->filter();

            $hasIzinByNip = $pegawaiNips->contains(fn(string $nip): bool => $izinByNip->has($nip));
            $hasIzinByName = $staffName !== '' && $izinByName->has($staffNameKey);
            $isUnavailable = $hasIzinByNip || $hasIzinByName;

            return [
                'value' => $value,
                'label' => $label,
                'is_unavailable' => $isUnavailable,
                'availability_note' => $isUnavailable ? 'Tidak Masuk' : null,
            ];
        })
        ->filter(fn(array $option): bool => $option['value'] !== '' && $option['label'] !== '')
        ->unique('value')
        ->values()
        ->toArray();

    return view('public.index', [
        'jenisIdOptions' => DropdownOption::getFullOptions(DropdownOption::CATEGORY_JENIS_ID),
        'keperluanOptions' => DropdownOption::getFullOptions(DropdownOption::CATEGORY_KEPERLUAN),
        'kabupatenKotaOptions' => DropdownOption::getFullOptions(DropdownOption::CATEGORY_KABUPATEN_KOTA),
        'bagianDitujuOptions' => $bagianDitujuOptions->values()->toArray(),
        'staffList' => $staffList,
        'apkDownloadAvailable' => $apkDownloadAvailable,
    ]);
})->name('index');

Route::get('/download/apk', function () {
    $apkCandidates = [
        storage_path('app/public/apk/buku-tamu-kcd.apk'),
        public_path('apk/buku-tamu-kcd.apk'),
        public_path('apk/app-release.apk'),
        public_path('apk/app-debug.apk'),
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
    Route::get('/admin/activity-logs/backup/download', [ActivityLogController::class, 'backupDownload'])->name('activity-logs.backup-download');
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
