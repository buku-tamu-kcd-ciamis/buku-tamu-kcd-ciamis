<?php

namespace App\Filament\Staff\Widgets;

use App\Models\BukuTamu;
use App\Models\StaffNotification;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffVisitTrendWidget extends ChartWidget
{
    protected ?string $heading = 'Tren Kunjungan 14 Hari Terakhir';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $pegawaiNama = $user->pegawai?->nama ?? $user->name;

        $kunjunganData = BukuTamu::select(
            DB::raw('DATE(created_at) as tanggal'),
            DB::raw('COUNT(*) as jumlah')
        )
            ->where('staff_dituju', $pegawaiNama)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->pluck('jumlah', 'tanggal')
            ->toArray();

        $diterimaData = StaffNotification::select(
            DB::raw('DATE(created_at) as tanggal'),
            DB::raw('COUNT(*) as jumlah')
        )
            ->where('user_id', $user->id)
            ->where('response', 'diterima')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->pluck('jumlah', 'tanggal')
            ->toArray();

        $labels = [];
        $kunjunganValues = [];
        $diterimaValues = [];

        for ($i = 13; $i >= 0; $i--) {
            $tanggal = now()->subDays($i);
            $key = $tanggal->toDateString();

            $labels[] = $tanggal->translatedFormat('d M');
            $kunjunganValues[] = $kunjunganData[$key] ?? 0;
            $diterimaValues[] = $diterimaData[$key] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Kunjungan ke Anda',
                    'data' => $kunjunganValues,
                    'borderColor' => '#0f9455',
                    'backgroundColor' => 'rgba(15, 148, 85, 0.14)',
                    'tension' => 0.32,
                    'fill' => true,
                ],
                [
                    'label' => 'Respons Diterima',
                    'data' => $diterimaValues,
                    'borderColor' => '#0a5f38',
                    'backgroundColor' => 'rgba(10, 95, 56, 0.10)',
                    'tension' => 0.32,
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
