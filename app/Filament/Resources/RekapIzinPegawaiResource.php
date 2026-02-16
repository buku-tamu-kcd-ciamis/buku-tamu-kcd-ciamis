<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RekapIzinPegawaiResource\Pages;
use App\Models\PegawaiIzin;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RekapIzinPegawaiResource extends Resource
{
  protected static ?string $model = PegawaiIzin::class;

  protected static ?string $slug = 'rekap-izin-pegawai';
  protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
  protected static ?string $navigationLabel = 'Rekap Izin Pegawai';
  protected static ?string $navigationGroup = 'Kepegawaian';
  protected static ?string $modelLabel = 'Rekap Izin';
  protected static ?string $pluralModelLabel = 'Rekap Izin Pegawai';
  protected static ?int $navigationSort = 3;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->role_user && $user->role_user->hasPermission('rekap_izin');
  }

  public static function canCreate(): bool
  {
    return false;
  }

  public static function table(Table $table): Table
  {
    return $table
      ->query(
        PegawaiIzin::query()
          ->select(
            'nama_pegawai',
            'nip',
            'jabatan',
            'unit_kerja',
            DB::raw('COUNT(*) as total_izin'),
            DB::raw("SUM(CASE WHEN jenis_izin = 'sakit' THEN 1 ELSE 0 END) as total_sakit"),
            DB::raw("SUM(CASE WHEN jenis_izin = 'cuti' THEN 1 ELSE 0 END) as total_cuti"),
            DB::raw("SUM(CASE WHEN jenis_izin = 'dinas_luar' THEN 1 ELSE 0 END) as total_dinas_luar"),
            DB::raw("SUM(CASE WHEN jenis_izin = 'izin_pribadi' THEN 1 ELSE 0 END) as total_izin_pribadi"),
            DB::raw("SUM(CASE WHEN jenis_izin = 'lainnya' THEN 1 ELSE 0 END) as total_lainnya"),
            DB::raw("SUM(DATEDIFF(tanggal_selesai, tanggal_mulai) + 1) as total_hari"),
            DB::raw("MAX(tanggal_mulai) as terakhir_izin"),
            DB::raw("SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as sedang_izin"),
          )
          ->groupBy('nip', 'nama_pegawai', 'jabatan', 'unit_kerja')
      )
      ->columns([
        Tables\Columns\TextColumn::make('row_number')
          ->label('#')
          ->state(fn($rowLoop) => $rowLoop->iteration)
          ->width('50px'),
        Tables\Columns\TextColumn::make('nama_pegawai')
          ->label('Nama Pegawai')
          ->searchable()
          ->weight('bold')
          ->description(fn($record) => $record->nip),
        Tables\Columns\TextColumn::make('jabatan')
          ->label('Jabatan')
          ->toggleable(),
        Tables\Columns\TextColumn::make('unit_kerja')
          ->label('Unit Kerja')
          ->toggleable(),
        Tables\Columns\TextColumn::make('total_izin')
          ->label('Total Izin')
          ->sortable()
          ->badge()
          ->color(fn($state): string => match (true) {
            $state >= 5 => 'danger',
            $state >= 3 => 'warning',
            default => 'success',
          })
          ->alignCenter()
          ->suffix(' kali'),
        Tables\Columns\TextColumn::make('total_hari')
          ->label('Total Hari')
          ->sortable()
          ->alignCenter()
          ->suffix(' hari')
          ->color(fn($state): string => match (true) {
            $state >= 10 => 'danger',
            $state >= 5 => 'warning',
            default => 'gray',
          }),
        Tables\Columns\TextColumn::make('total_sakit')
          ->label('Sakit')
          ->alignCenter()
          ->toggleable()
          ->color(fn($state) => $state > 0 ? 'danger' : 'gray'),
        Tables\Columns\TextColumn::make('total_cuti')
          ->label('Cuti')
          ->alignCenter()
          ->toggleable()
          ->color(fn($state) => $state > 0 ? 'info' : 'gray'),
        Tables\Columns\TextColumn::make('total_dinas_luar')
          ->label('Dinas Luar')
          ->alignCenter()
          ->toggleable()
          ->color(fn($state) => $state > 0 ? 'warning' : 'gray'),
        Tables\Columns\TextColumn::make('total_izin_pribadi')
          ->label('Pribadi')
          ->alignCenter()
          ->toggleable()
          ->color(fn($state) => $state > 0 ? 'primary' : 'gray'),
        Tables\Columns\TextColumn::make('total_lainnya')
          ->label('Lainnya')
          ->alignCenter()
          ->toggleable(isToggledHiddenByDefault: true)
          ->color(fn($state) => $state > 0 ? 'gray' : 'gray'),
        Tables\Columns\TextColumn::make('sedang_izin')
          ->label('Status')
          ->formatStateUsing(fn($state) => $state > 0 ? 'Sedang Izin' : 'Aktif')
          ->badge()
          ->color(fn($state) => $state > 0 ? 'warning' : 'success')
          ->alignCenter(),
        Tables\Columns\TextColumn::make('terakhir_izin')
          ->label('Terakhir Izin')
          ->date('d/m/Y')
          ->sortable(),
      ])
      ->defaultSort('total_izin', 'desc')
      ->recordUrl(fn($record) => static::getUrl('view', ['record' => $record->nip]))
      ->filters([
        Tables\Filters\SelectFilter::make('jenis_izin')
          ->label('Jenis Izin')
          ->options(PegawaiIzin::JENIS_IZIN_LABELS)
          ->query(function (Builder $query, array $data) {
            if ($data['value']) {
              $query->having(DB::raw("SUM(CASE WHEN jenis_izin = '{$data['value']}' THEN 1 ELSE 0 END)"), '>', 0);
            }
          }),
        Tables\Filters\Filter::make('sering_izin')
          ->label('Sering Izin (≥ 3x)')
          ->query(fn(Builder $query) => $query->having('total_izin', '>=', 3))
          ->toggle(),
        Tables\Filters\Filter::make('sedang_izin')
          ->label('Sedang Izin Sekarang')
          ->query(fn(Builder $query) => $query->having('sedang_izin', '>', 0))
          ->toggle(),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\Action::make('detail')
            ->label('Lihat Detail')
            ->icon('heroicon-o-eye')
            ->color('info')
            ->url(fn($record) => static::getUrl('view', ['record' => $record->nip])),
        ])
          ->icon('heroicon-m-ellipsis-vertical')
          ->tooltip('Aksi'),
      ])
      ->bulkActions([])
      ->emptyStateHeading('Belum Ada Data Izin')
      ->emptyStateDescription('Data rekap izin pegawai akan muncul setelah ada data izin yang diinput dari panel Piket.')
      ->emptyStateIcon('heroicon-o-clipboard-document-list');
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListRekapIzinPegawai::route('/'),
      'view' => Pages\ViewRekapIzinPegawai::route('/{record}'),
    ];
  }
}
