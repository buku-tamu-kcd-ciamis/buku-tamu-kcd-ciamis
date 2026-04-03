<?php

namespace App\Filament\Piket\Widgets;

use App\Models\PegawaiIzin;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class StaffTidakMasukTodayWidget extends TableWidget
{
  protected static ?int $sort = 2;

  protected int|string|array $columnSpan = 'full';

  public function table(Table $table): Table
  {
    $today = now()->toDateString();

    return $table
      ->heading('Staff Tidak Masuk Hari Ini')
      ->query(
        PegawaiIzin::query()
          ->whereIn('status', [
            PegawaiIzin::STATUS_DISETUJUI,
            PegawaiIzin::STATUS_AKTIF,
          ])
          ->whereDate('tanggal_mulai', '<=', $today)
          ->whereDate('tanggal_selesai', '>=', $today)
      )
      ->columns([
        Tables\Columns\TextColumn::make('nama_pegawai')
          ->label('Nama Staff')
          ->searchable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('jenis_izin')
          ->label('Jenis Izin')
          ->badge()
          ->formatStateUsing(fn(string $state) => PegawaiIzin::JENIS_IZIN_LABELS[$state] ?? $state)
          ->color('warning'),
        Tables\Columns\TextColumn::make('status')
          ->label('Keterangan')
          ->badge()
          ->formatStateUsing(fn(): string => 'Tidak Masuk')
          ->color('danger'),
        Tables\Columns\TextColumn::make('keterangan')
          ->label('Alasan / Catatan')
          ->placeholder('-')
          ->wrap()
          ->limit(80),
        Tables\Columns\TextColumn::make('tanggal_selesai')
          ->label('Sampai Tanggal')
          ->date('d M Y')
          ->sortable(),
      ])
      ->defaultSort('tanggal_mulai', 'asc')
      ->defaultPaginationPageOption(5)
      ->paginationPageOptions([5, 10])
      ->emptyStateIcon('heroicon-o-check-circle')
      ->emptyStateHeading('Tidak ada staff yang tidak masuk hari ini')
      ->emptyStateDescription('Semua staff tercatat tersedia untuk hari ini.');
  }
}
