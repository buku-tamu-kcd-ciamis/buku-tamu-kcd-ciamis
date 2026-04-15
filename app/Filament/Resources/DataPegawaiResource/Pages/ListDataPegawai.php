<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages;

use App\Exports\PegawaiTemplateExport;
use App\Filament\Resources\DataPegawaiResource;
use App\Imports\PegawaiImport;
use App\Services\StaffDitujuSyncService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Throwable;

class ListDataPegawai extends ListRecords
{
  protected static string $resource = DataPegawaiResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('importPegawaiExcel')
        ->label('Import')
        ->icon('heroicon-o-arrow-up-tray')
        ->color('gray')
        ->modalHeading('Import Data Pegawai dari Excel')
        ->modalDescription('Unggah file Excel (.xlsx/.xls) untuk menambah atau memperbarui data pegawai.')
        ->schema([
          FileUpload::make('file')
            ->label('File Excel')
            ->disk('local')
            ->directory('imports/pegawai')
            ->preserveFilenames()
            ->acceptedFileTypes([
              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
              'application/vnd.ms-excel',
            ])
            ->maxSize(5120)
            ->required(),
        ])
        ->action(function (array $data): void {
          $uploadedState = $data['file'] ?? null;
          $relativePath = is_array($uploadedState)
            ? (string) ($uploadedState[0] ?? '')
            : (string) $uploadedState;

          if ($relativePath === '' || !Storage::disk('local')->exists($relativePath)) {
            Notification::make()
              ->danger()
              ->title('File tidak ditemukan')
              ->body('Silakan unggah ulang file Excel untuk proses import.')
              ->send();

            return;
          }

          try {
            $importer = (new PegawaiImport())->import(Storage::disk('local')->path($relativePath));
            $syncResult = app(StaffDitujuSyncService::class)->syncNow();
            $syncSummary = $syncResult['has_staff']
              ? ($syncResult['saved'] . ' opsi staff aktif tersinkron otomatis')
              : ('tidak ada pegawai aktif, ' . $syncResult['removed'] . ' opsi staff lama dibersihkan');

            if ($importer->hasErrors()) {
              $errors = array_slice($importer->getErrors(), 0, 3);

              Notification::make()
                ->warning()
                ->title('Import selesai dengan catatan')
                ->body($importer->getSummary() . '. Sinkron staff otomatis: ' . $syncSummary . '. Contoh error: ' . implode(' | ', $errors))
                ->send();
            } else {
              Notification::make()
                ->success()
                ->title('Import berhasil')
                ->body($importer->getSummary() . '. Sinkron staff otomatis: ' . $syncSummary)
                ->send();
            }
          } catch (Throwable $exception) {
            Log::error('Import Data Pegawai gagal.', [
              'exception' => $exception,
              'message' => $exception->getMessage(),
            ]);

            Notification::make()
              ->danger()
              ->title('Import gagal')
              ->body('Gagal import. Periksa format file dan gunakan template yang disediakan.')
              ->send();
          } finally {
            Storage::disk('local')->delete($relativePath);
          }
        }),
      Actions\Action::make('downloadTemplatePegawai')
        ->label('Template')
        ->icon('heroicon-o-document-arrow-down')
        ->color('gray')
        ->action(fn() => (new PegawaiTemplateExport())->download()),
      Actions\Action::make('exportPegawaiPdf')
        ->label('Export')
        ->icon('heroicon-o-document-arrow-down')
        ->color('gray')
        ->url(route('data-pegawai.print'))
        ->openUrlInNewTab(true),
      Actions\CreateAction::make()
        ->label('Buat')
        ->color('success'),
    ];
  }
}
