<?php

namespace App\Filament\Pages;

use App\Models\BukuTamu;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
  protected static ?int $navigationSort = 4;
  protected string $view = 'filament.pages.riwayat-tamu';

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Filament::auth()->user();
    if (!$user)
      return false;
    if ($user->hasRole('Super Admin'))
      return true;
    return $user->role_user && $user->role_user->hasPermission('riwayat_tamu');
  }

  public static function canAccess(): bool
  {
    return static::shouldRegisterNavigation();
  }

  public static function getNavigationItemActiveRoutePattern(): string | array
  {
    return [
      static::getRouteName(),
      ViewRiwayatTamu::getRouteName(),
    ];
  }

  public function getTableRecordKey($record): string
  {
    return (string) $record->id;
  }

  public function table(Table $table): Table
  {
    return $table
      ->query(static::riwayatTamuQuery())
      ->columns([
        Tables\Columns\ViewColumn::make('foto_selfie')
          ->label('Foto')
          ->view('filament.tables.columns.avatar-column'),
        Tables\Columns\TextColumn::make('nama_lengkap')
          ->label('Nama')
          ->searchable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('jenis_id')
          ->label('Jenis ID')
          ->formatStateUsing(fn($state): string => filled($state) ? strtoupper((string) $state) : '-')
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('nik')
          ->label('Nomor ID')
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
      ->paginationPageOptions([5, 10, 20, 50, 100])
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
              ->label('Dari'),
            Forms\Components\DatePicker::make('sampai')
              ->label('Sampai'),
          ])
          ->query(function (Builder $query, array $data): Builder {
            return $query
              ->when($data['dari'] ?? null, fn(Builder $builder, $date): Builder => $builder->whereDate('created_at', '>=', $date))
              ->when($data['sampai'] ?? null, fn(Builder $builder, $date): Builder => $builder->whereDate('created_at', '<=', $date));
          }),
      ])
      ->recordUrl(fn(BukuTamu $record): string => ViewRiwayatTamu::getUrl(['nik' => $record->nik]))
      ->recordActions([
        ActionGroup::make([
          Action::make('view')
            ->label('Lihat Detail')
            ->icon('heroicon-o-eye')
            ->color('primary')
            ->url(fn(BukuTamu $record): string => ViewRiwayatTamu::getUrl(['nik' => $record->nik])),
          Action::make('print')
            ->label('Print')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->url(fn(BukuTamu $record): string => route('buku-tamu.print', ['id' => $record->id]))
            ->openUrlInNewTab(true),
          Action::make('delete')
            ->label('Hapus')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->extraAttributes([
              'style' => 'padding: 10px 16px !important;',
            ])
            ->modalHeading('Hapus Riwayat Pengunjung')
            ->modalDescription(fn(BukuTamu $record): string => static::hasDeletePasswordVerification()
              ? "Semua riwayat kunjungan untuk '{$record->nama_lengkap}' akan dihapus permanen."
              : "Semua riwayat kunjungan untuk '{$record->nama_lengkap}' akan dihapus permanen. Verifikasi password hanya diminta sekali per sesi login.")
            ->schema(fn(): array => static::hasDeletePasswordVerification()
              ? []
              : [
                Forms\Components\TextInput::make('password')
                  ->label('Password Akun Login')
                  ->password()
                  ->required()
                  ->autocomplete('current-password')
                  ->helperText('Ketik password akun yang sedang login. Verifikasi ini hanya diminta sekali per sesi login.'),
              ])
            ->action(function (array $data, BukuTamu $record): void {
              static::verifyDeletePasswordForSession($data);

              $nama = $record->nama_lengkap;
              $deletedCount = static::deleteRiwayatByRecord($record);

              Notification::make()
                ->title('Data berhasil dihapus')
                ->body("{$deletedCount} riwayat kunjungan untuk '{$nama}' berhasil dihapus.")
                ->success()
                ->send();
            }),
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
          BulkAction::make('bulk_delete')
            ->label('Hapus')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->extraAttributes([
              'style' => 'padding: 10px 16px !important;',
            ])
            ->modalHeading('Hapus Riwayat Pengunjung Terpilih')
            ->modalDescription(fn(): string => static::hasDeletePasswordVerification()
              ? 'Semua riwayat pengunjung terpilih akan dihapus permanen.'
              : 'Semua riwayat pengunjung terpilih akan dihapus permanen. Verifikasi password hanya diminta sekali per sesi login.')
            ->schema(fn(): array => static::hasDeletePasswordVerification()
              ? []
              : [
                Forms\Components\TextInput::make('password')
                  ->label('Password Akun Login')
                  ->password()
                  ->required()
                  ->autocomplete('current-password')
                  ->helperText('Ketik password akun yang sedang login. Verifikasi ini hanya diminta sekali per sesi login.'),
              ])
            ->action(function (array $data, $records): void {
              static::verifyDeletePasswordForSession($data);

              $deletedCount = 0;
              $processedNiks = [];

              $records->each(function (BukuTamu $record) use (&$deletedCount, &$processedNiks): void {
                $nik = trim((string) $record->nik);

                if ($nik !== '') {
                  if (isset($processedNiks[$nik])) {
                    return;
                  }

                  $processedNiks[$nik] = true;
                  $deletedCount += static::deleteRiwayatByNik($nik);

                  return;
                }

                $record->delete();
                $deletedCount++;
              });

              Notification::make()
                ->title('Data berhasil dihapus')
                ->body("{$deletedCount} data riwayat pengunjung berhasil dihapus.")
                ->success()
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
        ]),
      ]);
  }

  protected static function riwayatTamuQuery(): Builder
  {
    return BukuTamu::query()
      ->select([
        'buku_tamu.*',
        DB::raw('(SELECT COUNT(*) FROM buku_tamu AS bt WHERE bt.nik = buku_tamu.nik AND (bt.foto_penerimaan IS NULL OR bt.foto_penerimaan = "")) as total_kunjungan'),
        DB::raw('(SELECT MAX(created_at) FROM buku_tamu AS bt WHERE bt.nik = buku_tamu.nik AND (bt.foto_penerimaan IS NULL OR bt.foto_penerimaan = "")) as kunjungan_terakhir'),
      ])
      ->where(function (Builder $query): void {
        $query->whereNull('foto_penerimaan')
          ->orWhere('foto_penerimaan', '');
      })
      ->whereNotExists(function ($subQuery): void {
        $subQuery->selectRaw('1')
          ->from('buku_tamu as bt_newer')
          ->whereColumn('bt_newer.nik', 'buku_tamu.nik')
          ->where(function ($candidateQuery): void {
            $candidateQuery->whereNull('bt_newer.foto_penerimaan')
              ->orWhere('bt_newer.foto_penerimaan', '');
          })
          ->where(function ($newerQuery): void {
            $newerQuery->whereColumn('bt_newer.created_at', '>', 'buku_tamu.created_at')
              ->orWhere(function ($sameTimestampQuery): void {
                $sameTimestampQuery->whereColumn('bt_newer.created_at', '=', 'buku_tamu.created_at')
                  ->whereColumn('bt_newer.id', '>', 'buku_tamu.id');
              });
          });
      });
  }

  protected static function deleteRiwayatByRecord(BukuTamu $record): int
  {
    $nik = trim((string) $record->nik);

    if ($nik !== '') {
      return static::deleteRiwayatByNik($nik);
    }

    $record->delete();

    return 1;
  }

  protected static function riwayatNikCountQuery(string $operator, int $value)
  {
    return DB::table('buku_tamu as bt')
      ->select('bt.nik')
      ->where(function ($query): void {
        $query->whereNull('bt.foto_penerimaan')
          ->orWhere('bt.foto_penerimaan', '');
      })
      ->groupBy('bt.nik')
      ->havingRaw("COUNT(*) {$operator} ?", [$value]);
  }

  protected static function deleteRiwayatByNik(string $nik): int
  {
    return BukuTamu::query()
      ->where('nik', $nik)
      ->where(function (Builder $query): void {
        $query->whereNull('foto_penerimaan')
          ->orWhere('foto_penerimaan', '');
      })
      ->delete();
  }

  protected static function deletePasswordVerificationSessionKey(): string
  {
    return 'riwayat_tamu.delete_password_verified_user_id';
  }

  protected static function hasDeletePasswordVerification(): bool
  {
    /** @var User|null $user */
    $user = Filament::auth()->user();

    if (! $user) {
      return false;
    }

    return (int) session(static::deletePasswordVerificationSessionKey()) === (int) $user->id;
  }

  protected static function verifyDeletePasswordForSession(array $data): void
  {
    /** @var User|null $user */
    $user = Filament::auth()->user();

    if (! $user) {
      throw ValidationException::withMessages([
        'password' => 'Sesi login tidak valid. Silakan login ulang.',
      ]);
    }

    if (static::hasDeletePasswordVerification()) {
      return;
    }

    if (! Hash::check((string) ($data['password'] ?? ''), (string) $user->password)) {
      throw ValidationException::withMessages([
        'password' => 'Password tidak sesuai dengan akun yang sedang login.',
      ]);
    }

    session([
      static::deletePasswordVerificationSessionKey() => (int) $user->id,
    ]);
  }

  public function getFooter(): ?View
  {
    return view('filament.pages.riwayat-tamu-footer');
  }
}
