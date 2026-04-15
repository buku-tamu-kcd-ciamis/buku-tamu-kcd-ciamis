<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
            Actions\Action::make('backup')
                ->label('Backup Log Aktivitas')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('activity-logs.backup-download')),

            Actions\Action::make('delete_backup')
                ->label('Hapus & Backup Log Aktivitas')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->hidden(fn(): bool => ! $this->isCurrentUserSuperAdmin())
                ->modalHeading('Hapus & Backup Log Aktivitas')
                ->modalDescription('Sistem akan membuat file backup terlebih dahulu, lalu menghapus seluruh log aktivitas. Tindakan ini memerlukan verifikasi password Super Admin.')
                ->schema([
                    TextInput::make('password')
                        ->label('Password Super Admin')
                        ->password()
                        ->required()
                        ->autocomplete('current-password')
                        ->helperText('Masukkan password akun Super Admin yang sedang login untuk melanjutkan.'),
                ])
                ->action(function (array $data) {
                    $this->verifySuperAdminPassword($data);

                    $backup = $this->exportToExcel(download: false, persistBackup: true);

                    if (! is_array($backup) || empty($backup['file_path']) || ! is_file((string) $backup['file_path'])) {
                        Notification::make()
                            ->danger()
                            ->title('Backup gagal dibuat')
                            ->body('Log aktivitas tidak dihapus karena file backup tidak tersedia.')
                            ->send();

                        return;
                    }

                    $deletedCount = Activity::query()->count();
                    Activity::query()->delete();

                    activity('activity_log')
                        ->causedBy(Filament::auth()->user())
                        ->event('deleted')
                        ->withProperties([
                            'jumlah_dihapus' => $deletedCount,
                            'backup_file' => $backup['file_name'] ?? null,
                            'backup_path' => $backup['relative_path'] ?? null,
                        ])
                        ->log('Hapus & backup log aktivitas (' . $deletedCount . ' data)');

                    Notification::make()
                        ->success()
                        ->title('Log aktivitas berhasil dihapus')
                        ->body('Backup otomatis diunduh dan juga tersimpan di storage/app/' . ($backup['relative_path'] ?? '-'))
                        ->send();

                    return response()->download(
                        (string) $backup['file_path'],
                        (string) $backup['file_name'],
                        [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]
                    );
                }),
        ];
    }

    protected function isCurrentUserSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        return (bool) ($user && $user->hasRole('Super Admin'));
    }

    protected function exportToExcel(bool $download = true, bool $persistBackup = false)
    {
        $baseQuery = Activity::query()
            ->with('causer')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $totalLogs = (clone $baseQuery)->count();

        if ($totalLogs === 0) {
            Notification::make()
                ->warning()
                ->title('Tidak ada data!')
                ->body('Belum ada data log aktivitas untuk di-backup.')
                ->send();
            return;
        }

        $fileName = 'backup-log-aktivitas-' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $relativePath = $persistBackup
            ? 'backups/activity-logs/' . $fileName
            : $fileName;
        $filePath = storage_path('app/' . $relativePath);

        if ($persistBackup) {
            Storage::disk('local')->makeDirectory('backups/activity-logs');
        }

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
        foreach ($baseQuery->cursor() as $log) {
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
            ->causedBy(Filament::auth()->user())
            ->event('created')
            ->withProperties([
                'jumlah' => $totalLogs,
                'tipe' => 'backup_excel',
                'file' => $fileName,
                'path' => $relativePath,
            ])
            ->log('Backup log aktivitas ke Excel (' . $totalLogs . ' data)');

        if ($download) {
            return response()->download($filePath, $fileName)->deleteFileAfterSend(! $persistBackup);
        }

        return [
            'file_name' => $fileName,
            'relative_path' => $relativePath,
            'file_path' => $filePath,
            'count' => $totalLogs,
        ];
    }

    protected function verifySuperAdminPassword(array $data): void
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        if (! $user || ! $user->hasRole('Super Admin')) {
            throw ValidationException::withMessages([
                'password' => 'Hanya Super Admin yang dapat menghapus log aktivitas.',
            ]);
        }

        if (! Hash::check((string) ($data['password'] ?? ''), (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password Super Admin tidak sesuai.',
            ]);
        }
    }
}
