<?php

namespace App\Filament\Loket\Pages;

use App\Models\BukuTamu;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class RiwayatTamu extends Page implements HasTable
{
  use InteractsWithTable;

  protected static ?string $navigationIcon = 'heroicon-o-clock';
  protected static ?string $navigationLabel = 'Riwayat Pengunjung';
  protected static ?string $navigationGroup = 'Layanan Tamu';
  protected static ?string $title = 'Riwayat Pengunjung';
  protected static ?int $navigationSort = 3;
  protected static string $view = 'filament.loket.pages.riwayat-tamu';

  public function table(Table $table): Table
  {
    return $table
      ->query(
        BukuTamu::query()
          ->select(
            'nama_lengkap',
            'nik',
            'instansi',
            'nomor_hp',
            DB::raw('COUNT(*) as total_kunjungan'),
            DB::raw('MAX(created_at) as kunjungan_terakhir')
          )
          ->groupBy('nama_lengkap', 'nik', 'instansi', 'nomor_hp')
      )
      ->columns([
        Tables\Columns\TextColumn::make('nama_lengkap')
          ->label('Nama')
          ->searchable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('nik')
          ->label('NIK')
          ->searchable(),
        Tables\Columns\TextColumn::make('instansi'),
        Tables\Columns\TextColumn::make('nomor_hp')
          ->label('No. HP'),
        Tables\Columns\TextColumn::make('total_kunjungan')
          ->label('Total Kunjungan')
          ->badge()
          ->color('success')
          ->alignCenter()
          ->sortable(),
        Tables\Columns\TextColumn::make('kunjungan_terakhir')
          ->label('Terakhir Berkunjung')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
      ])
      ->defaultSort('total_kunjungan', 'desc');
  }
}
