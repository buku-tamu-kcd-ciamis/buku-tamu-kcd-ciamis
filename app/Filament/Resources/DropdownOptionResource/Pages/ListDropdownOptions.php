<?php

namespace App\Filament\Resources\DropdownOptionResource\Pages;

use App\Filament\Resources\DropdownOptionResource;
use App\Models\DropdownOption;
use Filament\Actions;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListDropdownOptions extends ListRecords
{
  protected static string $resource = DropdownOptionResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->label('Tambahkan Opsi Dropdown')
        ->color('info'),
    ];
  }

  public function getTabs(): array
  {
    return [
      'semua' => Tab::make('Semua')
        ->icon('heroicon-o-squares-2x2')
        ->badge(DropdownOption::count())
        ->badgeColor('gray'),
      'jenis_id' => Tab::make('Jenis ID')
        ->icon('heroicon-o-identification')
        ->badge(DropdownOption::where('category', DropdownOption::CATEGORY_JENIS_ID)->count())
        ->badgeColor('info')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('category', DropdownOption::CATEGORY_JENIS_ID)),
      'keperluan' => Tab::make('Keperluan')
        ->icon('heroicon-o-clipboard-document-list')
        ->badge(DropdownOption::where('category', DropdownOption::CATEGORY_KEPERLUAN)->count())
        ->badgeColor('success')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('category', DropdownOption::CATEGORY_KEPERLUAN)),
      'kabupaten_kota' => Tab::make('Kabupaten/Kota')
        ->icon('heroicon-o-map-pin')
        ->badge(DropdownOption::where('category', DropdownOption::CATEGORY_KABUPATEN_KOTA)->count())
        ->badgeColor('warning')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('category', DropdownOption::CATEGORY_KABUPATEN_KOTA)),
      'bagian_dituju' => Tab::make('Bagian Dituju')
        ->icon('heroicon-o-building-office')
        ->badge(DropdownOption::where('category', DropdownOption::CATEGORY_BAGIAN_DITUJU)->count())
        ->badgeColor('danger')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('category', DropdownOption::CATEGORY_BAGIAN_DITUJU)),
    ];
  }

  public function getFooter(): ?View
  {
    return view('filament.resources.dropdown-option-resource.pages.list-dropdown-options-footer');
  }
}
