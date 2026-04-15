<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Support\LoginEmailNormalizer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class PegawaiImport
{
    protected array $errors = [];
    protected array $processedNips = [];
    protected array $processedRows = [];
    protected array $reservedEmails = [];
    protected int $imported = 0;
    protected int $updated = 0;
    protected int $skipped = 0;

    /**
     * Import pegawai data from an Excel file.
     */
    public function import(string $filePath): self
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);

        $detectedFormat = $this->detectColumnMap($rows);
        $columnMap = $detectedFormat['column_map'];
        $dataStartIndex = $detectedFormat['data_start_index'];

        if ($columnMap === []) {
            $this->errors[] = 'Format kolom belum dikenali. Gunakan kolom: Nama Pegawai, NIP, Pangkat/Golongan, Jabatan, Unit Kerja (Role User opsional).';
            return $this;
        }

        if (!in_array('nama', array_values($columnMap), true)) {
            $this->errors[] = 'Kolom "Nama" wajib ada di file Excel.';
            return $this;
        }

        // Process data rows
        foreach ($rows as $index => $row) {
            if ($index < $dataStartIndex)
                continue;

            $rowNumber = (int) $index;
            $data = [];
            foreach ($columnMap as $col => $field) {
                $data[$field] = trim((string)($row[$col] ?? ''));
            }

            // Skip empty rows
            if (empty($data['nama']) && empty($data['nip'] ?? '') && empty($data['email'] ?? '')) {
                continue;
            }

            // Validate required fields
            if (empty($data['nama'])) {
                $this->errors[] = "Baris {$rowNumber}: Nama tidak boleh kosong.";
                $this->skipped++;
                continue;
            }

            // Clean NIP (optional) - remove spaces and non-numeric.
            $data['nip'] = preg_replace('/[^0-9]/', '', (string) ($data['nip'] ?? ''));

            if ($data['nip'] !== '' && strlen($data['nip']) !== 18) {
                $this->errors[] = "Baris {$rowNumber}: NIP '{$data['nip']}' harus tepat 18 digit (saat ini " . strlen($data['nip']) . " digit).";
                $this->skipped++;
                continue;
            }

            $data['email'] = $this->normalizeEmail($data['email'] ?? null);

            if ($data['email'] !== '' && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = "Baris {$rowNumber}: Format email tidak valid.";
                $this->skipped++;
                continue;
            }

            // Clean nomor_hp
            if (!empty($data['nomor_hp'])) {
                $cleaned = preg_replace('/[^0-9]/', '', $data['nomor_hp']);
                if (str_starts_with($cleaned, '62')) {
                    $cleaned = substr($cleaned, 2);
                } elseif (str_starts_with($cleaned, '0')) {
                    $cleaned = substr($cleaned, 1);
                }
                $data['nomor_hp'] = $cleaned;
            }

            // Handle is_active
            if (isset($data['is_active'])) {
                $val = strtolower($data['is_active']);
                $data['is_active'] = in_array($val, ['ya', 'yes', '1', 'aktif', 'true', 'active']);
            } else {
                $data['is_active'] = true;
            }

            // Upsert by NIP when present, otherwise update the same nama with empty NIP.
            try {
                $existing = null;
                if ($data['nip'] !== '') {
                    $existing = Pegawai::where('nip', $data['nip'])->first();
                } else {
                    $existing = Pegawai::where('nama', $data['nama'])
                        ->whereNull('nip')
                        ->first();
                }

                $rowKey = $data['nip'] !== ''
                    ? 'nip:' . $data['nip']
                    : 'nama:' . strtolower((string) $data['nama']);

                $resolvedEmail = $this->resolveEmailForRow($data['email'], $data['nama'], $existing?->id, $existing?->email);

                if ($existing) {
                    $existing->update([
                        'nama' => $data['nama'],
                        'nip' => $data['nip'] !== '' ? $data['nip'] : $existing->nip,
                        'email' => $resolvedEmail,
                        'jabatan' => $data['jabatan'] ?? $existing->jabatan,
                        'unit_kerja' => $data['unit_kerja'] ?? $existing->unit_kerja,
                        'nomor_hp' => !empty($data['nomor_hp']) ? $data['nomor_hp'] : $existing->nomor_hp,
                        'is_active' => $data['is_active'],
                    ]);
                    $this->updated++;
                    if ($data['nip'] !== '') {
                        $this->processedNips[$data['nip']] = $data['nip'];
                    }
                    $this->processedRows[$rowKey] = [
                        'nip' => $data['nip'],
                        'email' => $resolvedEmail,
                        'role_user_name' => (string) ($data['role_user_name'] ?? ''),
                    ];
                } else {
                    Pegawai::create([
                        'nama' => $data['nama'],
                        'nip' => $data['nip'] !== '' ? $data['nip'] : null,
                        'email' => $resolvedEmail,
                        'jabatan' => $data['jabatan'] ?? null,
                        'unit_kerja' => $data['unit_kerja'] ?? null,
                        'nomor_hp' => !empty($data['nomor_hp']) ? $data['nomor_hp'] : null,
                        'is_active' => $data['is_active'],
                    ]);
                    $this->imported++;
                    if ($data['nip'] !== '') {
                        $this->processedNips[$data['nip']] = $data['nip'];
                    }
                    $this->processedRows[$rowKey] = [
                        'nip' => $data['nip'],
                        'email' => $resolvedEmail,
                        'role_user_name' => (string) ($data['role_user_name'] ?? ''),
                    ];
                }
            } catch (Throwable $e) {
                $this->errors[] = "Baris {$rowNumber}: Gagal menyimpan data. Periksa format data dan pastikan tidak ada data duplikat.";
                $this->skipped++;
            }
        }

        return $this;
    }

    private function detectColumnMap(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $normalizedHeaders = [];

            foreach ($row as $col => $value) {
                $normalizedHeaders[$col] = $this->normalizeHeader((string) $value);
            }

            $mapped = $this->buildColumnMapFromHeaders($normalizedHeaders);

            if (
                in_array('nama', array_values($mapped), true)
                || in_array('nip', array_values($mapped), true)
                || in_array('jabatan', array_values($mapped), true)
                || in_array('unit_kerja', array_values($mapped), true)
            ) {
                return [
                    'header_row_index' => (int) $index,
                    'data_start_index' => ((int) $index) + 1,
                    'column_map' => $mapped,
                ];
            }
        }

        return $this->detectPositionalColumnMap($rows);
    }

    private function buildColumnMapFromHeaders(array $normalizedHeaders): array
    {
        $columnMap = [];

        foreach ($normalizedHeaders as $col => $header) {
            $mapped = match ($header) {
                'nama', 'nama lengkap', 'nama pegawai', 'nama staff', 'nama karyawan', 'nama_lengkap' => 'nama',
                'nip', 'nip pegawai' => 'nip',
                'pangkat golongan', 'pangkat gol', 'golongan', 'golongan ruang', 'pangkat' => 'pangkat_golongan',
                'jabatan', 'jabatan pegawai', 'jabatan saat ini', 'jabatan fungsional' => 'jabatan',
                'unit kerja', 'unit_kerja', 'satuan kerja', 'satminkal', 'instansi', 'kantor', 'cabang', 'unit' => 'unit_kerja',
                'email', 'e mail', 'email login' => 'email',
                'nomor hp', 'nomor_hp', 'no hp', 'hp', 'telepon', 'phone' => 'nomor_hp',
                'status', 'is active', 'aktif' => 'is_active',
                'role', 'role user', 'role_user', 'role pegawai', 'role_pegawai' => 'role_user_name',
                default => null,
            };

            if ($mapped !== null) {
                $columnMap[$col] = $mapped;
            }
        }

        return $columnMap;
    }

    private function detectPositionalColumnMap(array $rows): array
    {
        $firstDataRowIndex = null;
        $firstDataRow = [];

        foreach ($rows as $index => $row) {
            $nonEmpty = array_filter($row, fn($value) => trim((string) $value) !== '');

            if (count($nonEmpty) >= 2) {
                $firstDataRowIndex = (int) $index;
                $firstDataRow = $row;
                break;
            }
        }

        if ($firstDataRowIndex === null) {
            return [
                'header_row_index' => null,
                'data_start_index' => 1,
                'column_map' => [],
            ];
        }

        $colA = trim((string) ($firstDataRow['A'] ?? ''));
        $colB = trim((string) ($firstDataRow['B'] ?? ''));
        $hasNumberingColumn = preg_match('/^\d+$/', $colA) === 1 && $colB !== '';

        $columnMap = $hasNumberingColumn
            ? ['B' => 'nama', 'C' => 'nip', 'E' => 'jabatan', 'F' => 'unit_kerja']
            : ['A' => 'nama', 'B' => 'nip', 'D' => 'jabatan', 'E' => 'unit_kerja'];

        return [
            'header_row_index' => null,
            'data_start_index' => $firstDataRowIndex,
            'column_map' => $columnMap,
        ];
    }

    private function normalizeHeader(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = str_replace(['_', '-', '.', '/'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return trim((string) $normalized);
    }

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getUpdated(): int
    {
        return $this->updated;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function getProcessedNips(): array
    {
        return array_values($this->processedNips);
    }

    public function getProcessedRows(): array
    {
        return array_values($this->processedRows);
    }

    public function getSummary(): string
    {
        $parts = [];
        if ($this->imported > 0) $parts[] = "{$this->imported} data baru ditambahkan";
        if ($this->updated > 0) $parts[] = "{$this->updated} data diperbarui";
        if ($this->skipped > 0) $parts[] = "{$this->skipped} data dilewati";

        return implode(', ', $parts) ?: 'Tidak ada data yang diproses';
    }

    protected function normalizeEmail(?string $email): string
    {
        return LoginEmailNormalizer::normalizeEmail($email);
    }

    protected function resolveEmailForRow(string $importedEmail, string $nama, ?int $ignorePegawaiId = null, ?string $existingEmail = null): string
    {
        if ($importedEmail !== '') {
            $preferredEmail = LoginEmailNormalizer::sanitizePreferredEmail($importedEmail, $nama, 'pegawai');

            return $this->ensureUniqueEmail($preferredEmail, $ignorePegawaiId);
        }

        $current = $this->normalizeEmail($existingEmail);
        if ($current !== '') {
            $preferredEmail = LoginEmailNormalizer::sanitizePreferredEmail($current, $nama, 'pegawai');

            return $this->ensureUniqueEmail($preferredEmail, $ignorePegawaiId);
        }

        $generatedEmail = LoginEmailNormalizer::sanitizePreferredEmail('', $nama, 'pegawai');

        return $this->ensureUniqueEmail($generatedEmail, $ignorePegawaiId);
    }

    protected function ensureUniqueEmail(string $email, ?int $ignorePegawaiId = null): string
    {
        $normalized = $this->normalizeEmail($email);
        [$localPart, $domain] = array_pad(explode('@', $normalized, 2), 2, 'cadisdik13.local');
        $localPart = $localPart !== '' ? $localPart : 'pegawai';
        $domain = $domain !== '' ? $domain : 'cadisdik13.local';

        $counter = 0;
        do {
            $suffix = $counter > 0 ? '.' . $counter : '';
            $candidate = $localPart . $suffix . '@' . $domain;

            $existsInImport = isset($this->reservedEmails[$candidate]);
            $existsInDb = Pegawai::query()
                ->when($ignorePegawaiId !== null, fn($query) => $query->where('id', '!=', $ignorePegawaiId))
                ->where('email', $candidate)
                ->exists();

            $counter++;
        } while ($existsInImport || $existsInDb);

        $this->reservedEmails[$candidate] = true;

        return $candidate;
    }
}
