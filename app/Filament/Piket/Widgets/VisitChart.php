<?php

namespace App\Filament\Piket\Widgets;

use App\Models\BukuTamu;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VisitChart extends ChartWidget
{
  protected static ?string $heading = 'Kunjungan 7 Hari Terakhir';
  protected static ?int $sort = 2;
  protected int | string | array $columnSpan = 'full';

  protected function getData(): array
  {
    $data = BukuTamu::select(
      DB::raw('DATE(created_at) as tanggal'),
      DB::raw('COUNT(*) as jumlah')
    )
      ->where('created_at', '>=', now()->subDays(6)->startOfDay())
      ->groupBy('tanggal')
      ->orderBy('tanggal')
      ->get()
      ->pluck('jumlah', 'tanggal')
      ->toArray();

    $labels = [];
    $values = [];

    for ($i = 6; $i >= 0; $i--) {
      $date = now()->subDays($i);
      $labels[] = $date->translatedFormat('d M');
      $values[] = $data[$date->toDateString()] ?? 0;
    }

    return [
      'datasets' => [
        [
          'label' => 'Jumlah Kunjungan',
          'data' => $values,
          'backgroundColor' => 'rgba(15, 148, 85, 0.15)',
          'borderColor' => '#0F9455',
          'borderWidth' => 2,
        ],
      ],
      'labels' => $labels,
    ];
  }

  protected function getType(): string
  {
    return 'bar';
  }

  protected function getOptions(): array
  {
    return [
      'plugins' => [
        'legend' => [
          'display' => true,
          'position' => 'bottom',
        ],
      ],
      'scales' => [
        'y' => [
          'beginAtZero' => true,
          'min' => 0,
          'max' => 20,
          'ticks' => [
            'stepSize' => 1,
            'precision' => 0,
          ],
        ],
        'x' => [
          'grid' => [
            'display' => false,
          ],
        ],
      ],
    ];
  }
}
