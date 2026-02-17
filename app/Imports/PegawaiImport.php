<?php

namespace App\Imports;

use App\Models\Pegawai;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Collection;

class PegawaiImport
{
    protected array $errors = [];
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
            $this->errors[] = 'Header tidak ditemukan. Pastikan file memiliki kolom: Nama, NIP, Jabatan, Unit Kerja, Nomor HP.';
            return $this;
        }

        // Map column letters to field names
        $columnMap = [];
        foreach ($headers as $col => $header) {
            $mapped = match ($header) {
                'nama', 'nama lengkap', 'nama_lengkap' => 'nama',
                'nip' => 'nip',
                'jabatan' => 'jabatan',
                'unit kerja', 'unit_kerja' => 'unit_kerja',
                'nomor hp', 'nomor_hp', 'no. hp', 'no hp', 'hp', 'telepon', 'phone' => 'nomor_hp',
                'status', 'is_active', 'aktif' => 'is_active',
                default => null,
            };
            if ($mapped) {
                $columnMap[$col] = $mapped;
            }
        }

        if (!isset(array_flip(array_values($columnMap))['nama']) || !isset(array_flip(array_values($columnMap))['nip'])) {
            $this->errors[] = 'Kolom "Nama" dan "NIP" wajib ada di file Excel.';
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
            if (empty($data['nama']) && empty($data['nip'])) {
                continue;
            }

            // Validate required fields
            if (empty($data['nama'])) {
                $this->errors[] = "Baris {$rowNumber}: Nama tidak boleh kosong.";
                $this->skipped++;
                continue;
            }

            if (empty($data['nip'])) {
                $this->errors[] = "Baris {$rowNumber}: NIP tidak boleh kosong.";
                $this->skipped++;
                continue;
            }

            // Clean NIP - remove spaces and non-numeric
            $data['nip'] = preg_replace('/[^0-9]/', '', $data['nip']);

            if (strlen($data['nip']) !== 18) {
                $this->errors[] = "Baris {$rowNumber}: NIP '{$data['nip']}' harus tepat 18 digit (saat ini " . strlen($data['nip']) . " digit).";
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

            // Upsert by NIP
            try {
                $existing = Pegawai::where('nip', $data['nip'])->first();

                if ($existing) {
                    $existing->update([
                        'nama' => $data['nama'],
                        'jabatan' => $data['jabatan'] ?? $existing->jabatan,
                        'unit_kerja' => $data['unit_kerja'] ?? $existing->unit_kerja,
                        'nomor_hp' => !empty($data['nomor_hp']) ? $data['nomor_hp'] : $existing->nomor_hp,
                        'is_active' => $data['is_active'],
                    ]);
                    $this->updated++;
                } else {
                    Pegawai::create([
                        'nama' => $data['nama'],
                        'nip' => $data['nip'],
                        'jabatan' => $data['jabatan'] ?? null,
                        'unit_kerja' => $data['unit_kerja'] ?? null,
                        'nomor_hp' => !empty($data['nomor_hp']) ? $data['nomor_hp'] : null,
                        'is_active' => $data['is_active'],
                    ]);
                    $this->imported++;
                }
            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowNumber}: Gagal menyimpan data — " . $e->getMessage();
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

    public function getSummary(): string
    {
        $parts = [];
        if ($this->imported > 0) $parts[] = "{$this->imported} data baru ditambahkan";
        if ($this->updated > 0) $parts[] = "{$this->updated} data diperbarui";
        if ($this->skipped > 0) $parts[] = "{$this->skipped} data dilewati";

        return implode(', ', $parts) ?: 'Tidak ada data yang diproses';
    }
}
