<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\RoleUser;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'user';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengguna';
    protected static ?string $navigationLabel = 'User';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->role_user && $user->role_user->hasPermission('user_management');
    }

    public static function canDelete($record): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) ($user && $user->hasRole('Super Admin'));
    }

    public static function canDeleteAny(): bool
    {
        return static::canDelete(null);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make('Panduan Pembuatan User')
                    ->description('Gunakan panduan singkat berikut agar data akun konsisten dan aman.')
                    ->icon('heroicon-o-information-circle')
                    ->columnSpanFull()
                    ->schema([
                        SchemaView::make('filament.resources.user-resource.partials.user-create-guide')
                            ->columnSpanFull(),
                    ]),
                Section::make('Informasi User')
                    ->description('Isi data identitas akun dan tentukan role akses pengguna.')
                    ->icon('heroicon-o-identification')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->prefixIcon('heroicon-o-user')
                            ->placeholder('Contoh: Hj. Siti Nurhaliza, S.Pd., M.M.')
                            ->helperText('Nama ini akan tampil di daftar user, aktivitas, dan profil akun.')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->prefixIcon('heroicon-o-envelope')
                            ->placeholder('contoh@cadisdik13.id')
                            ->helperText('Digunakan sebagai identitas login. Format bebas dan tidak memerlukan verifikasi email.')
                            ->required()
                            ->unique(ignorable: fn(?User $record): ?User => $record),
                        Select::make('role_user_id')
                            ->label('Role user')
                            ->prefixIcon('heroicon-o-shield-check')
                            ->placeholder('Pilih role akses')
                            ->required()
                            ->options(fn(?User $record): array => static::roleUserOptions($record))
                            ->preload()
                            ->searchable()
                            ->helperText(function (?User $record): ?string {
                                $baseText = 'Role menentukan hak akses menu dan fitur yang bisa digunakan user.';

                                if (! static::isKepalaCabdinRoleLocked($record)) {
                                    return $baseText;
                                }

                                $kepalaRoleId = static::kepalaCabdinRoleId();

                                if ($record && $kepalaRoleId && (string) $record->role_user_id === (string) $kepalaRoleId) {
                                    return $baseText;
                                }

                                return $baseText . ' Role Kepala Cabang Dinas sudah digunakan oleh user lain.';
                            })
                            ->columnSpanFull(),
                    ]),
                Section::make('Keamanan')
                    ->description('Atur kata sandi akun pengguna sesuai standar keamanan.')
                    ->icon('heroicon-o-lock-closed')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        SchemaView::make('filament.resources.user-resource.partials.password-security-note')
                            ->columnSpanFull(),
                        TextInput::make('password')
                            ->label('Password')
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->placeholder('Masukkan password aman')
                            ->helperText('Password wajib diisi saat membuat user baru.')
                            ->password()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->revealable()
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->same('passwordConfirmation'),
                        TextInput::make('passwordConfirmation')
                            ->label('Konfirmasi Password')
                            ->prefixIcon('heroicon-o-check-badge')
                            ->placeholder('Ulangi password')
                            ->helperText('Harus sama persis dengan password di atas.')
                            ->password()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->revealable()
                            ->dehydrated(false),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query
                ->where(function (Builder $userQuery): void {
                    $userQuery->whereDoesntHave('role_user', function (Builder $roleQuery): void {
                        $roleQuery->where('name', 'Super Admin');
                    })
                    ->orWhereNull('role_user_id');
                }))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role_user.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Super Admin' => 'danger',
                        'Kepala Cabang Dinas' => 'warning',
                        'Piket' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('email_verified_at')
                    ->label('Terverifikasi')
                    ->since()
                    ->color('gray')
                    ->tooltip(fn($record) => $record->email_verified_at ? \Carbon\Carbon::parse($record->email_verified_at)->format('d/m/Y H:i') : '-')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat')
                        ->icon('heroicon-o-eye')
                        ->color('primary')
                        ->extraAttributes([
                            'style' => 'padding: 10px 16px !important;',
                        ]),
                    EditAction::make()
                        ->label('Edit')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->extraAttributes([
                            'style' => 'padding: 10px 16px !important;',
                        ]),
                    Action::make('delete')
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->extraAttributes([
                            'style' => 'padding: 10px 16px !important;',
                        ])
                        ->hidden(fn(): bool => !static::canDelete(null))
                        ->disabled(fn(User $record): bool => !$record->isDeletable())
                        ->tooltip(function (User $record): ?string {
                            if ($record->isDeletable()) {
                                return null;
                            }

                            if ($record->hasRole('Super Admin')) {
                                return 'User dengan role Super Admin tidak dapat dihapus.';
                            }

                            return 'Minimal harus ada 1 user dengan role ' . ($record->role_user->name ?? 'ini') . '.';
                        })
                        ->modalHeading('Hapus User')
                        ->modalDescription(fn(User $record): string => static::hasDeletePasswordVerification()
                            ? "User '{$record->name}' akan dihapus permanen."
                            : "User '{$record->name}' akan dihapus permanen. Verifikasi password hanya diminta sekali per sesi login.")
                        ->schema(fn(): array => static::hasDeletePasswordVerification()
                            ? []
                            : [
                                TextInput::make('password')
                                    ->label('Password Akun Login')
                                    ->password()
                                    ->required()
                                    ->autocomplete('current-password')
                                    ->helperText('Ketik password akun yang sedang login untuk konfirmasi penghapusan.'),
                            ])
                        ->requiresConfirmation()
                        ->modalSubmitActionLabel('Hapus')
                        ->action(function (array $data, User $record): void {
                            static::verifyDeletePasswordForSession($data);

                            if (! $record->isDeletable()) {
                                $reason = $record->hasRole('Super Admin')
                                    ? 'User dengan role Super Admin tidak dapat dihapus.'
                                    : 'Minimal harus ada 1 user dengan role ' . ($record->role_user->name ?? 'ini') . '.';

                                Notification::make()
                                    ->danger()
                                    ->title('Tidak dapat menghapus user')
                                    ->body($reason)
                                    ->send();

                                return;
                            }

                            $name = $record->name;
                            $record->delete();

                            Notification::make()
                                ->title('User berhasil dihapus')
                                ->body('User ' . $name . ' berhasil dihapus.')
                                ->success()
                                ->send();
                        }),
                    Action::make('resetPassword')
                        ->label('Reset Password')
                        ->icon('heroicon-o-key')
                        ->color('gray')
                        ->extraAttributes([
                            'style' => 'padding: 10px 16px !important;',
                        ])
                        ->schema([
                            TextInput::make('password')
                                ->label('Password Baru')
                                ->password()
                                ->revealable()
                                ->required()
                                ->minLength(8)
                                ->same('passwordConfirmation'),
                            TextInput::make('passwordConfirmation')
                                ->label('Konfirmasi Password Baru')
                                ->password()
                                ->revealable()
                                ->required()
                                ->dehydrated(false),
                        ])
                        ->modalHeading(fn(User $record): string => 'Reset password: ' . $record->name)
                        ->modalSubmitActionLabel('Simpan')
                        ->action(function (User $record, array $data): void {
                            $record->update([
                                'password' => Hash::make((string) ($data['password'] ?? '')),
                            ]);

                            Notification::make()
                                ->title('Password berhasil direset')
                                ->body('Password untuk user ' . $record->name . ' berhasil diperbarui.')
                                ->success()
                                ->send();
                        }),
                ])
                    ->label(false)
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->toolbarActions([]);
    }

    public static function validateSingleKepalaCabdin(
        ?string $selectedRoleId,
        ?string $ignoreUserId = null,
        ?string $currentRoleId = null,
    ): void
    {
        $kepalaRoleId = static::kepalaCabdinRoleId();

        if (! $selectedRoleId || ! $kepalaRoleId || $selectedRoleId !== (string) $kepalaRoleId) {
            return;
        }

        if ($currentRoleId && $currentRoleId === $selectedRoleId) {
            return;
        }

        $query = User::query()->where('role_user_id', $kepalaRoleId);

        if ($ignoreUserId) {
            $query->where('id', '!=', $ignoreUserId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'role_user_id' => 'User dengan role Kepala Cabang Dinas sudah ada. Tidak dapat menambahkan lebih dari satu user.',
            ]);
        }
    }

    protected static function roleUserOptions(?User $record = null): array
    {
        $kepalaRoleId = static::kepalaCabdinRoleId();
        $isCurrentRecordKepala = $record && $kepalaRoleId && (string) $record->role_user_id === (string) $kepalaRoleId;
        $query = RoleUser::query();

        if (static::isKepalaCabdinRoleLocked($record) && ! $isCurrentRecordKepala) {
            $query->where('name', '!=', 'Kepala Cabang Dinas');
        }

        return $query
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected static function isKepalaCabdinRoleLocked(?User $record = null): bool
    {
        $kepalaRoleId = static::kepalaCabdinRoleId();

        if (! $kepalaRoleId) {
            return false;
        }

        $query = User::query()->where('role_user_id', $kepalaRoleId);

        if ($record?->id) {
            $query->where('id', '!=', $record->id);
        }

        return $query->exists();
    }

    protected static function kepalaCabdinRoleId(): ?string
    {
        return RoleUser::query()
            ->where('name', 'Kepala Cabang Dinas')
            ->value('id');
    }

    protected static function deletePasswordVerificationSessionKey(): string
    {
        return 'user_management.delete_password_verified_user_id';
    }

    protected static function hasDeletePasswordVerification(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return (int) session(static::deletePasswordVerificationSessionKey()) === (int) $user->id;
    }

    protected static function verifyDeletePasswordForSession(array $data): void
    {
        /** @var User|null $user */
        $user = Auth::user();

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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
