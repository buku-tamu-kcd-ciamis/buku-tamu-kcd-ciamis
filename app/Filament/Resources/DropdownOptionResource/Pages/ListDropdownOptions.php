<?php

namespace App\Filament\Resources\DropdownOptionResource\Pages;

use App\Filament\Resources\DropdownOptionResource;
use App\Models\DropdownOption;
use App\Models\PengaturanKcd;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ListDropdownOptions extends ListRecords
{
  protected static string $resource = DropdownOptionResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->label('Tambahkan Opsi Dropdown')
        ->color('info'),
      Actions\Action::make('ganti_barcode_skm')
        ->label('Ganti Barcode SKM')
        ->icon('heroicon-o-qr-code')
        ->color('warning')
        ->fillForm(fn(): array => [
          'barcode_skm_current' => PengaturanKcd::getSettings()->barcode_skm,
        ])
        ->schema([
          FileUpload::make('barcode_skm_current')
            ->label('Preview Barcode Saat Ini')
            ->image()
            ->disk('public')
            ->visibility('public')
            ->panelLayout('compact')
            ->imagePreviewHeight('140')
            ->openable()
            ->dehydrated(false)
            ->disabled()
            ->helperText('Preview barcode SKM yang sedang aktif.'),
          FileUpload::make('barcode_skm')
            ->label('Upload Gambar Baru')
            ->image()
            ->disk('public')
            ->directory('barcode-skm')
            ->visibility('public')
            ->panelLayout('compact')
            ->imagePreviewHeight('140')
            ->placeholder('<span class="filepond--label-action">Pilih File SKM Baru</span>')
            ->openable()
            ->imageEditor()
            ->required()
            ->maxSize(2048)
            ->helperText('Pilih file gambar barcode SKM terbaru (maks. 2 MB).'),
        ])
        ->action(function (array $data): void {
          $settings = PengaturanKcd::getSettings();
          $oldBarcode = $settings->barcode_skm;
          $newBarcode = $data['barcode_skm'] ?? null;

          if (
            $oldBarcode
            && $newBarcode
            && $oldBarcode !== $newBarcode
            && Storage::disk('public')->exists($oldBarcode)
          ) {
            Storage::disk('public')->delete($oldBarcode);
          }

          $settings->update([
            'barcode_skm' => $newBarcode,
          ]);

          Notification::make()
            ->success()
            ->title('Barcode SKM berhasil diperbarui')
            ->body('Perubahan langsung diterapkan pada halaman buku tamu.')
            ->send();
        }),
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
