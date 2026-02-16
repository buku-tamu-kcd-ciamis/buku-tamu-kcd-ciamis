<?php

namespace App\Filament\Resources\VerifikasiProfilResource\Pages;

use App\Filament\Resources\VerifikasiProfilResource;
use App\Models\ProfileChangeRequest;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListVerifikasiProfil extends ListRecords
{
  protected static string $resource = VerifikasiProfilResource::class;

  public function getTabs(): array
  {
    return [
      'semua' => Tab::make('Semua')
        ->icon('heroicon-o-squares-2x2')
        ->badge(ProfileChangeRequest::count()),
      'pending' => Tab::make('Menunggu Verifikasi')
        ->icon('heroicon-o-clock')
        ->badge(ProfileChangeRequest::where('status', ProfileChangeRequest::STATUS_PENDING)->count())
        ->badgeColor('warning')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('status', ProfileChangeRequest::STATUS_PENDING)),
      'approved' => Tab::make('Disetujui')
        ->icon('heroicon-o-check-circle')
        ->badge(ProfileChangeRequest::where('status', ProfileChangeRequest::STATUS_APPROVED)->count())
        ->badgeColor('success')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('status', ProfileChangeRequest::STATUS_APPROVED)),
      'rejected' => Tab::make('Ditolak')
        ->icon('heroicon-o-x-circle')
        ->badge(ProfileChangeRequest::where('status', ProfileChangeRequest::STATUS_REJECTED)->count())
        ->badgeColor('danger')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('status', ProfileChangeRequest::STATUS_REJECTED)),
    ];
  }
}
