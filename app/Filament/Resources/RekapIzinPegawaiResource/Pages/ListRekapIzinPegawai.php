<?php

namespace App\Filament\Resources\RekapIzinPegawaiResource\Pages;

use App\Filament\Resources\RekapIzinPegawaiResource;
use App\Models\PegawaiIzin;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ListRekapIzinPegawai extends ListRecords
{
  protected static string $resource = RekapIzinPegawaiResource::class;

  public function getTableRecordKey(Model $record): string
  {
    return $record->nip;
  }

  public function getTabs(): array
  {
    return [
      'semua' => Tab::make('Semua')
        ->icon('heroicon-o-squares-2x2')
        ->badge(PegawaiIzin::distinct('nip')->count('nip')),
      'sering_izin' => Tab::make('Sering Izin (≥3x)')
        ->icon('heroicon-o-exclamation-triangle')
        ->badge(
          PegawaiIzin::select('nip')
            ->groupBy('nip')
            ->havingRaw('COUNT(*) >= 3')
            ->get()
            ->count()
        )
        ->badgeColor('danger')
        ->modifyQueryUsing(fn(Builder $query) => $query->having('total_izin', '>=', 3)),
      'sedang_izin' => Tab::make('Sedang Izin')
        ->icon('heroicon-o-clock')
        ->badge(
          PegawaiIzin::where('status', 'aktif')
            ->where('tanggal_selesai', '>=', now()->toDateString())
            ->distinct('nip')
            ->count('nip')
        )
        ->badgeColor('warning')
        ->modifyQueryUsing(fn(Builder $query) => $query->having('sedang_izin', '>', 0)),
    ];
  }
}
