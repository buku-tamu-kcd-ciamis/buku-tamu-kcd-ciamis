<?php

namespace App\Filament\Staff\Resources\BukuTamuResource\Pages;

use App\Filament\Staff\Resources\BukuTamuResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\BukuTamu;

class ListBukuTamu extends ListRecords
{
    protected static string $resource = BukuTamuResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $hasCustomDate = !empty($this->tableFilters['tanggal']['tanggal'] ?? null);

        $baseQuery = BukuTamuResource::applyCurrentStaffScope(BukuTamu::query())
            ->where(function ($q) {
                $q->where('keperluan', 'not like', '%berkas%')
                  ->where('keperluan', 'not like', '%surat%')
                  ->where('keperluan', 'not like', '%dokumen%')
                  ->where('keperluan', 'not like', '%legalisir%');
            });

        return [
            'hari_ini' => Tab::make('Hari Ini')
                ->badge((clone $baseQuery)->whereDate('created_at', now()->toDateString())->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $hasCustomDate ? $query : $query->whereDate('created_at', now()->toDateString())),
            'kemarin' => Tab::make('Kemarin')
                ->badge((clone $baseQuery)->whereDate('created_at', now()->subDay()->toDateString())->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $hasCustomDate ? $query : $query->whereDate('created_at', now()->subDay()->toDateString())),
            'semua' => Tab::make('Semua Data')
                ->badge((clone $baseQuery)->count())
                ->badgeColor('gray'),
        ];
    }

    public function getFooter(): ?View
    {
        return view('filament.staff.pages.kunjungan-footer');
    }
}
