<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataPegawaiResource\Pages;
use App\Models\Pegawai;
use App\Models\RoleUser;
use App\Models\User;
use App\Support\LoginEmailNormalizer;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;

class DataPegawaiResource extends Resource
{
  protected static ?string $model = Pegawai::class;

  protected static ?string $slug = 'data-pegawai';
  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
  protected static ?string $navigationLabel = 'Data Pegawai';
  protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
  protected static ?string $modelLabel = 'Pegawai';
  protected static ?string $pluralModelLabel = 'Data Pegawai';
  protected static ?int $navigationSort = 12;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Filament::auth()->user();
    return $user && $user->role_user && $user->role_user->hasPermission('data_pegawai');
  }

  public static function canViewAny(): bool
  {
    return static::shouldRegisterNavigation();
  }

  public static function form(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Informasi Pegawai')
        ->description('Data lengkap pegawai untuk otomatisasi formulir izin.')
        ->columnSpanFull()
        ->columns(2)
        ->schema([
          Forms\Components\TextInput::make('nama')
            ->label('Nama Lengkap')
            ->required()
            ->maxLength(255)
            ->placeholder('Contoh: Drs. H. Ahmad Suryadi, M.Pd.'),
          Forms\Components\TextInput::make('nip')
            ->label('NIP')
            ->validationAttribute('NIP')
            ->validationMessages([
              'unique' => 'NIP sudah digunakan.',
            ])
            ->required()
            ->unique(ignoreRecord: true)
            ->minLength(18)
            ->maxLength(18)
            ->placeholder('Masukkan 18 digit NIP')
            ->mask('999999999999999999')
            ->suffixIcon(function ($state) {
              if (!$state) return null;
              return strlen($state) === 18 ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle';
            })
            ->suffixIconColor(function ($state) {
              if (!$state) return null;
              return strlen($state) === 18 ? 'success' : 'danger';
            })
            ->helperText(function ($state) {
              if (!$state) return 'NIP harus tepat 18 digit angka';
              $length = strlen($state);
              $status = $length === 18 ? '✓ sesuai' : '✗ belum lengkap';
              return "{$length}/18 digit — {$status}";
            }),
          Forms\Components\TextInput::make('email')
            ->label('Email Login')
            ->email()
            ->maxLength(255)
            ->placeholder('contoh: nama.pegawai@cadisdik13.local')
            ->helperText('Jika kosong, sistem akan membuat email dummy dari nama secara otomatis.'),
          Forms\Components\TextInput::make('jabatan')
            ->label('Jabatan')
            ->required()
            ->maxLength(255)
            ->placeholder('Contoh: Kepala Cabang Dinas'),
          Forms\Components\TextInput::make('unit_kerja')
            ->label('Unit Kerja')
            ->required()
            ->maxLength(255)
            ->placeholder('Contoh: Sub Bagian Tata Usaha'),
          Forms\Components\TextInput::make('nomor_hp')
            ->label('Nomor HP')
            ->tel()
            ->prefix('+62')
            ->placeholder('8xx-xxxx-xxxx')
            ->mask('999-9999-99999')
            ->maxLength(15)
            ->helperText('Format: 8xx-xxxx-xxxx (tanpa 0 di depan)'),
          Forms\Components\Toggle::make('is_active')
            ->label('Status Aktif')
            ->helperText('Nonaktifkan jika pegawai sudah tidak bertugas.')
            ->inline(false),
        ]),
      Section::make('Akun Login (Opsional)')
        ->description('Isi password baru jika ingin mengubah password akun login pegawai dari halaman ini.')
        ->columnSpanFull()
        ->columns(2)
        ->schema([
          Forms\Components\TextInput::make('login_password')
            ->label('Password Login Baru')
            ->password()
            ->revealable()
            ->dehydrated(false)
            ->nullable()
            ->minLength(8)
            ->same('login_password_confirmation')
            ->helperText('Kosongkan saat create untuk memakai password default sesuai role (staff123/piket123/kepalakcd123). Pada edit, kosongkan jika tidak ingin mengubah password akun.'),
          Forms\Components\TextInput::make('login_password_confirmation')
            ->label('Konfirmasi Password Baru')
            ->password()
            ->revealable()
            ->dehydrated(false)
            ->nullable()
            ->minLength(8),
        ]),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('nama')
          ->label('Nama Lengkap')
          ->searchable()
          ->sortable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('nip')
          ->label('NIP')
          ->searchable()
          ->copyable()
          ->fontFamily('mono'),
        Tables\Columns\TextColumn::make('email')
          ->label('Email Login')
          ->searchable()
          ->toggleable()
          ->copyable(),
        Tables\Columns\TextColumn::make('jabatan')
          ->label('Jabatan')
          ->searchable()
          ->limit(30)
          ->tooltip(fn($record) => $record->jabatan),
        Tables\Columns\TextColumn::make('unit_kerja')
          ->label('Unit Kerja')
          ->searchable()
          ->toggleable()
          ->limit(30)
          ->tooltip(fn($record) => $record->unit_kerja),
        Tables\Columns\TextColumn::make('nomor_hp')
          ->label('No. HP')
          ->formatStateUsing(function ($state) {
            if (!$state) return '-';

            $cleaned = preg_replace('/[^0-9]/', '', (string) $state);

            if (!$cleaned) return '-';

            if (str_starts_with($cleaned, '62')) {
              return '0' . substr($cleaned, 2);
            }

            if (str_starts_with($cleaned, '8')) {
              return '0' . $cleaned;
            }

            if (str_starts_with($cleaned, '0')) {
              return $cleaned;
            }

            return '0' . $cleaned;
          })
          ->toggleable(),
        Tables\Columns\IconColumn::make('is_active')
          ->label('Status')
          ->boolean()
          ->trueIcon('heroicon-o-check-circle')
          ->falseIcon('heroicon-o-x-circle')
          ->trueColor('success')
          ->falseColor('danger')
          ->alignCenter(),
      ])
      ->defaultSort('nama')
      ->defaultPaginationPageOption(25)
      ->paginationPageOptions([10, 25, 50])
      ->recordActionsColumnLabel('')
      ->filters([
        Tables\Filters\TernaryFilter::make('is_active')
          ->label('Status')
          ->placeholder('Semua')
          ->trueLabel('Aktif')
          ->falseLabel('Nonaktif'),
      ])
      ->recordActions([
        ActionGroup::make([
          EditAction::make()
            ->label('Edit')
            ->icon('heroicon-o-pencil-square')
            ->color('warning'),
          Action::make('reset_default_password')
            ->label('Reset Password')
            ->icon('heroicon-o-key')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Reset Password Pegawai')
            ->modalDescription(fn(Pegawai $record) => 'Password akun login untuk ' . $record->nama . ' akan direset ke default sesuai role.')
            ->modalSubmitActionLabel('Reset Password')
            ->action(function (Pegawai $record): void {
              $result = static::resetPasswordForPegawaiRecord($record);

              if (! $result['updated']) {
                Notification::make()
                  ->warning()
                  ->title('Reset password dibatalkan')
                  ->body($result['message'])
                  ->send();

                return;
              }

              Notification::make()
                ->success()
                ->title('Password berhasil direset')
                ->body($result['message'])
                ->send();
            }),
          DeleteAction::make()
            ->label('Hapus')
            ->icon('heroicon-o-trash')
            ->color('danger'),
        ])
          ->label(false)
          ->icon('heroicon-m-ellipsis-vertical')
          ->color('gray'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          BulkAction::make('bulk_activate')
            ->label('Aktifkan Terpilih')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Aktifkan Pegawai Terpilih')
            ->modalDescription('Semua data pegawai yang dipilih akan diaktifkan.')
            ->modalSubmitActionLabel('Aktifkan')
            ->action(function ($records): void {
              $count = 0;

              $records->each(function (Pegawai $record) use (&$count): void {
                if (! $record->is_active) {
                  $record->update(['is_active' => true]);
                  $count++;
                }
              });

              Notification::make()
                ->success()
                ->title('Bulk update selesai')
                ->body($count . ' data pegawai berhasil diaktifkan.')
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
          BulkAction::make('bulk_deactivate')
            ->label('Nonaktifkan Terpilih')
            ->icon('heroicon-o-no-symbol')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Nonaktifkan Pegawai Terpilih')
            ->modalDescription('Semua data pegawai yang dipilih akan dinonaktifkan.')
            ->modalSubmitActionLabel('Nonaktifkan')
            ->action(function ($records): void {
              $count = 0;

              $records->each(function (Pegawai $record) use (&$count): void {
                if ($record->is_active) {
                  $record->update(['is_active' => false]);
                  $count++;
                }
              });

              Notification::make()
                ->success()
                ->title('Bulk update selesai')
                ->body($count . ' data pegawai berhasil dinonaktifkan.')
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
          BulkAction::make('bulk_reset_password_default')
            ->label('Reset Password Terpilih')
            ->icon('heroicon-o-key')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Reset Password Data Pegawai Terpilih')
            ->modalDescription('Password akun login dari data pegawai yang dipilih akan direset ke default sesuai role masing-masing akun.')
            ->modalSubmitActionLabel('Reset Password')
            ->action(function (Collection $records): void {
              $result = static::resetPasswordsForPegawaiRecords($records);

              if ($result['updated'] === 0) {
                Notification::make()
                  ->warning()
                  ->title('Reset password dibatalkan')
                  ->body('Tidak ada akun yang berhasil direset. Pastikan data pegawai terhubung ke akun login.')
                  ->send();

                return;
              }

              $message = $result['updated'] . ' akun berhasil direset.';

              if ($result['skipped'] > 0) {
                $message .= ' ' . $result['skipped'] . ' data dilewati karena akun tidak ditemukan atau role tidak didukung.';
              }

              Notification::make()
                ->success()
                ->title('Bulk reset password selesai')
                ->body($message)
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
          BulkAction::make('bulk_delete')
            ->label('Hapus Terpilih')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Hapus Data Pegawai Terpilih')
            ->modalDescription('Semua data pegawai yang dipilih akan dihapus permanen.')
            ->modalSubmitActionLabel('Hapus')
            ->action(function ($records): void {
              $count = $records->count();
              $records->each->delete();

              Notification::make()
                ->success()
                ->title('Bulk hapus selesai')
                ->body($count . ' data pegawai berhasil dihapus.')
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
        ]),
      ]);
  }

  protected static function resetPasswordForPegawaiRecord(Pegawai $record): array
  {
    $resolved = static::resolveDefaultCredentialByPegawai($record);

    if (! $resolved['allowed']) {
      return [
        'updated' => false,
        'message' => $resolved['message'],
      ];
    }

    /** @var RoleUser|null $targetRole */
    $targetRole = RoleUser::query()
      ->where('name', $resolved['role_name'])
      ->first();

    if (! $targetRole) {
      return [
        'updated' => false,
        'message' => 'Role ' . $resolved['role_name'] . ' tidak ditemukan. Reset password dibatalkan.',
      ];
    }

    $user = static::resolveUserFromPegawai($record);
    $resolvedEmail = static::resolveUniqueUserEmailForPegawai($record, $user?->id);

    if (! $user) {
      $createdUser = User::query()->create([
        'name' => (string) ($record->nama ?: 'Pegawai'),
        'email' => $resolvedEmail,
        'password' => Hash::make($resolved['password']),
        'role_user_id' => $targetRole->id,
        'pegawai_id' => $record->id,
      ]);

      return [
        'updated' => true,
        'message' => 'Akun login tidak ditemukan, akun baru dibuat dengan email ' . $createdUser->email . ', role ' . $resolved['role_name'] . ', dan password default ' . $resolved['password'] . '.',
      ];
    }

    $user->update([
      'name' => (string) ($record->nama ?: $user->name),
      'email' => $resolvedEmail,
      'password' => Hash::make($resolved['password']),
      'role_user_id' => $targetRole->id,
      'pegawai_id' => $record->id,
    ]);

    return [
      'updated' => true,
      'message' => 'Akun ' . $user->email . ' diset sebagai role ' . $resolved['role_name'] . ' dengan password default ' . $resolved['password'] . '.',
    ];
  }

  protected static function resetPasswordsForPegawaiRecords(Collection $records): array
  {
    $updated = 0;
    $skipped = 0;

    /** @var Pegawai $record */
    foreach ($records as $record) {
      $result = static::resetPasswordForPegawaiRecord($record);

      if ($result['updated']) {
        $updated++;
      } else {
        $skipped++;
      }
    }

    return [
      'updated' => $updated,
      'skipped' => $skipped,
    ];
  }

  protected static function resolveUserFromPegawai(Pegawai $pegawai): ?User
  {
    $user = User::query()
      ->where('pegawai_id', $pegawai->id)
      ->first();

    if ($user) {
      return $user;
    }

    $normalizedEmail = strtolower(trim((string) ($pegawai->email ?? '')));

    if ($normalizedEmail !== '') {
      return User::query()
        ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
        ->first();
    }

    return null;
  }

  protected static function resolveDefaultCredentialByPegawai(Pegawai $pegawai): array
  {
    $jabatan = strtolower(trim((string) ($pegawai->jabatan ?? '')));
    $roleName = 'Staff';

    if ($jabatan !== '' && str_contains($jabatan, 'kepala cabang')) {
      $roleName = 'Kepala Cabang Dinas';
    } elseif ($jabatan !== '' && str_contains($jabatan, 'piket')) {
      $roleName = 'Piket';
    }

    return match ($roleName) {
      'Staff' => [
        'allowed' => true,
        'role_name' => 'Staff',
        'password' => 'staff123',
      ],
      'Piket' => [
        'allowed' => true,
        'role_name' => 'Piket',
        'password' => 'piket123',
      ],
      'Kepala Cabang Dinas' => [
        'allowed' => true,
        'role_name' => 'Kepala Cabang Dinas',
        'password' => 'kepalakcd123',
      ],
      default => [
        'allowed' => false,
        'password' => '',
        'role_name' => '',
        'message' => 'Role default tidak bisa ditentukan dari jabatan pegawai.',
      ],
    };
  }

  protected static function resolveUniqueUserEmailForPegawai(Pegawai $pegawai, ?string $ignoreUserId = null): string
  {
    $normalized = LoginEmailNormalizer::sanitizePreferredEmail($pegawai->email, $pegawai->nama, 'user');

    if ($normalized === '' || ! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
      $normalized = LoginEmailNormalizer::localPartFromName($pegawai->nama, 'user') . '@' . LoginEmailNormalizer::INTERNAL_DOMAIN;
    }

    [$localPart, $domain] = array_pad(explode('@', $normalized, 2), 2, LoginEmailNormalizer::INTERNAL_DOMAIN);
    $localPart = $localPart !== '' ? $localPart : 'user';
    $domain = $domain !== '' ? $domain : LoginEmailNormalizer::INTERNAL_DOMAIN;

    $counter = 0;

    do {
      $suffix = $counter > 0 ? '.' . $counter : '';
      $candidate = $localPart . $suffix . '@' . $domain;
      $exists = User::query()
        ->when($ignoreUserId !== null, fn($query) => $query->where('id', '!=', $ignoreUserId))
        ->where('email', $candidate)
        ->exists();
      $counter++;
    } while ($exists);

    return $candidate;
  }

  public static function getRelations(): array
  {
    return [];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListDataPegawai::route('/'),
      'create' => Pages\CreateDataPegawai::route('/create'),
      'edit' => Pages\EditDataPegawai::route('/{record}/edit'),
    ];
  }
}
