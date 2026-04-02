<?php

namespace App\Filament\Piket\Pages;

use App\Models\BukuTamu;
use Filament\Panel;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Livewire\WithPagination;

class ViewRiwayatTamu extends Page
{
    use WithPagination;
    protected string $view = 'filament.piket.pages.view-riwayat-tamu';
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
        return url('/piket/view-riwayat-tamu/' . ($parameters['nik'] ?? ''));
    }

    public function mount(): void
    {
        $perPage = (int) request()->query('per_page', 5);
        $this->kunjunganPerPage = in_array($perPage, [3, 5, 10], true) ? $perPage : 5;

        $tamu = BukuTamu::where('nik', $this->nik)->first();

        if (!$tamu) {
            abort(404);
        }
    }

    public function getTamu()
    {
        return BukuTamu::where('nik', $this->nik)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getAllKunjungan()
    {
        return BukuTamu::where('nik', $this->nik)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getKunjunganPaginated()
    {
        return BukuTamu::where('nik', $this->nik)
            ->orderBy('created_at', 'desc')
            ->paginate($this->kunjunganPerPage);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
