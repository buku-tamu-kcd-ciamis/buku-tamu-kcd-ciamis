<?php

namespace App\Filament\Resources\DropdownOptionResource\Pages;

use App\Filament\Resources\DropdownOptionResource;
use App\Models\DropdownOption;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Components\Tab;
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
        ->label('Tambah Opsi'),
      Actions\Action::make('barcode')
        ->label('Ubah Barcode SKM')
        ->icon('heroicon-o-qr-code')
        ->color('info')
        ->form([
          Forms\Components\Section::make('Upload Barcode Survey')
            ->description('Barcode/QR Code ini akan ditampilkan di halaman publik buku tamu untuk Survey Kepuasan Masyarakat.')
            ->schema([
              Forms\Components\FileUpload::make('barcode_skm')
                ->label('Barcode/QR Code')
                ->image()
                ->disk('public')
                ->directory('barcode')
                ->visibility('public')
                ->imageEditor()
                ->imageEditorAspectRatios(['1:1', '4:3', '16:9', null])
                ->maxSize(2048)
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                ->helperText('Upload gambar barcode/QR Code (max 2MB). Format: PNG, JPG, JPEG, atau WebP.')
                ->columnSpanFull(),
              Forms\Components\Placeholder::make('current_barcode')
                ->label('Barcode Saat Ini')
                ->content(function () {
                  $settings = \App\Models\PengaturanKcd::getSettings();
                  return new \Illuminate\Support\HtmlString(
                    '<div class="text-center"><img src="' . $settings->barcode_skm_url . '" alt="Current Barcode" class="max-w-xs mx-auto" style="max-width: 200px;"></div>'
                  );
                })
                ->columnSpanFull(),
            ]),
        ])
        ->action(function (array $data) {
          $settings = \App\Models\PengaturanKcd::getSettings();

          // Delete old barcode if exists and new one is uploaded
          if ($settings->barcode_skm && isset($data['barcode_skm'])) {
            Storage::disk('public')->delete($settings->barcode_skm);
          }

          if (isset($data['barcode_skm'])) {
            $settings->update(['barcode_skm' => $data['barcode_skm']]);
          }

          \Filament\Notifications\Notification::make()
            ->title('Barcode berhasil diperbarui!')
            ->success()
            ->send();
        })
        ->modalHeading('Ubah Barcode Survey Kepuasan Masyarakat')
        ->modalSubmitActionLabel('Simpan')
        ->modalWidth('2xl'),
      Actions\Action::make('print')
        ->label('Cetak Data')
        ->icon('heroicon-o-printer')
        ->color('success')
        ->form([
          Forms\Components\Select::make('category')
            ->label('Kategori')
            ->options([
              'all' => 'Semua Kategori',
              ...DropdownOption::CATEGORY_LABELS
            ])
            ->default('all')
            ->required(),
        ])
        ->action(function (array $data, $livewire) {
          $url = route('dropdown-options.print', ['category' => $data['category']]);
          $livewire->dispatch('open-url-in-new-tab', url: $url);
        })
        ->modalHeading('Cetak Data Dropdown')
        ->modalSubmitActionLabel('Cetak'),
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
      'pegawai_piket' => Tab::make('Pegawai Piket')
        ->icon('heroicon-o-user-group')
        ->badge(DropdownOption::where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)->count())
        ->badgeColor('primary')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)),
    ];
  }

  public function getFooter(): ?View
  {
    return view('filament.resources.dropdown-option-resource.pages.list-dropdown-options-footer');
  }
}
