<?php

namespace App\Filament\Piket\Pages;

use App\Models\BukuTamu;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Support\Enums\MaxWidth;
use Livewire\WithPagination;

class ViewRiwayatTamu extends Page
{
    use WithPagination;
    protected static string $view = 'filament.piket.pages.view-riwayat-tamu';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Detail Riwayat Pengunjung';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public ?string $nik = null;

    public static function getRoutePath(): string
    {
        return 'view-riwayat-tamu/{nik}';
    }

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null): string
    {
        return url('/piket/view-riwayat-tamu/' . ($parameters['nik'] ?? ''));
    }

    public function mount(): void
    {
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
            ->paginate(5);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(route('filament.piket.pages.riwayat-tamu')),
        ];
    }
}
