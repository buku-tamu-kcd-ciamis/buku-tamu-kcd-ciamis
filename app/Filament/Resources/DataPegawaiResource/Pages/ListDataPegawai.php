<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages;

use App\Filament\Resources\DataPegawaiResource;
use App\Exports\PegawaiTemplateExport;
use App\Imports\PegawaiImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListDataPegawai extends ListRecords
{
  protected static string $resource = DataPegawaiResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->label('Tambah Pegawai')
        ->icon('heroicon-o-plus'),

      Actions\Action::make('import')
        ->label('Import Excel')
        ->icon('heroicon-o-arrow-up-tray')
        ->color('warning')
        ->form([
          FileUpload::make('file')
            ->label('File Excel (.xlsx)')
            ->acceptedFileTypes([
              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
              'application/vnd.ms-excel',
            ])
            ->maxSize(5120) // 5MB
            ->required()
            ->disk('local')
            ->directory('imports')
            ->helperText('Upload file Excel dengan format sesuai template. Maks 5MB.'),
        ])
        ->modalHeading('Import Data Pegawai')
        ->modalDescription('Upload file Excel untuk mengimpor data pegawai. Data dengan NIP yang sudah ada akan diperbarui.')
        ->modalSubmitActionLabel('Import')
        ->modalIcon('heroicon-o-arrow-up-tray')
        ->action(function (array $data) {
          $filePath = storage_path('app/private/' . $data['file']);

          // Fallback: check other possible locations
          if (!file_exists($filePath)) {
            $filePath = storage_path('app/' . $data['file']);
          }

          if (!file_exists($filePath)) {
            Notification::make()
              ->title('File tidak ditemukan')
              ->body('File yang diupload tidak dapat ditemukan di server.')
              ->danger()
              ->send();
            return;
          }

          try {
            $importer = new PegawaiImport();
            $importer->import($filePath);

            // Clean up uploaded file
            @unlink($filePath);

            // Show result notification
            if ($importer->hasErrors() && $importer->getImported() === 0 && $importer->getUpdated() === 0) {
              Notification::make()
                ->title('Import Gagal')
                ->body(implode("\n", array_slice($importer->getErrors(), 0, 5)))
                ->danger()
                ->persistent()
                ->send();
            } elseif ($importer->hasErrors()) {
              Notification::make()
                ->title('Import Selesai (Sebagian)')
                ->body($importer->getSummary() . "\n\nError:\n" . implode("\n", array_slice($importer->getErrors(), 0, 5)))
                ->warning()
                ->persistent()
                ->send();
            } else {
              Notification::make()
                ->title('Import Berhasil')
                ->body($importer->getSummary())
                ->success()
                ->send();
            }
          } catch (\Exception $e) {
            @unlink($filePath);

            Notification::make()
              ->title('Import Gagal')
              ->body('Terjadi kesalahan: ' . $e->getMessage())
              ->danger()
              ->send();
          }
        }),

      Actions\Action::make('downloadTemplate')
        ->label('Download Template')
        ->icon('heroicon-o-arrow-down-tray')
        ->color('gray')
        ->action(function () {
          return (new PegawaiTemplateExport())->download();
        }),

      Actions\Action::make('print')
        ->label('Cetak Laporan')
        ->icon('heroicon-o-printer')
        ->color('success')
        ->url(fn() => route('data-pegawai.print'))
        ->openUrlInNewTab(),
    ];
  }
}
