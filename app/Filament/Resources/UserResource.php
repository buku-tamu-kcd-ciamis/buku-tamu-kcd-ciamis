<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput as FormsTextInput;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'user';
    protected static ?string $navigationGroup = 'Pengguna';
    protected static ?string $navigationLabel = 'User';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->role_user && $user->role_user->hasPermission('user_management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General')
                    ->description('User General Data')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true),
                                TextInput::make('email')
                                    ->required(fn(string $operation): bool => $operation === 'create')
                                    ->unique(ignorable: fn(?User $record): ?User => $record)
                                    ->disabled(fn(string $operation): bool => $operation === 'edit')
                                    ->live(onBlur: true)
                            ]),
                        Select::make('role_user_id')
                            ->required()
                            ->relationship('role_user', 'name'),
                    ])
                    ->aside(),
                Section::make('Privacy')
                    ->description('this is description')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('password')
                                    ->password()
                                    ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                                    ->dehydrated(fn(?string $state): bool => filled($state))
                                    ->required(fn(string $operation): bool => $operation === 'create')
                                    ->hidden(fn(string $operation): bool => $operation === 'view')
                                    ->revealable()
                                    ->same('passwordConfirmation'),
                                TextInput::make('passwordConfirmation')
                                    ->password()
                                    ->required(fn(string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn(?string $state): bool => filled($state))
                                    ->revealable()
                            ]),
                    ])
                    ->aside(),
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
                        'Ketua KCD' => 'warning',
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
                    ->tooltip(fn($record) => $record->email_verified_at ? \Carbon\Carbon::parse($record->email_verified_at)->format('d/m/Y H:i') : '-')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail')
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->icon('heroicon-o-pencil-square'),
                    Tables\Actions\Action::make('resetPassword')
                        ->label('Reset Password')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->form([
                            FormsTextInput::make('new_password')
                                ->label('Password Baru')
                                ->password()
                                ->required()
                                ->revealable()
                                ->minLength(8)
                                ->same('new_password_confirmation'),
                            FormsTextInput::make('new_password_confirmation')
                                ->label('Konfirmasi Password')
                                ->password()
                                ->required()
                                ->revealable()
                                ->dehydrated(false),
                        ])
                        ->action(function (User $record, array $data): void {
                            $record->update([
                                'password' => Hash::make($data['new_password']),
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Password berhasil direset!')
                                ->body('Password untuk ' . $record->name . ' telah diperbarui.')
                                ->send();

                            activity()
                                ->performedOn($record)
                                ->causedBy(Auth::user())
                                ->event('reset_password')
                                ->log('Password user ' . $record->name . ' direset');
                        })
                        ->modalHeading('Reset Password')
                        ->modalSubmitActionLabel('Reset Password')
                        ->modalWidth('md'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->hidden(fn(User $record): bool => !$record->isDeletable())
                        ->before(function (Tables\Actions\DeleteAction $action, User $record) {
                            if (!$record->isDeletable()) {
                                $reason = $record->hasRole('Super Admin')
                                    ? 'User dengan role Super Admin tidak dapat dihapus.'
                                    : 'Minimal harus ada 1 user dengan role ' . ($record->role_user->name ?? 'ini') . '. Ini adalah satu-satunya user dengan role tersebut.';

                                Notification::make()
                                    ->danger()
                                    ->title('Tidak dapat menghapus user!')
                                    ->body($reason)
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ])
                    ->label(false)
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button()
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                        // Check each record for deletability
                        $undeletable = $records->filter(fn(User $record) => !$record->isDeletable());

                        if ($undeletable->isNotEmpty()) {
                            $hasSuperAdmin = $undeletable->contains(fn(User $r) => $r->hasRole('Super Admin'));

                            if ($hasSuperAdmin) {
                                Notification::make()
                                    ->danger()
                                    ->title('Tidak dapat menghapus!')
                                    ->body('Terdapat Super Admin dalam pilihan. Super Admin tidak dapat dihapus.')
                                    ->send();
                            } else {
                                // Check if deleting would leave any role without users
                                $roleIds = $records->pluck('role_user_id')->unique();
                                $affectedRoles = [];

                                foreach ($roleIds as $roleId) {
                                    $selectedCount = $records->where('role_user_id', $roleId)->count();
                                    $totalCount = User::where('role_user_id', $roleId)->count();

                                    if ($selectedCount >= $totalCount) {
                                        $affectedRoles[] = $records->firstWhere('role_user_id', $roleId)->role_user->name ?? 'role ini';
                                    }
                                }

                                Notification::make()
                                    ->danger()
                                    ->title('Tidak dapat menghapus!')
                                    ->body('Minimal harus ada 1 user dengan role: ' . implode(', ', $affectedRoles) . '. Penghapusan ini akan menghapus semua user dengan role tersebut.')
                                    ->send();
                            }

                            $action->cancel();
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
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
