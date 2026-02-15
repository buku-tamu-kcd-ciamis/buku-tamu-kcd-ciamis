<?php

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use App\Models\Faq;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFaqs extends ListRecords
{
  protected static string $resource = FaqResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->label('Tambah FAQ'),
    ];
  }

  public function getTabs(): array
  {
    return [
      'semua' => Tab::make('Semua')
        ->icon('heroicon-o-squares-2x2')
        ->badge(Faq::count()),
      'semua_panel' => Tab::make('Semua Panel')
        ->icon('heroicon-o-globe-alt')
        ->badge(Faq::where('target', 'semua')->count())
        ->badgeColor('primary')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('target', 'semua')),
      'admin' => Tab::make('Panel Admin (Ketua KCD)')
        ->icon('heroicon-o-shield-check')
        ->badge(Faq::where('target', 'admin')->count())
        ->badgeColor('warning')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('target', 'admin')),
      'piket' => Tab::make('Panel Piket')
        ->icon('heroicon-o-clipboard-document-check')
        ->badge(Faq::where('target', 'piket')->count())
        ->badgeColor('success')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('target', 'piket')),
    ];
  }
}
