<?php

namespace App\Services;

use App\Models\DropdownOption;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;

class StaffDitujuSyncService
{
    private static bool $isDeferred = false;
    private static bool $hasSyncedThisRequest = false;

    /**
     * Defer synchronization once per request lifecycle.
     */
    public function deferSync(): void
    {
        if (self::$isDeferred) {
            return;
        }

        self::$isDeferred = true;

        app()->terminating(function (): void {
            self::$isDeferred = false;

            if (self::$hasSyncedThisRequest) {
                return;
            }

            $this->syncNow();
        });
    }

    /**
     * Synchronize active pegawai into dropdown_options category staff_dituju.
     *
     * @return array{has_staff: bool, processed: int, saved: int, removed: int}
     */
    public function syncNow(): array
    {
        self::$hasSyncedThisRequest = true;

        $category = DropdownOption::CATEGORY_STAFF_DITUJU;

        $pegawaiAktif = Pegawai::query()
            ->where('is_active', true)
            ->select(['id', 'nama', 'jabatan'])
            ->get()
            ->filter(fn(Pegawai $pegawai): bool => filled(trim((string) $pegawai->nama)))
            ->values();

        if ($pegawaiAktif->isEmpty()) {
            $removedCount = DropdownOption::query()
                ->where('category', $category)
                ->delete();

            DropdownOption::clearCache($category);

            return [
                'has_staff' => false,
                'processed' => 0,
                'saved' => 0,
                'removed' => $removedCount,
            ];
        }

        $duplicateCounts = $pegawaiAktif
            ->map(function (Pegawai $pegawai): string {
                $nama = trim((string) $pegawai->nama);
                $jabatan = trim((string) ($pegawai->jabatan ?? ''));

                return mb_strtolower($nama . '|' . $jabatan);
            })
            ->countBy();

        $staffOptions = $pegawaiAktif
            ->map(function (Pegawai $pegawai) use ($duplicateCounts): array {
                $nama = trim((string) $pegawai->nama);
                $jabatan = trim((string) ($pegawai->jabatan ?? ''));
                $baseDisplay = $jabatan !== '' ? ($nama . ' — ' . $jabatan) : $nama;
                $duplicateKey = mb_strtolower($nama . '|' . $jabatan);

                $display = ($duplicateCounts[$duplicateKey] ?? 0) > 1
                    ? ($baseDisplay . ' #' . $pegawai->id)
                    : $baseDisplay;

                return [
                    'value' => $display,
                    'label' => $display,
                ];
            })
            ->unique('value')
            ->sortBy('value', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $keepValues = [];

        DB::transaction(function () use ($staffOptions, $category, &$keepValues): void {
            foreach ($staffOptions as $index => $option) {
                DropdownOption::updateOrCreate(
                    ['category' => $category, 'value' => $option['value']],
                    [
                        'label' => $option['label'],
                        'metadata' => null,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ],
                );

                $keepValues[] = $option['value'];
            }
        });

        $removedCount = DropdownOption::query()
            ->where('category', $category)
            ->whereNotIn('value', $keepValues)
            ->delete();

        DropdownOption::clearCache($category);

        $savedCount = DropdownOption::query()
            ->where('category', $category)
            ->count();

        return [
            'has_staff' => true,
            'processed' => count($keepValues),
            'saved' => $savedCount,
            'removed' => $removedCount,
        ];
    }
}
