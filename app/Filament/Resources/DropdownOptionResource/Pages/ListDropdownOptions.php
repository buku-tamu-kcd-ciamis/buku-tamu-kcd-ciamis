<?php

namespace App\Filament\Resources\DropdownOptionResource\Pages;

use App\Filament\Resources\DropdownOptionResource;
use App\Models\DropdownOption;
use App\Models\Pegawai;
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
      Actions\Action::make('sync_staff_dituju')
        ->label('Sinkronkan Staff Dituju')
        ->icon('heroicon-o-arrow-path')
        ->color('success')
        ->requiresConfirmation()
        ->modalHeading('Sinkronkan Staff Yang Dituju')
        ->modalDescription('Data kategori Staff Yang Dituju akan disesuaikan dengan data pegawai aktif.')
        ->modalSubmitActionLabel('Sinkronkan')
        ->action(function (): void {
          $staffOptions = Pegawai::query()
            ->where('is_active', true)
            ->select(['nama', 'jabatan'])
            ->get()
            ->filter(fn(Pegawai $pegawai): bool => filled(trim((string) $pegawai->nama)))
            ->map(function (Pegawai $pegawai): array {
              $nama = trim((string) $pegawai->nama);
              $jabatan = trim((string) ($pegawai->jabatan ?? ''));

              return [
                'value' => $nama,
                'label' => $jabatan !== '' ? ($nama . ' — ' . $jabatan) : $nama,
              ];
            })
            ->unique('value')
            ->sortBy('value', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

          if ($staffOptions->isEmpty()) {
            Notification::make()
              ->warning()
              ->title('Sinkronisasi dibatalkan')
              ->body('Belum ada data staff aktif yang bisa disinkronkan.')
              ->send();

            return;
          }

          $category = DropdownOption::CATEGORY_STAFF_DITUJU;
          $keepValues = [];

          foreach ($staffOptions as $index => $option) {
            DropdownOption::updateOrCreate(
              ['category' => $category, 'value' => $option['value']],
              [
                'label' => $option['label'],
                'metadata' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
              ],
            );

            $keepValues[] = $option['value'];
          }

          DropdownOption::query()
            ->where('category', $category)
            ->whereNotIn('value', $keepValues)
            ->delete();

          DropdownOption::clearCache($category);

          Notification::make()
            ->success()
            ->title('Sinkronisasi berhasil')
            ->body(count($keepValues) . ' staff berhasil disinkronkan ke kategori Staff Yang Dituju.')
            ->send();
        }),
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
      'staff_dituju' => Tab::make('Staff Yang Dituju')
        ->icon('heroicon-o-building-office')
        ->badge(DropdownOption::where('category', DropdownOption::CATEGORY_STAFF_DITUJU)->count())
        ->badgeColor('danger')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('category', DropdownOption::CATEGORY_STAFF_DITUJU)),
    ];
  }

  public function getFooter(): ?View
  {
    return view('filament.resources.dropdown-option-resource.pages.list-dropdown-options-footer');
  }
}
