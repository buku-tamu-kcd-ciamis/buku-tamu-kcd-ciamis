<?php

namespace App\Filament\Resources\PegawaiPiketResource\Pages;

use App\Exports\PegawaiPiketTemplateExport;
use App\Filament\Resources\PegawaiPiketResource;
use App\Imports\PegawaiPiketImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ListPegawaiPikets extends ListRecords
{
    protected static string $resource = PegawaiPiketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambahkan Pegawai Piket')
                ->color('info'),
            Actions\Action::make('importPegawaiPiketExcel')
                ->label('Import Pegawai Piket')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Pegawai Piket dari Excel')
                ->modalDescription('Unggah file Excel (.xlsx/.xls) untuk menambah atau memperbarui data pegawai piket.')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->disk('local')
                        ->directory('imports/pegawai-piket')
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

                    if ($relativePath === '' || ! Storage::disk('local')->exists($relativePath)) {
                        Notification::make()
                            ->danger()
                            ->title('File tidak ditemukan')
                            ->body('Silakan unggah ulang file Excel untuk proses import.')
                            ->send();

                        return;
                    }

                    try {
                        $importer = (new PegawaiPiketImport())->import(Storage::disk('local')->path($relativePath));

                        if ($importer->hasErrors()) {
                            $errors = array_slice($importer->getErrors(), 0, 3);

                            Notification::make()
                                ->warning()
                                ->title('Import selesai dengan catatan')
                                ->body($importer->getSummary() . '. Contoh error: ' . implode(' | ', $errors))
                                ->send();
                        } else {
                            Notification::make()
                                ->success()
                                ->title('Import berhasil')
                                ->body($importer->getSummary())
                                ->send();
                        }
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->danger()
                            ->title('Import gagal')
                            ->body('Terjadi kesalahan saat memproses file Excel.')
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($relativePath);
                    }
                }),
            Actions\Action::make('downloadTemplatePegawaiPiket')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(fn() => (new PegawaiPiketTemplateExport())->download()),
            Actions\Action::make('exportPegawaiPiketPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->url(route('pegawai-piket.print'))
                ->openUrlInNewTab(true),
        ];
    }
}
