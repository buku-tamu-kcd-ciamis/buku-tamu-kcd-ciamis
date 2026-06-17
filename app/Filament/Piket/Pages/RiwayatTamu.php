<?php

namespace App\Filament\Piket\Pages;

use App\Models\BukuTamu;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class RiwayatTamu extends Page implements HasTable
{
  use InteractsWithTable;

  public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
  {
    return null;
  }

  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';
  protected static ?string $navigationLabel = 'Riwayat Pengunjung';
  protected static string|\UnitEnum|null $navigationGroup = 'Layanan Tamu';
  protected static ?string $title = 'Riwayat Pengunjung';
  protected static ?int $navigationSort = 3;
  protected string $view = 'filament.piket.pages.riwayat-tamu';

  public static function shouldRegisterNavigation(): bool
  {
    return true;
  }

  public static function canAccess(): bool
  {
    return true;
  }

  public function getTableRecordKey($record): string
  {
    return (string) $record->id;
  }

  public ?string $activeTab = 'hari_ini';

  protected function hasCustomDateFilters(): bool
  {
    return !empty($this->tableFilters['kunjungan_terakhir']['dari'] ?? null) || !empty($this->tableFilters['kunjungan_terakhir']['sampai'] ?? null);
  }

  protected static function riwayatTamuQuery(): Builder
  {
    return BukuTamu::query()
      ->select([
        'buku_tamu.*',
        DB::raw('(SELECT COUNT(*) FROM buku_tamu AS bt WHERE bt.nik = buku_tamu.nik) as total_kunjungan'),
        DB::raw('(SELECT MAX(created_at) FROM buku_tamu AS bt WHERE bt.nik = buku_tamu.nik) as kunjungan_terakhir')
      ])
      ->whereNotExists(function ($subQuery): void {
        $subQuery->selectRaw('1')
          ->from('buku_tamu as bt_newer')
          ->whereColumn('bt_newer.nik', 'buku_tamu.nik')
          ->where(function ($newerQuery): void {
            $newerQuery->whereColumn('bt_newer.created_at', '>', 'buku_tamu.created_at')
              ->orWhere(function ($sameTimestampQuery): void {
                $sameTimestampQuery->whereColumn('bt_newer.created_at', '=', 'buku_tamu.created_at')
                  ->whereColumn('bt_newer.id', '>', 'buku_tamu.id');
              });
          });
      });
  }

  public function getTabBadge(string $tab): int
  {
    $query = static::riwayatTamuQuery();

    return match ($tab) {
      'hari_ini' => $query->whereDate('created_at', now()->toDateString())->count(),
      'kemarin' => $query->whereDate('created_at', now()->subDay()->toDateString())->count(),
      'minggu_ini' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
      'bulan_ini' => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
      'semua' => $query->count(),
      default => 0,
    };
  }

  public function table(Table $table): Table
  {
    return $table
      ->query(
        static::riwayatTamuQuery()
          ->when($this->activeTab === 'hari_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereDate('created_at', now()->toDateString()))
          ->when($this->activeTab === 'kemarin' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereDate('created_at', now()->subDay()->toDateString()))
          ->when($this->activeTab === 'minggu_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
          ->when($this->activeTab === 'bulan_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]))
      )
      ->columns([
        Tables\Columns\ViewColumn::make('foto_selfie')
          ->label('Foto')
          ->view('filament.tables.columns.avatar-column'),
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
            if (!$state)
              return '-';
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
          ->color('gray')
          ->tooltip(fn($record) => \Carbon\Carbon::parse($record->kunjungan_terakhir)->format('d/m/Y H:i'))
          ->sortable(),
      ])
      ->defaultSort('total_kunjungan', 'desc')
      ->defaultPaginationPageOption(10)
      ->paginationPageOptions([10])
      ->filters([
        Tables\Filters\SelectFilter::make('instansi')
          ->label('Instansi')
          ->options(fn(): array => static::riwayatTamuQuery()
            ->select('instansi')
            ->whereNotNull('instansi')
            ->where('instansi', '!=', '')
            ->orderBy('instansi')
            ->distinct()
            ->pluck('instansi', 'instansi')
            ->all())
          ->searchable(),
        Tables\Filters\TernaryFilter::make('jenis_kunjungan')
          ->label('Jenis Kunjungan')
          ->placeholder('Semua')
          ->trueLabel('Kunjungan Berulang')
          ->falseLabel('Kunjungan Sekali')
          ->queries(
            true: fn(Builder $query): Builder => $query->whereIn('nik', static::riwayatNikCountQuery('>', 1)),
            false: fn(Builder $query): Builder => $query->whereIn('nik', static::riwayatNikCountQuery('=', 1)),
            blank: fn(Builder $query): Builder => $query,
          ),
        Tables\Filters\Filter::make('kunjungan_terakhir')
          ->label('Periode Terakhir Berkunjung')
          ->schema([
            Forms\Components\DatePicker::make('dari')
              ->label('Dari Tanggal')
              ->native(false)
              ->displayFormat('d/m/Y')
              ->closeOnDateSelection(),
            Forms\Components\DatePicker::make('sampai')
              ->label('Sampai Tanggal')
              ->native(false)
              ->displayFormat('d/m/Y')
              ->closeOnDateSelection(),
          ])
          ->query(function (Builder $query, array $data): Builder {
            return $query
              ->when($data['dari'] ?? null, fn(Builder $builder, $date): Builder => $builder->whereDate('created_at', '>=', $date))
              ->when($data['sampai'] ?? null, fn(Builder $builder, $date): Builder => $builder->whereDate('created_at', '<=', $date));
          })
          ->indicateUsing(function (array $data): array {
            $indicators = [];
            if ($data['dari'] ?? null) {
              $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['dari'])->translatedFormat('d M Y');
            }
            if ($data['sampai'] ?? null) {
              $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai'])->translatedFormat('d M Y');
            }
            return $indicators;
          })
          ->columns(2)
          ->columnSpan(2),
      ])
      ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
      ->filtersFormColumns(4)
      ->recordActionsColumnLabel('')
      ->recordActions([
        ActionGroup::make([
          Action::make('view')
            ->label('Lihat Detail')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->url(fn(BukuTamu $record) => ViewRiwayatTamu::getUrl(['nik' => $record->nik])),
        ])
          ->label(false)
          ->icon('heroicon-m-ellipsis-vertical')
          ->color('gray'),
      ])
      ->headerActions([])
      ->toolbarActions([
        BulkActionGroup::make([
          BulkAction::make('bulk_print')
            ->label('Print')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->url(route('buku-tamu.print-bulk'))
            ->livewireClickHandlerEnabled(false)
            ->accessSelectedRecords(false)
            ->openUrlInNewTab(true)
            ->extraAttributes([
              'style' => 'padding: 10px 16px !important;',
              'x-bind:href' => "`\${window.location.origin}/print/buku-tamu-bulk?ids=\${[...selectedRecords].join(',')}`",
            ]),
        ]),
      ]);
  }

  public function getFooter(): ?View
  {
    return view('filament.piket.pages.riwayat-tamu-footer');
  }

  protected static function riwayatNikCountQuery(string $operator, int $value)
  {
    return DB::table('buku_tamu as bt')
      ->select('bt.nik')
      ->groupBy('bt.nik')
      ->havingRaw("COUNT(*) {$operator} ?", [$value]);
  }
}
