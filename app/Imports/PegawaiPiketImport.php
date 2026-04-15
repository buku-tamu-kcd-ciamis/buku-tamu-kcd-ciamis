<?php

namespace App\Imports;

use App\Models\DropdownOption;
use App\Models\Pegawai;
use App\Models\User;
use App\Support\LoginEmailNormalizer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class PegawaiPiketImport
{
    protected array $errors = [];
    protected array $processedRows = [];
    protected array $reservedEmails = [];
    protected int $imported = 0;
    protected int $updated = 0;
    protected int $skipped = 0;

    /**
     * Import pegawai piket data from an Excel file.
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

        if (!in_array('label', array_values($columnMap), true)) {
            $this->errors[] = 'Kolom "Nama Lengkap" wajib ada di file Excel.';
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
            if (empty($data['label']) && empty($data['nip'] ?? '') && empty($data['email'] ?? '')) {
                continue;
            }

            if (empty($data['label'])) {
                $this->errors[] = "Baris {$rowNumber}: Nama pegawai tidak boleh kosong.";
                $this->skipped++;
                continue;
            }

            $data['nip'] = preg_replace('/[^0-9]/', '', (string) ($data['nip'] ?? ''));

            if ($data['nip'] !== '' && strlen($data['nip']) !== 18) {
                $this->errors[] = "Baris {$rowNumber}: NIP '{$data['nip']}' harus tepat 18 digit (saat ini " . strlen($data['nip']) . " digit).";
                $this->skipped++;
                continue;
            }

            // Auto-fill value from label if not provided
            if (empty($data['value'] ?? '')) {
                $data['value'] = $data['label'];
            }

            $data['email'] = $this->normalizeEmail($data['email'] ?? null);
            if ($data['email'] !== '' && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = "Baris {$rowNumber}: Format email tidak valid.";
                $this->skipped++;
                continue;
            }

            // Handle is_active
            if (isset($data['is_active']) && $data['is_active'] !== '') {
                $val = strtolower($data['is_active']);
                $data['is_active'] = in_array($val, ['ya', 'yes', '1', 'aktif', 'true', 'active']);
            } else {
                $data['is_active'] = true;
            }

            try {
                $existing = null;

                if ($data['nip'] !== '') {
                    $existing = DropdownOption::query()
                        ->where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)
                        ->where('metadata->nip', $data['nip'])
                        ->first();
                }

                if (! $existing) {
                    $existing = DropdownOption::query()
                        ->where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)
                        ->where('label', $data['label'])
                        ->first();
                }

                $matchedPegawai = $this->resolvePegawaiByData($data);

                $resolvedEmail = $this->resolveEmailForRow(
                    $data['email'],
                    $data['label'],
                    is_array($existing?->metadata) ? ($existing->metadata['email'] ?? null) : null,
                    $matchedPegawai?->email
                );

                $metadata = is_array($existing?->metadata) ? $existing->metadata : [];
                $metadata['email'] = $resolvedEmail;

                $resolvedNip = $data['nip'] !== '' ? $data['nip'] : (string) ($matchedPegawai?->nip ?? '');
                if ($resolvedNip !== '') {
                    $metadata['nip'] = $resolvedNip;
                } else {
                    unset($metadata['nip']);
                }

                $resolvedJabatan = trim((string) ($data['jabatan'] ?? ''));
                if ($resolvedJabatan === '' && $matchedPegawai) {
                    $resolvedJabatan = trim((string) ($matchedPegawai->jabatan ?? ''));
                }
                if ($resolvedJabatan !== '') {
                    $metadata['jabatan'] = $resolvedJabatan;
                }

                $resolvedUnitKerja = trim((string) ($data['unit_kerja'] ?? ''));
                if ($resolvedUnitKerja === '' && $matchedPegawai) {
                    $resolvedUnitKerja = trim((string) ($matchedPegawai->unit_kerja ?? ''));
                }
                if ($resolvedUnitKerja !== '') {
                    $metadata['unit_kerja'] = $resolvedUnitKerja;
                }

                if ($matchedPegawai) {
                    $metadata['pegawai_id'] = $matchedPegawai->id;
                }

                $nextSort = DropdownOption::where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)->max('sort_order') ?? 0;

                if ($existing) {
                    $existing->update([
                        'value' => $data['value'],
                        'metadata' => $metadata,
                        'is_active' => $data['is_active'],
                    ]);
                    $this->updated++;
                } else {
                    DropdownOption::create([
                        'category' => DropdownOption::CATEGORY_PEGAWAI_PIKET,
                        'label' => $data['label'],
                        'value' => $data['value'],
                        'metadata' => ['email' => $resolvedEmail],
                        'sort_order' => $nextSort + 1,
                        'is_active' => $data['is_active'],
                    ]);
                    $this->imported++;
                }

                $rowKey = $resolvedNip !== ''
                    ? 'nip:' . $resolvedNip
                    : ($resolvedEmail !== '' ? 'email:' . $resolvedEmail : 'label:' . strtolower((string) $data['label']));

                $this->processedRows[$rowKey] = [
                    'label' => $data['label'],
                    'email' => $resolvedEmail,
                    'nip' => $resolvedNip,
                    'pegawai_id' => $matchedPegawai?->id,
                ];
            } catch (Throwable $e) {
                $this->errors[] = "Baris {$rowNumber}: Gagal menyimpan data. Periksa format data dan pastikan tidak ada data duplikat.";
                $this->skipped++;
            }
        }

        return $this;
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

    private function detectColumnMap(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $normalizedHeaders = [];

            foreach ($row as $col => $value) {
                $normalizedHeaders[$col] = $this->normalizeHeader((string) $value);
            }

            $mapped = $this->buildColumnMapFromHeaders($normalizedHeaders);

            if (
                in_array('label', array_values($mapped), true)
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
                'nama', 'nama lengkap', 'nama pegawai', 'nama piket', 'nama_lengkap' => 'label',
                'nip', 'nip pegawai' => 'nip',
                'pangkat golongan', 'pangkat gol', 'golongan', 'golongan ruang', 'pangkat' => 'pangkat_golongan',
                'jabatan', 'jabatan pegawai', 'jabatan saat ini', 'jabatan fungsional' => 'jabatan',
                'unit kerja', 'unit_kerja', 'satuan kerja', 'satminkal', 'instansi', 'kantor', 'cabang', 'unit' => 'unit_kerja',
                'email', 'e mail', 'email login' => 'email',
                'nilai', 'value', 'id internal', 'id_internal', 'kode' => 'value',
                'status', 'aktif', 'is active', 'is_active' => 'is_active',
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
            ? ['B' => 'label', 'C' => 'nip', 'E' => 'jabatan', 'F' => 'unit_kerja', 'G' => 'role_user_name']
            : ['A' => 'label', 'B' => 'nip', 'D' => 'jabatan', 'E' => 'unit_kerja', 'F' => 'role_user_name'];

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

    private function resolvePegawaiByData(array $data): ?Pegawai
    {
        $nip = preg_replace('/[^0-9]/', '', (string) ($data['nip'] ?? ''));
        if ($nip !== '') {
            $pegawai = Pegawai::query()->where('nip', $nip)->first();
            if ($pegawai) {
                return $pegawai;
            }
        }

        $email = $this->normalizeEmail((string) ($data['email'] ?? ''));
        if ($email !== '') {
            $pegawai = Pegawai::query()->where('email', $email)->first();
            if ($pegawai) {
                return $pegawai;
            }
        }

        $label = trim((string) ($data['label'] ?? ''));
        if ($label !== '') {
            return Pegawai::query()->where('nama', $label)->first();
        }

        return null;
    }

    protected function resolveEmailForRow(string $importedEmail, string $label, ?string $existingEmail = null, ?string $pegawaiEmail = null): string
    {
        if ($importedEmail !== '') {
            $preferredEmail = LoginEmailNormalizer::sanitizePreferredEmail($importedEmail, $label, 'piket');

            return $this->ensureUniqueEmail($preferredEmail, $existingEmail);
        }

        $pegawaiEmail = $this->normalizeEmail($pegawaiEmail);
        if ($pegawaiEmail !== '' && filter_var($pegawaiEmail, FILTER_VALIDATE_EMAIL)) {
            $preferredEmail = LoginEmailNormalizer::sanitizePreferredEmail($pegawaiEmail, $label, 'piket');

            return $this->ensureUniqueEmail($preferredEmail, $existingEmail);
        }

        $current = $this->normalizeEmail($existingEmail);
        if ($current !== '') {
            $preferredEmail = LoginEmailNormalizer::sanitizePreferredEmail($current, $label, 'piket');

            return $this->ensureUniqueEmail($preferredEmail, $existingEmail);
        }

        $generatedEmail = LoginEmailNormalizer::sanitizePreferredEmail('', $label, 'piket');

        return $this->ensureUniqueEmail($generatedEmail);
    }

    protected function ensureUniqueEmail(string $email, ?string $existingEmail = null): string
    {
        $normalized = $this->normalizeEmail($email);
        [$localPart, $domain] = array_pad(explode('@', $normalized, 2), 2, 'cadisdik13.local');
        $localPart = $localPart !== '' ? $localPart : 'piket';
        $domain = $domain !== '' ? $domain : 'cadisdik13.local';

        $existingNormalized = $this->normalizeEmail($existingEmail);
        if ($existingNormalized !== '' && $existingNormalized === $normalized) {
            $this->reservedEmails[$existingNormalized] = true;

            return $existingNormalized;
        }

        $counter = 0;
        do {
            $suffix = $counter > 0 ? '.' . $counter : '';
            $candidate = $localPart . $suffix . '@' . $domain;

            $existsInImport = isset($this->reservedEmails[$candidate]);
            $existsInDropdown = DropdownOption::query()
                ->where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)
                ->where('metadata->email', $candidate)
                ->exists();
            $existsInUsers = User::query()->where('email', $candidate)->exists();

            $counter++;
        } while ($existsInImport || $existsInDropdown || $existsInUsers);

        $this->reservedEmails[$candidate] = true;

        return $candidate;
    }
}
