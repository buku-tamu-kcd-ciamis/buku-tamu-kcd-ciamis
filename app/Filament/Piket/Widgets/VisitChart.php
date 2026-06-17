<?php

namespace App\Filament\Piket\Widgets;

use App\Models\BukuTamu;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VisitChart extends ChartWidget
{
  protected ?string $heading = 'Grafik Kunjungan Tamu';
  protected static ?int $sort = 3;
  protected int | string | array $columnSpan = 'full';

  public ?string $filter = 'week';
  private ?array $cachedData = null;

  protected function getFilters(): ?array
  {
    return [
      'week' => '1 Minggu',
      'month' => '1 Bulan',
      'year' => '1 Tahun',
    ];
  }

  protected function getData(): array
  {
    if ($this->cachedData !== null) {
      return $this->cachedData;
    }

    $daysBack = match ($this->filter) {
      'week' => 6,
      'month' => 29,
      'year' => 364,
      default => 6,
    };

    $data = BukuTamu::select(
      DB::raw('DATE(created_at) as tanggal'),
      DB::raw('COUNT(*) as jumlah')
    )
      ->where('created_at', '>=', now()->subDays($daysBack)->startOfDay())
      ->groupBy('tanggal')
      ->orderBy('tanggal')
      ->get()
      ->pluck('jumlah', 'tanggal')
      ->toArray();

    $labels = [];
    $values = [];

    // Untuk 1 tahun, group by bulan
    if ($this->filter === 'year') {
      for ($i = 11; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $labels[] = $date->translatedFormat('M Y');

        $sum = BukuTamu::whereYear('created_at', $date->year)
          ->whereMonth('created_at', $date->month)
          ->count();

        $values[] = $sum;
      }
    } else {
      for ($i = $daysBack; $i >= 0; $i--) {
        $date = now()->subDays($i);
        $labels[] = $date->translatedFormat('d M');
        $values[] = $data[$date->toDateString()] ?? 0;
      }
    }

    $this->cachedData = [
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

    return $this->cachedData;
  }

  protected function getType(): string
  {
    return 'bar';
  }

  protected function getOptions(): array
  {
    $data = $this->getData();
    $values = $data['datasets'][0]['data'] ?? [];
    $maxActualValue = empty($values) ? 0 : max($values);

    $defaultMax = match ($this->filter) {
      'week' => 20,
      'month' => 50,
      'year' => 100,
      default => 20,
    };

    $maxValue = max($defaultMax, $maxActualValue);

    if ($maxValue > $defaultMax) {
      if ($maxValue <= 50) {
        $stepSize = 5;
      } elseif ($maxValue <= 100) {
        $stepSize = 10;
      } elseif ($maxValue <= 500) {
        $stepSize = 50;
      } else {
        $stepSize = 100;
      }
      $maxValue = ceil($maxValue / $stepSize) * $stepSize;
    } else {
      $stepSize = match ($this->filter) {
        'week' => 1,
        'month' => 5,
        'year' => 10,
        default => 1,
      };
    }

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
          'max' => $maxValue,
          'ticks' => [
            'stepSize' => $stepSize,
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
