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
        return $user && $user->hasRole('Super Admin');
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
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('role_user.name'),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('email_verified_at')->sortable(),
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
                ])
                    ->label(false)
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button()
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
