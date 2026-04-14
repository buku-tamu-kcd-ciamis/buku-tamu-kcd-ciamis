<?php

namespace App\Imports;

use App\Models\DropdownOption;
use App\Models\User;
use Illuminate\Support\Str;
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
            $this->errors[] = 'Header tidak ditemukan. Pastikan file memiliki kolom: Nama Lengkap dan Email (opsional).';
            return $this;
        }

        // Map columns
        $columnMap = [];
        foreach ($headers as $col => $header) {
            $mapped = match ($header) {
                'nama', 'nama lengkap', 'nama_lengkap' => 'label',
                'email', 'e-mail', 'email login' => 'email',
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

            // Upsert by label + category
            try {
                $existing = DropdownOption::where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)
                    ->where('label', $data['label'])
                    ->first();

                $resolvedEmail = $this->resolveEmailForRow(
                    $data['email'],
                    $data['label'],
                    is_array($existing?->metadata) ? ($existing->metadata['email'] ?? null) : null
                );

                $metadata = is_array($existing?->metadata) ? $existing->metadata : [];
                $metadata['email'] = $resolvedEmail;

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

                $this->processedRows[$resolvedEmail] = [
                    'label' => $data['label'],
                    'email' => $resolvedEmail,
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
        return strtolower(trim((string) $email));
    }

    protected function resolveEmailForRow(string $importedEmail, string $label, ?string $existingEmail = null): string
    {
        if ($importedEmail !== '') {
            return $this->ensureUniqueEmail($importedEmail, $existingEmail);
        }

        $current = $this->normalizeEmail($existingEmail);
        if ($current !== '') {
            $this->reservedEmails[$current] = true;

            return $current;
        }

        $localPart = Str::slug($label, '.');
        $localPart = $localPart !== '' ? $localPart : 'piket';

        return $this->ensureUniqueEmail($localPart . '@cadisdik13.local');
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
