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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        return [];
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
