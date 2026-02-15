<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->form([
                    Select::make('user_id')
                        ->label('Filter User')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Semua User'),

                    Select::make('log_name')
                        ->label('Modul Aktivitas')
                        ->options(ActivityLogResource::getLogNameLabels())
                        ->placeholder('Semua Modul'),

                    Select::make('event')
                        ->label('Tipe Event')
                        ->options([
                            'created' => 'Created',
                            'updated' => 'Updated',
                            'deleted' => 'Deleted',
                            'login' => 'Login',
                            'logout' => 'Logout',
                            'print' => 'Print',
                        ])
                        ->placeholder('Semua Event'),

                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    Select::make('limit')
                        ->label('Jumlah Data')
                        ->options([
                            10 => '10 Log Terakhir',
                            25 => '25 Log Terakhir',
                            50 => '50 Log Terakhir',
                            100 => '100 Log Terakhir',
                            250 => '250 Log Terakhir',
                            500 => '500 Log Terakhir',
                        ])
                        ->default(50)
                        ->required(),
                ])
                ->url(function (array $data) {
                    $queryParams = array_filter([
                        'user_id' => $data['user_id'] ?? null,
                        'log_name' => $data['log_name'] ?? null,
                        'event' => $data['event'] ?? null,
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'limit' => $data['limit'] ?? 50,
                    ]);

                    return route('activity-logs.print', $queryParams);
                })
                ->openUrlInNewTab()
                ->modalHeading('Print Log Aktivitas')
                ->modalSubmitActionLabel('Print')
                ->modalWidth('lg'),

            Actions\Action::make('backupExcel')
                ->label('Backup Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-arrow-down-tray')
                ->modalHeading('Backup Log Aktivitas ke Excel')
                ->modalDescription('Seluruh data log aktivitas akan diunduh dalam format Excel (.xlsx). Pastikan koneksi internet stabil.')
                ->modalSubmitActionLabel('Download Backup')
                ->action(function () {
                    return $this->exportToExcel();
                }),

            Actions\Action::make('backupAndClear')
                ->label('Backup & Hapus Semua')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalHeading('Backup & Hapus Semua Log')
                ->modalDescription('PERHATIAN! Tindakan ini akan: 1) Mengunduh backup Excel seluruh log aktivitas, 2) Menghapus SEMUA data log dari database. Tindakan ini TIDAK DAPAT DIBATALKAN. Pastikan file backup berhasil terunduh sebelum melanjutkan.')
                ->modalSubmitActionLabel('Ya, Backup & Hapus Semua')
                ->action(function () {
                    $totalLogs = Activity::count();

                    if ($totalLogs === 0) {
                        Notification::make()
                            ->warning()
                            ->title('Tidak ada data log!')
                            ->body('Belum ada data log aktivitas yang perlu di-backup.')
                            ->send();
                        return;
                    }

                    // Generate backup first
                    $response = $this->exportToExcel();

                    // Then clear all logs
                    Activity::truncate();

                    // Log this action
                    activity('pengaturan')
                        ->causedBy(Auth::user())
                        ->event('deleted')
                        ->withProperties(['jumlah_dihapus' => $totalLogs])
                        ->log('Backup & hapus seluruh log aktivitas (' . $totalLogs . ' data)');

                    Notification::make()
                        ->success()
                        ->title('Backup & hapus berhasil!')
                        ->body($totalLogs . ' data log aktivitas telah di-backup dan dihapus.')
                        ->send();

                    return $response;
                }),
        ];
    }

    protected function exportToExcel()
    {
        $logs = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($logs->isEmpty()) {
            Notification::make()
                ->warning()
                ->title('Tidak ada data!')
                ->body('Belum ada data log aktivitas untuk di-backup.')
                ->send();
            return;
        }

        $fileName = 'backup-log-aktivitas-' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $filePath = storage_path('app/' . $fileName);

        $options = new Options();
        $writer = new Writer($options);
        $writer->openToFile($filePath);

        // Header style
        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(30, 64, 175));

        // Header row
        $headerRow = Row::fromValues([
            'No',
            'Waktu',
            'User',
            'Modul',
            'Aksi',
            'Aktivitas',
            'Model',
            'ID Subject',
            'Properties',
        ], $headerStyle);
        $writer->addRow($headerRow);

        // Data rows
        $no = 1;
        foreach ($logs as $log) {
            $dataRow = Row::fromValues([
                $no++,
                $log->created_at->format('d/m/Y H:i:s'),
                $log->causer?->name ?? 'System',
                ActivityLogResource::getLogNameLabel($log->log_name ?? ''),
                match ($log->event) {
                    'created' => 'Dibuat',
                    'updated' => 'Diubah',
                    'deleted' => 'Dihapus',
                    default => ucfirst($log->event ?? '-'),
                },
                $log->description ?? '-',
                $log->subject_type ? class_basename($log->subject_type) : '-',
                $log->subject_id ?? '-',
                $log->properties ? json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-',
            ]);
            $writer->addRow($dataRow);
        }

        $writer->close();

        // Log this backup action
        activity('cetak')
            ->causedBy(Auth::user())
            ->event('created')
            ->withProperties([
                'jumlah' => $logs->count(),
                'tipe' => 'backup_excel',
                'file' => $fileName,
            ])
            ->log('Backup log aktivitas ke Excel (' . $logs->count() . ' data)');

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }
}
