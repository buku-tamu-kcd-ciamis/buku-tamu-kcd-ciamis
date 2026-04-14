<?php

namespace App\Http\Controllers;

use App\Filament\Resources\ActivityLogResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
  public function backupDownload(Request $request)
  {
    /** @var User|null $user */
    $user = Auth::user();

    if (! $user || ! $user->role_user || ! $user->role_user->hasPermission('activity_log')) {
      abort(403);
    }

    $baseQuery = Activity::query()
      ->with('causer')
      ->orderByDesc('created_at')
      ->orderByDesc('id');

    $totalLogs = (clone $baseQuery)->count();

    if ($totalLogs === 0) {
      abort(404, 'Belum ada data log aktivitas untuk di-backup.');
    }

    $fileName = 'backup-log-aktivitas-' . now()->format('Y-m-d_H-i-s') . '.xlsx';
    $relativePath = 'tmp/' . $fileName;
    $filePath = storage_path('app/' . $relativePath);

    Storage::disk('local')->makeDirectory('tmp');

    $options = new Options();
    $writer = new Writer($options);
    $writer->openToFile($filePath);

    $headerStyle = (new Style())
      ->setFontBold()
      ->setFontSize(11)
      ->setFontColor(Color::WHITE)
      ->setBackgroundColor(Color::rgb(30, 64, 175));

    $writer->addRow(Row::fromValues([
      'No',
      'Waktu',
      'User',
      'Modul',
      'Aksi',
      'Aktivitas',
      'Model',
      'ID Subject',
      'Properties',
    ], $headerStyle));

    $no = 1;
    foreach ($baseQuery->cursor() as $log) {
      $writer->addRow(Row::fromValues([
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
      ]));
    }

    $writer->close();

    activity('cetak')
      ->causedBy($user)
      ->event('created')
      ->withProperties([
        'jumlah' => $totalLogs,
        'tipe' => 'backup_excel',
        'file' => $fileName,
        'path' => $relativePath,
      ])
      ->log('Backup log aktivitas ke Excel (' . $totalLogs . ' data)');

    return response()
      ->download($filePath, $fileName, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      ])
      ->deleteFileAfterSend(true);
  }

  public function print(Request $request)
  {
    $query = Activity::with('causer')->orderBy('created_at', 'desc');

    // Filter by user
    if ($request->has('user_id') && $request->user_id) {
      $query->where('causer_id', $request->user_id);
    }

    // Filter by log name
    if ($request->has('log_name') && $request->log_name) {
      $query->where('log_name', $request->log_name);
    }

    // Filter by event
    if ($request->has('event') && $request->event) {
      $query->where('event', $request->event);
    }

    // Filter by date range
    if ($request->has('start_date') && $request->start_date) {
      $query->whereDate('created_at', '>=', $request->start_date);
    }

    if ($request->has('end_date') && $request->end_date) {
      $query->whereDate('created_at', '<=', $request->end_date);
    }

    // Limit records
    $limit = $request->query('limit', 50);
    $activityLogs = $query->limit($limit)->get();

    $kepalaCabdin = \App\Models\PengaturanKcd::getSettings();

    // Log this print action
    if (Auth::check()) {
      activity('cetak')
        ->causedBy(Auth::user())
        ->withProperties([
          'jumlah' => $activityLogs->count(),
          'tipe' => 'activity_logs',
          'limit' => $limit,
          'filter' => array_filter([
            'user_id' => $request->user_id,
            'log_name' => $request->log_name,
            'event' => $request->event,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
          ]),
        ])
        ->log('Mencetak log aktivitas (' . $activityLogs->count() . ' data)');
    }

    return view('print.activity-logs', compact('activityLogs', 'kepalaCabdin', 'limit'));
  }
}
