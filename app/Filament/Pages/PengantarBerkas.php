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
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PengantarBerkas extends Page implements HasTable
{
  use InteractsWithTable;

  public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
  {
    return null;
  }

  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel = 'Pengantar Berkas';
  protected static string|\UnitEnum|null $navigationGroup = 'Layanan Tamu';
  protected static ?string $title = 'Daftar Pengantar Berkas';
  protected static ?int $navigationSort = 3;
  protected string $view = 'filament.pages.pengantar-berkas';

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Filament::auth()->user();
    return $user && $user->hasRole('Super Admin');
  }

  public static function canAccess(): bool
  {
    /** @var User $user */
    $user = Filament::auth()->user();

    return $user && $user->hasRole('Super Admin');
  }

  public static function getNavigationItemActiveRoutePattern(): string | array
  {
    return [
      static::getRouteName(),
      ViewPengantarBerkas::getRouteName(),
    ];
  }

  public function table(Table $table): Table
  {
    return $table
      ->query(static::pengantarBerkasQuery())
      ->columns([
        Tables\Columns\ViewColumn::make('foto_selfie')
          ->label('Foto')
          ->view('filament.tables.columns.avatar-column'),
        Tables\Columns\TextColumn::make('nama_lengkap')
          ->label('Nama')
          ->searchable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('instansi')
          ->searchable(),
        Tables\Columns\TextColumn::make('keperluan'),
        Tables\Columns\TextColumn::make('staff_dituju')
          ->label('Staff Yang Dituju'),
        Tables\Columns\TextColumn::make('status')
          ->badge()
          ->color(fn(string $state): string => match ($state) {
            'menunggu' => 'warning',
            'diproses' => 'info',
            'selesai' => 'success',
            'ditolak' => 'danger',
            'dibatalkan' => 'gray',
            default => 'gray',
          })
          ->formatStateUsing(fn(string $state) => BukuTamu::STATUS_LABELS[$state] ?? ucfirst($state)),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Waktu')
          ->since()
          ->color('gray')
          ->tooltip(fn($record) => $record->created_at->format('d/m/Y H:i'))
          ->sortable(),
      ])
      ->defaultSort('created_at', 'desc')
      ->defaultPaginationPageOption(10)
      ->paginationPageOptions([10])
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->options(BukuTamu::STATUS_LABELS),
        Tables\Filters\SelectFilter::make('keperluan')
          ->label('Keperluan')
          ->options(fn(): array => static::pengantarBerkasQuery()
            ->select('keperluan')
            ->whereNotNull('keperluan')
            ->where('keperluan', '!=', '')
            ->orderBy('keperluan')
            ->distinct()
            ->pluck('keperluan', 'keperluan')
            ->all())
          ->searchable(),
        Tables\Filters\SelectFilter::make('staff_dituju')
          ->label('Staff Yang Dituju')
          ->options(fn(): array => static::pengantarBerkasQuery()
            ->select('staff_dituju')
            ->whereNotNull('staff_dituju')
            ->where('staff_dituju', '!=', '')
            ->orderBy('staff_dituju')
            ->distinct()
            ->pluck('staff_dituju', 'staff_dituju')
            ->all())
          ->searchable(),
      ])
      ->recordUrl(fn(BukuTamu $record): string => ViewPengantarBerkas::getUrl(['record' => $record->id]))
      ->recordActions([
        ActionGroup::make([
          Action::make('view_detail')
            ->label('Lihat Detail')
            ->icon('heroicon-o-eye')
            ->color('primary')
            ->url(fn(BukuTamu $record): string => ViewPengantarBerkas::getUrl(['record' => $record->id])),
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
            ->modalHeading('Hapus Data Pengantar Berkas')
            ->modalDescription(fn(BukuTamu $record): string => static::hasDeletePasswordVerification()
              ? "Data '{$record->nama_lengkap}' akan dihapus permanen."
              : "Data '{$record->nama_lengkap}' akan dihapus permanen. Verifikasi password hanya diminta sekali per sesi login.")
            ->schema(fn(): array => static::hasDeletePasswordVerification()
              ? []
              : [
                Forms\Components\TextInput::make('password')
                  ->label('Password Super Admin')
                  ->password()
                  ->required()
                  ->autocomplete('current-password')
                  ->helperText('Ketik password akun Super Admin. Verifikasi ini hanya diminta sekali per sesi login.'),
              ])
            ->action(function (array $data, BukuTamu $record): void {
              static::verifyDeletePasswordForSession($data);

              $nama = $record->nama_lengkap;
              $record->delete();

              Notification::make()
                ->title('Data berhasil dihapus')
                ->body("Data pengantar berkas '{$nama}' telah dihapus.")
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
            ->modalHeading('Hapus Data Pengantar Berkas Terpilih')
            ->modalDescription(fn(): string => static::hasDeletePasswordVerification()
              ? 'Semua data pengantar berkas yang dipilih akan dihapus permanen.'
              : 'Semua data pengantar berkas yang dipilih akan dihapus permanen. Verifikasi password hanya diminta sekali per sesi login.')
            ->schema(fn(): array => static::hasDeletePasswordVerification()
              ? []
              : [
                Forms\Components\TextInput::make('password')
                  ->label('Password Super Admin')
                  ->password()
                  ->required()
                  ->autocomplete('current-password')
                  ->helperText('Ketik password akun Super Admin. Verifikasi ini hanya diminta sekali per sesi login.'),
              ])
            ->action(function (array $data, $records): void {
              static::verifyDeletePasswordForSession($data);

              $count = $records->count();
              $records->each(fn(BukuTamu $record): bool => $record->delete());

              Notification::make()
                ->title('Data berhasil dihapus')
                ->body("{$count} data pengantar berkas berhasil dihapus.")
                ->success()
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
        ]),
      ]);
  }

  protected static function pengantarBerkasQuery(): Builder
  {
    return BukuTamu::query()
      ->whereNotNull('foto_penerimaan')
      ->where('foto_penerimaan', '!=', '');
  }

  protected static function deletePasswordVerificationSessionKey(): string
  {
    return 'pengantar_berkas.delete_password_verified_user_id';
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
}
