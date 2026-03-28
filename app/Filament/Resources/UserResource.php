<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
            ->components([
                Section::make('Informasi User')
                    ->description('Data utama akun pengguna.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignorable: fn(?User $record): ?User => $record),
                        Select::make('role_user_id')
                            ->label('Role user')
                            ->required()
                            ->relationship('role_user', 'name')
                            ->preload()
                            ->searchable(),
                    ]),
                Section::make('Keamanan')
                    ->description('Atur kata sandi akun pengguna.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->revealable()
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->same('passwordConfirmation'),
                        TextInput::make('passwordConfirmation')
                            ->label('Konfirmasi Password')
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
                        ->color('primary'),
                    EditAction::make()
                        ->label('Edit')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning'),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
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
                        }),
                    Action::make('resetPassword')
                        ->label('Reset Password')
                        ->icon('heroicon-o-key')
                        ->color('gray')
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
