<?php

namespace App\Http\Controllers;

use App\Models\BukuTamu;
use App\Models\NomorSuratSetting;
use App\Models\StaffNotification;
use App\Services\BookingChatManager;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BukuTamuController extends Controller
{
    /**
     * Lookup guest data for auto-fill by NIK, full name, phone number, or email.
     */
    public function getByNik(Request $request)
    {
        $nik = trim((string) $request->query('nik', ''));
        $namaLengkap = trim((string) $request->query('nama_lengkap', ''));
        $nomorHp = trim((string) $request->query('nomor_hp', ''));
        $email = trim((string) $request->query('email', ''));
        $isSuggest = $request->boolean('suggest', false);

        if ($nik === '' && $namaLengkap === '' && $nomorHp === '' && $email === '') {
            return response()->json(['found' => false, 'suggestions' => []]);
        }

        if ($isSuggest) {
            $suggestions = $this->buildSuggestionPayload(
                $this->findGuestSuggestions($namaLengkap, $nomorHp, $email)
            );

            return response()->json([
                'found' => false,
                'suggestions' => $suggestions,
            ]);
        }

        $guest = null;
        $matchedBy = null;

        if ($nik !== '') {
            $guest = BukuTamu::where('nik', $nik)
                ->orderBy('created_at', 'desc')
                ->first();
            $matchedBy = 'nik';
        }

        if (!$guest && $email !== '') {
            $guest = BukuTamu::whereRaw('LOWER(email) = ?', [strtolower($email)])
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$guest) {
                $guest = BukuTamu::where('email', 'like', '%' . $email . '%')
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            $matchedBy = 'email';
        }

        if (!$guest && $namaLengkap !== '') {
            $nameMatches = BukuTamu::where('nama_lengkap', 'like', '%' . $namaLengkap . '%')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($nameMatches->count() > 1) {
                $nameSuggestions = $this->buildSuggestionPayload($nameMatches);

                if ($nameSuggestions->count() === 1) {
                    return response()->json([
                        'found' => true,
                        'matched_by' => 'nama_lengkap',
                        'data' => $nameSuggestions->first(),
                    ]);
                }

                return response()->json([
                    'found' => false,
                    'multiple' => true,
                    'matched_by' => 'nama_lengkap',
                    'suggestions' => $nameSuggestions,
                ]);
            }

            $guest = $nameMatches->first();
            $matchedBy = 'nama_lengkap';
        }

        if (!$guest && $nomorHp !== '') {
            $nomorHpNormalized = $this->normalizePhone($nomorHp);
            $nomorHpLocal = str_starts_with($nomorHpNormalized, '62')
                ? substr($nomorHpNormalized, 2)
                : ltrim($nomorHpNormalized, '0');

            $nomorHpDbExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(nomor_hp, '-', ''), ' ', ''), '+', ''), '(', ''), ')', ''), '.', '')";

            $guest = BukuTamu::where(function ($query) use ($nomorHp, $nomorHpNormalized, $nomorHpLocal, $nomorHpDbExpr) {
                $hasCondition = false;

                if ($nomorHp !== '') {
                    $query->where('nomor_hp', 'like', '%' . $nomorHp . '%');
                    $hasCondition = true;
                }

                if ($nomorHpNormalized !== '') {
                    $method = $hasCondition ? 'orWhereRaw' : 'whereRaw';
                    $query->{$method}($nomorHpDbExpr . ' like ?', ['%' . $nomorHpNormalized . '%']);
                    $hasCondition = true;
                }

                if ($nomorHpLocal !== '' && $nomorHpLocal !== $nomorHpNormalized) {
                    $method = $hasCondition ? 'orWhereRaw' : 'whereRaw';
                    $query->{$method}($nomorHpDbExpr . ' like ?', ['%' . $nomorHpLocal . '%']);
                }
            })
                ->orderBy('created_at', 'desc')
                ->first();
            $matchedBy = 'nomor_hp';
        }

        if (!$guest) {
            return response()->json([
                'found' => false,
                'suggestions' => $this->buildSuggestionPayload(
                    $this->findGuestSuggestions($namaLengkap, $nomorHp, $email)
                ),
            ]);
        }

        return response()->json([
            'found' => true,
            'matched_by' => $matchedBy,
            'data' => $this->formatGuestData($guest),
        ]);
    }

    private function findGuestSuggestions(string $namaLengkap, string $nomorHp, string $email)
    {
        $namaLengkap = trim($namaLengkap);
        $nomorHp = trim($nomorHp);
        $email = trim($email);

        if ($namaLengkap === '' && $nomorHp === '' && $email === '') {
            return collect();
        }

        $nomorHpNormalized = $this->normalizePhone($nomorHp);
        $nomorHpLocal = str_starts_with($nomorHpNormalized, '62')
            ? substr($nomorHpNormalized, 2)
            : ltrim($nomorHpNormalized, '0');

        $nomorHpDbExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(nomor_hp, '-', ''), ' ', ''), '+', ''), '(', ''), ')', ''), '.', '')";

        return BukuTamu::where(function ($query) use ($namaLengkap, $nomorHp, $email, $nomorHpNormalized, $nomorHpLocal, $nomorHpDbExpr) {
            $hasCondition = false;

            if ($namaLengkap !== '') {
                $query->where('nama_lengkap', 'like', '%' . $namaLengkap . '%');
                $hasCondition = true;
            }

            if ($email !== '') {
                $method = $hasCondition ? 'orWhere' : 'where';
                $query->{$method}('email', 'like', '%' . $email . '%');
                $hasCondition = true;
            }

            if ($nomorHp !== '') {
                $method = $hasCondition ? 'orWhere' : 'where';
                $query->{$method}('nomor_hp', 'like', '%' . $nomorHp . '%');
                $hasCondition = true;
            }

            if ($nomorHpNormalized !== '') {
                $method = $hasCondition ? 'orWhereRaw' : 'whereRaw';
                $query->{$method}($nomorHpDbExpr . ' like ?', ['%' . $nomorHpNormalized . '%']);
                $hasCondition = true;
            }

            if ($nomorHpLocal !== '' && $nomorHpLocal !== $nomorHpNormalized) {
                $method = $hasCondition ? 'orWhereRaw' : 'whereRaw';
                $query->{$method}($nomorHpDbExpr . ' like ?', ['%' . $nomorHpLocal . '%']);
            }
        })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function formatGuestData(BukuTamu $guest, bool $forSuggestion = false): array
    {
        $payload = [
            'jenis_id' => $guest->jenis_id,
            'nik' => $guest->nik,
            'nama_lengkap' => $guest->nama_lengkap,
            'instansi' => $guest->instansi,
            'nomor_hp' => $guest->nomor_hp,
            'jabatan' => $guest->jabatan,
            'kabupaten_kota' => $guest->kabupaten_kota,
            'email' => $guest->email,
        ];

        if ($forSuggestion) {
            $payload['display_label'] = trim(
                ($guest->nama_lengkap ?? '-') .
                    ' | ' .
                    ($guest->jenis_id ?? '-') . ': ' . ($guest->nik ?? '-') .
                    ' | ' .
                    ($guest->nomor_hp ?? '-')
            );
        }

        return $payload;
    }

    private function buildSuggestionPayload($guests)
    {
        return $guests
            ->unique(function (BukuTamu $guest) {
                return strtolower(
                    ($guest->nama_lengkap ?? '') . '|' .
                        ($guest->nik ?? '') . '|' .
                        ($guest->nomor_hp ?? '') . '|' .
                        ($guest->email ?? '')
                );
            })
            ->take(8)
            ->map(fn(BukuTamu $guest) => $this->formatGuestData($guest, true))
            ->values();
    }

    private function normalizePhone(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    public function store(Request $request)
    {
        try {
            // Validasi data
            $validatedData = $request->validate([
                'jenis_id' => 'required|string',
                'nik' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $jenisId = $request->input('jenis_id');
                        $option = \App\Models\DropdownOption::where('category', \App\Models\DropdownOption::CATEGORY_JENIS_ID)
                            ->where('value', $jenisId)
                            ->first();

                        $metadata = $option ? $option->metadata : [];
                        $maxRepeated = (int) ($metadata['max_repeated_digits'] ?? 3);
                        $maxSequential = max((int) ($metadata['max_sequential_digits'] ?? 4), 4);
                        $requiredDigits = $metadata['digits'] ?? null;

                        // Check digits length if specified
                        if ($requiredDigits && strlen($value) != $requiredDigits) {
                            $fail('Nomor ID harus berjumlah ' . $requiredDigits . ' digit.');
                        }

                        // Check for repeated digits
                        if (preg_match('/(\d)\1{' . $maxRepeated . ',}/', $value)) {
                            $fail('Nomor ID tidak valid. Angka tidak boleh sama lebih dari ' . $maxRepeated . ' digit berturut-turut.');
                        }

                        // Check for sequential digits
                        for ($i = 0; $i < strlen($value) - $maxSequential; $i++) {
                            $isSequentialAsc = true;
                            $isSequentialDesc = true;

                            for ($j = 0; $j < $maxSequential; $j++) {
                                $digit = (int) $value[$i + $j];
                                $nextDigit = (int) $value[$i + $j + 1];

                                if ($nextDigit !== $digit + 1)
                                    $isSequentialAsc = false;
                                if ($nextDigit !== $digit - 1)
                                    $isSequentialDesc = false;
                            }

                            if ($isSequentialAsc || $isSequentialDesc) {
                                $fail('Nomor ID tidak valid. Angka tidak boleh berurutan lebih dari ' . $maxSequential . ' digit.');
                                break;
                            }
                        }
                    },
                ],
                'nama_lengkap' => 'required|string|max:255',
                'instansi' => 'nullable|string|max:255',
                'nomor_hp' => 'required|string|max:15',
                'jabatan' => 'nullable|string|max:255',
                'kabupaten_kota' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'keperluan' => 'required|string',
                'foto_selfie' => 'required|string',
                'foto_penerimaan' => 'nullable|string',
                'tanda_tangan' => 'required|string',
                'staff_dituju' => 'required|string|max:255',
            ]);

            // Proses & kompres gambar ke filesystem (bukan disimpan mentah di database)
            $validatedData['foto_selfie'] = ImageHelper::processAndStore(
                $validatedData['foto_selfie'],
                'buku-tamu/selfie',
                1000,
                65
            );

            $validatedData['foto_penerimaan'] = ImageHelper::processAndStore(
                $validatedData['foto_penerimaan'] ?? null,
                'buku-tamu/penerimaan',
                1000,
                65
            );

            $validatedData['tanda_tangan'] = ImageHelper::processAndStore(
                $validatedData['tanda_tangan'],
                'buku-tamu/ttd',
                600,
                80
            );

            // Simpan data ke database
            $bukuTamu = BukuTamu::create($validatedData);

            // Create staff notification
            if (!empty($validatedData['staff_dituju'])) {
                $staffUsers = \App\Models\User::whereHas('role_user', function ($q) {
                    $q->where('name', 'Staff');
                })->whereHas('pegawai', function ($q) use ($validatedData) {
                    $q->where('nama', $validatedData['staff_dituju']);
                })->get();

                foreach ($staffUsers as $staffUser) {
                    StaffNotification::create([
                        'user_id' => $staffUser->id,
                        'buku_tamu_id' => $bukuTamu->id,
                        'type' => 'tamu_baru',
                        'message' => "Tamu baru '{$bukuTamu->nama_lengkap}' dari {$bukuTamu->instansi} ingin menemui Anda. Keperluan: {$bukuTamu->keperluan}",
                    ]);
                }

                app(BookingChatManager::class)->bootstrapForBooking($bukuTamu, Auth::user());
            }

            return redirect()->route('index')->with('success', 'Data buku tamu berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error('Error saving buku tamu: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data.')->withInput();
        }
    }

    /**
     * Print surat pengantar / detail kunjungan
     */
    public function print($id)
    {
        $tamu = BukuTamu::findOrFail($id);
        $nomorSuratSetting = NomorSuratSetting::getByJenis('buku_tamu');

        if (Auth::check()) {
            activity('cetak')
                ->performedOn($tamu)
                ->causedBy(Auth::user())
                ->withProperties(['nama_tamu' => $tamu->nama_lengkap, 'tipe' => 'buku_tamu_detail'])
                ->log("Mencetak detail kunjungan tamu '{$tamu->nama_lengkap}'");
        }

        return view('print.buku-tamu', compact('tamu', 'nomorSuratSetting'));
    }

    /**
     * Print bulk / filtered kunjungan
     */
    public function printBulk(Request $request)
    {
        $selectedIds = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn(string $id): int => (int) trim($id))
            ->filter(fn(int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($selectedIds->isNotEmpty()) {
            $query = BukuTamu::query()->whereIn('id', $selectedIds->all());
        } else {
            $query = BukuTamu::query()->where('status', 'selesai');

            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            if ($request->has('nama') && $request->nama) {
                $query->where('nama_lengkap', 'like', '%' . $request->nama . '%');
            }

            if ($request->has('keperluan') && $request->keperluan) {
                $query->where('keperluan', 'like', '%' . $request->keperluan . '%');
            }

            if ($request->has('type') && $request->type === 'pengantar') {
                $query->where(function ($q) {
                    $q->where('keperluan', 'like', '%berkas%')
                        ->orWhere('keperluan', 'like', '%surat%')
                        ->orWhere('keperluan', 'like', '%dokumen%')
                        ->orWhere('keperluan', 'like', '%legalisir%');
                });
            }
        }

        $tamuList = $query->orderBy('created_at', 'desc')->get();

        $kepalaCabdin = \App\Models\PengaturanKcd::getSettings();
        $nomorSuratSetting = NomorSuratSetting::getByJenis('buku_tamu');

        $filterLog = [
            'ids' => $selectedIds->isNotEmpty() ? $selectedIds->all() : null,
            'start_date' => $selectedIds->isEmpty() ? $request->start_date : null,
            'end_date' => $selectedIds->isEmpty() ? $request->end_date : null,
            'nama' => $selectedIds->isEmpty() ? $request->nama : null,
        ];

        if (Auth::check()) {
            activity('cetak')
                ->causedBy(Auth::user())
                ->withProperties([
                    'jumlah' => $tamuList->count(),
                    'tipe' => $request->query('type', 'buku_tamu_bulk'),
                    'filter' => array_filter($filterLog, fn($value): bool => !blank($value)),
                ])
                ->log('Mencetak laporan buku tamu (' . $tamuList->count() . ' data)');
        }

        if ($selectedIds->isNotEmpty()) {
            return view('print.buku-tamu-bulk-per-orang', compact('tamuList', 'nomorSuratSetting'));
        }

        return view('print.buku-tamu-bulk', compact('tamuList', 'kepalaCabdin'));
    }

    /**
     * Print dropdown options data
     */
    public function printDropdownOptions(Request $request)
    {
        $category = $request->query('category', 'all');

        if ($category === 'all') {
            $options = \App\Models\DropdownOption::orderBy('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('category');
        } else {
            $options = collect([
                $category => \App\Models\DropdownOption::where('category', $category)
                    ->orderBy('sort_order')
                    ->get()
            ]);
        }

        $categoryLabels = \App\Models\DropdownOption::CATEGORY_LABELS;

        if (Auth::check()) {
            $catName = $category === 'all' ? 'Semua Kategori' : ($categoryLabels[$category] ?? $category);
            activity('cetak')
                ->causedBy(Auth::user())
                ->withProperties(['kategori' => $catName, 'tipe' => 'dropdown_options'])
                ->log("Mencetak data dropdown options ({$catName})");
        }

        return view('print.dropdown-options', compact('options', 'categoryLabels', 'category'));
    }

    /**
     * Print pegawai piket data
     */
    public function printPegawaiPiket()
    {
        $pegawaiList = \App\Models\DropdownOption::where('category', \App\Models\DropdownOption::CATEGORY_PEGAWAI_PIKET)
            ->orderBy('sort_order')
            ->get();

        $kepalaCabdin = \App\Models\PengaturanKcd::getSettings();

        if (Auth::check()) {
            activity('cetak')
                ->causedBy(Auth::user())
                ->withProperties(['jumlah' => $pegawaiList->count(), 'tipe' => 'pegawai_piket'])
                ->log('Mencetak data pegawai piket (' . $pegawaiList->count() . ' data)');
        }

        return view('print.pegawai-piket', compact('pegawaiList', 'kepalaCabdin'));
    }

    public function printDataPegawai()
    {
        $pegawaiList = \App\Models\Pegawai::orderBy('nama')->get();

        $kepalaCabdin = \App\Models\PengaturanKcd::getSettings();

        if (Auth::check()) {
            activity('cetak')
                ->causedBy(Auth::user())
                ->withProperties(['jumlah' => $pegawaiList->count(), 'tipe' => 'data_pegawai'])
                ->log('Mencetak data pegawai (' . $pegawaiList->count() . ' data)');
        }

        return view('print.data-pegawai', compact('pegawaiList', 'kepalaCabdin'));
    }
}
