<?php

namespace App\Imports;

use App\Models\Pegawai;
use Illuminate\Support\Str;
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

        // Find header row (first row with 'nama' or 'nip')
        $headerRowIndex = null;
        $headers = [];

        foreach ($rows as $index => $row) {
            $normalized = array_map(fn($v) => strtolower(trim((string)($v ?? ''))), $row);
            if (in_array('nama', $normalized) || in_array('nama lengkap', $normalized) || in_array('nip', $normalized)) {
                $headerRowIndex = $index;
                $headers = $normalized;
                break;
            }
        }

        if ($headerRowIndex === null) {
            $this->errors[] = 'Header tidak ditemukan. Pastikan file memiliki kolom: Nama, NIP, Email, Jabatan, Unit Kerja, Nomor HP.';
            return $this;
        }

        // Map column letters to field names
        $columnMap = [];
        foreach ($headers as $col => $header) {
            $mapped = match ($header) {
                'nama', 'nama lengkap', 'nama_lengkap' => 'nama',
                'nip' => 'nip',
                'email', 'e-mail', 'email login' => 'email',
                'jabatan' => 'jabatan',
                'unit kerja', 'unit_kerja' => 'unit_kerja',
                'nomor hp', 'nomor_hp', 'no. hp', 'no hp', 'hp', 'telepon', 'phone' => 'nomor_hp',
                'status', 'is_active', 'aktif' => 'is_active',
                'role', 'role user', 'role_user', 'role pegawai', 'role_pegawai' => 'role_user_name',
                default => null,
            };
            if ($mapped) {
                $columnMap[$col] = $mapped;
            }
        }

        if (!isset(array_flip(array_values($columnMap))['nama'])) {
            $this->errors[] = 'Kolom "Nama" wajib ada di file Excel.';
            return $this;
        }

        // Process data rows
        $rowNumber = 0;
        foreach ($rows as $index => $row) {
            if ($index <= $headerRowIndex) continue;

            $rowNumber++;
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
        return strtolower(trim((string) $email));
    }

    protected function resolveEmailForRow(string $importedEmail, string $nama, ?int $ignorePegawaiId = null, ?string $existingEmail = null): string
    {
        if ($importedEmail !== '') {
            return $this->ensureUniqueEmail($importedEmail, $ignorePegawaiId);
        }

        $current = $this->normalizeEmail($existingEmail);
        if ($current !== '') {
            $this->reservedEmails[$current] = true;
            return $current;
        }

        $localPart = Str::slug($nama, '.');
        $localPart = $localPart !== '' ? $localPart : 'pegawai';

        return $this->ensureUniqueEmail($localPart . '@cadisdik13.local', $ignorePegawaiId);
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
