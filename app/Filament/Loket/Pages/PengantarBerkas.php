<?php

namespace App\Filament\Loket\Pages;

use App\Models\BukuTamu;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengantarBerkas extends Page implements HasTable
{
  use InteractsWithTable;

  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel = 'Pengantar Berkas';
  protected static ?string $navigationGroup = 'Layanan Tamu';
  protected static ?string $title = 'Daftar Pengantar Berkas';
  protected static ?int $navigationSort = 2;
  protected static string $view = 'filament.loket.pages.pengantar-berkas';

  public function table(Table $table): Table
  {
    return $table
      ->query(
        BukuTamu::query()
          ->where(function ($q) {
            $q->where('keperluan', 'like', '%berkas%')
              ->orWhere('keperluan', 'like', '%surat%')
              ->orWhere('keperluan', 'like', '%dokumen%')
              ->orWhere('keperluan', 'like', '%legalisir%');
          })
      )
      ->columns([
        Tables\Columns\TextColumn::make('nama_lengkap')
          ->label('Nama')
          ->searchable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('instansi')
          ->searchable(),
        Tables\Columns\TextColumn::make('keperluan'),
        Tables\Columns\TextColumn::make('bagian_dituju')
          ->label('Bagian Dituju'),
        Tables\Columns\BadgeColumn::make('status')
          ->colors([
            'warning' => 'menunggu',
            'info' => 'diproses',
            'success' => 'selesai',
            'danger' => 'ditolak',
            'gray' => 'dibatalkan',
          ])
          ->formatStateUsing(fn(string $state) => BukuTamu::STATUS_LABELS[$state] ?? ucfirst($state)),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Waktu')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
      ])
      ->defaultSort('created_at', 'desc')
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->options(BukuTamu::STATUS_LABELS),
      ]);
  }
}
