<?php

namespace App\Filament\Piket\Pages;

use App\Models\BukuTamu;
use App\Models\DropdownOption;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class RiwayatTamu extends Page implements HasTable
{
  use InteractsWithTable;

  protected static ?string $navigationIcon = 'heroicon-o-clock';
  protected static ?string $navigationLabel = 'Riwayat Pengunjung';
  protected static ?string $navigationGroup = 'Layanan Tamu';
  protected static ?string $title = 'Riwayat Pengunjung';
  protected static ?int $navigationSort = 3;
  protected static string $view = 'filament.piket.pages.riwayat-tamu';

  public function getTableRecordKey($record): string
  {
    return (string) $record->id;
  }

  public function table(Table $table): Table
  {
    return $table
      ->query(
        BukuTamu::query()
          ->select([
            'buku_tamu.*',
            DB::raw('(SELECT COUNT(*) FROM buku_tamu AS bt WHERE bt.nik = buku_tamu.nik) as total_kunjungan'),
            DB::raw('(SELECT MAX(created_at) FROM buku_tamu AS bt WHERE bt.nik = buku_tamu.nik) as kunjungan_terakhir')
          ])
          ->whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
              ->from('buku_tamu')
              ->groupBy('nik');
          })
      )
      ->columns([
        Tables\Columns\ImageColumn::make('foto_selfie')
          ->label('Foto')
          ->circular()
          ->size(40)
          ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=G&background=0F9455&color=fff'),
        Tables\Columns\TextColumn::make('nama_lengkap')
          ->label('Nama')
          ->searchable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('nik')
          ->label('NIK')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('instansi')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('nomor_hp')
          ->label('No. HP')
          ->formatStateUsing(function ($state) {
            if (!$state) return '-';
            $cleaned = preg_replace('/[^0-9]/', '', $state);
            if (str_starts_with($cleaned, '0')) {
              $cleaned = substr($cleaned, 1);
            }
            return '+62' . $cleaned;
          })
          ->toggleable(),
        Tables\Columns\TextColumn::make('total_kunjungan')
          ->label('Total Kunjungan')
          ->badge()
          ->color('success')
          ->alignCenter()
          ->sortable(),
        Tables\Columns\TextColumn::make('kunjungan_terakhir')
          ->label('Terakhir Berkunjung')
          ->since()
          ->tooltip(fn($record) => \Carbon\Carbon::parse($record->kunjungan_terakhir)->format('d/m/Y H:i'))
          ->sortable(),
      ])
      ->defaultSort('total_kunjungan', 'desc')
      ->defaultPaginationPageOption(10)
      ->paginationPageOptions([10])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\Action::make('lihat_detail')
            ->label('Lihat Detail')
            ->icon('heroicon-s-eye')
            ->url(fn($record) => \App\Filament\Piket\Pages\ViewRiwayatTamu::getUrl(['nik' => $record->nik])),
        ])
          ->label(false)
          ->icon('heroicon-m-ellipsis-vertical')
          ->button()
          ->color('gray'),
      ])
      ->headerActions([
        Tables\Actions\Action::make('print_bulk')
          ->label('Cetak Laporan')
          ->icon('heroicon-o-printer')
          ->color('success')
          ->form([
            Forms\Components\DatePicker::make('start_date')
              ->label('Tanggal Mulai'),
            Forms\Components\DatePicker::make('end_date')
              ->label('Tanggal Akhir'),
            Forms\Components\Select::make('nama')
              ->label('Nama Pengunjung')
              ->searchable()
              ->getSearchResultsUsing(function (string $search) {
                return BukuTamu::query()
                  ->where('nama_lengkap', 'like', "%{$search}%")
                  ->distinct()
                  ->pluck('nama_lengkap', 'nama_lengkap')
                  ->toArray();
              })
              ->getOptionLabelUsing(fn($value): ?string => $value)
              ->placeholder('Cari nama pengunjung...'),
            Forms\Components\Select::make('keperluan')
              ->label('Keperluan')
              ->searchable()
              ->options(DropdownOption::getOptions(DropdownOption::CATEGORY_KEPERLUAN))
              ->placeholder('Pilih keperluan'),
          ])
          ->action(function (array $data, $livewire) {
            $query = http_build_query(array_filter([
              'start_date' => $data['start_date'] ?? null,
              'end_date' => $data['end_date'] ?? null,
              'nama' => $data['nama'] ?? null,
              'keperluan' => $data['keperluan'] ?? null,
            ]));

            $url = route('buku-tamu.print-bulk') . ($query ? '?' . $query : '');

            // Dispatch browser event to open in new tab
            $livewire->dispatch('open-url-in-new-tab', url: $url);
          })
          ->modalHeading('Filter Laporan Riwayat Pengunjung')
          ->modalSubmitActionLabel('Cetak'),
      ]);
  }

  public function getFooter(): ?View
  {
    return view('filament.piket.pages.riwayat-tamu-footer');
  }
}
