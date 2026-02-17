<?php

namespace App\Imports;

use App\Models\DropdownOption;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PegawaiPiketImport
{
    protected array $errors = [];
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

        // Find header row
        $headerRowIndex = null;
        $headers = [];

        foreach ($rows as $index => $row) {
            $normalized = array_map(fn($v) => strtolower(trim((string)($v ?? ''))), $row);
            if (in_array('nama', $normalized) || in_array('nama lengkap', $normalized)) {
                $headerRowIndex = $index;
                $headers = $normalized;
                break;
            }
        }

        if ($headerRowIndex === null) {
            $this->errors[] = 'Header tidak ditemukan. Pastikan file memiliki kolom: Nama Lengkap.';
            return $this;
        }

        // Map columns
        $columnMap = [];
        foreach ($headers as $col => $header) {
            $mapped = match ($header) {
                'nama', 'nama lengkap', 'nama_lengkap' => 'label',
                'nilai', 'value', 'id internal', 'id_internal', 'kode' => 'value',
                'status', 'aktif', 'is_active' => 'is_active',
                default => null,
            };
            if ($mapped) {
                $columnMap[$col] = $mapped;
            }
        }

        if (!isset(array_flip(array_values($columnMap))['label'])) {
            $this->errors[] = 'Kolom "Nama Lengkap" wajib ada di file Excel.';
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
            if (empty($data['label'])) {
                continue;
            }

            // Auto-fill value from label if not provided
            if (empty($data['value'] ?? '')) {
                $data['value'] = $data['label'];
            }

            // Handle is_active
            if (isset($data['is_active']) && $data['is_active'] !== '') {
                $val = strtolower($data['is_active']);
                $data['is_active'] = in_array($val, ['ya', 'yes', '1', 'aktif', 'true', 'active']);
            } else {
                $data['is_active'] = true;
            }

            // Upsert by label + category
            try {
                $existing = DropdownOption::where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)
                    ->where('label', $data['label'])
                    ->first();

                $nextSort = DropdownOption::where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)->max('sort_order') ?? 0;

                if ($existing) {
                    $existing->update([
                        'value' => $data['value'],
                        'is_active' => $data['is_active'],
                    ]);
                    $this->updated++;
                } else {
                    DropdownOption::create([
                        'category' => DropdownOption::CATEGORY_PEGAWAI_PIKET,
                        'label' => $data['label'],
                        'value' => $data['value'],
                        'sort_order' => $nextSort + 1,
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
