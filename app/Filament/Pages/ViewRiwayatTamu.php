<?php

namespace App\Filament\Pages;

use App\Models\BukuTamu;
use Filament\Panel;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

class ViewRiwayatTamu extends Page
{
    use WithPagination;
    protected string $view = 'filament.pages.view-riwayat-tamu';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Detail Riwayat Pengunjung';

    public function getMaxContentWidth(): string | Width | null
    {
        return Width::Full;
    }

    public ?string $nik = null;
    public int $kunjunganPerPage = 5;

    public static function getRoutePath(Panel $panel): string
    {
        return 'view-riwayat-tamu/{nik}';
    }

    public static function getUrl(
        array $parameters = [],
        bool $isAbsolute = true,
        ?string $panel = null,
        ?\Illuminate\Database\Eloquent\Model $tenant = null,
        bool $shouldGuessMissingParameters = false,
        ?string $configuration = null,
    ): string {
        return url('/admin/view-riwayat-tamu/' . ($parameters['nik'] ?? ''));
    }

    public function mount(): void
    {
        $perPage = (int) request()->query('per_page', 5);
        $this->kunjunganPerPage = in_array($perPage, [3, 5, 10], true) ? $perPage : 5;

        $tamu = $this->riwayatQuery()->first();

        if (!$tamu) {
            abort(404);
        }
    }

    protected function riwayatQuery(): Builder
    {
        return BukuTamu::query()
            ->where('nik', $this->nik)
            ->orderByDesc('created_at')
            ->orderByDesc('updated_at');
    }

    public function getTamu()
    {
        return $this->riwayatQuery()->first();
    }

    public function getAllKunjungan()
    {
        return $this->riwayatQuery()->get();
    }

    public function getKunjunganPaginated()
    {
        return $this->riwayatQuery()->paginate($this->kunjunganPerPage);
    }

    public function getPageClasses(): array
    {
        return ['fi-page-view-riwayat-tamu'];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
