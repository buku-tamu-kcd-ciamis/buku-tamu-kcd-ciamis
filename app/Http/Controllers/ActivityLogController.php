<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
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

    $ketuaKcd = \App\Models\PengaturanKcd::getSettings();

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

    return view('print.activity-logs', compact('activityLogs', 'ketuaKcd', 'limit'));
  }
}
