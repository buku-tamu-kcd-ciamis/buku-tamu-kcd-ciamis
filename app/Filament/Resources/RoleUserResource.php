<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleUserResource\Pages;
use App\Filament\Resources\RoleUserResource\RelationManagers;
use App\Models\RoleUser;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Facades\Filament;
use App\Models\User;

class RoleUserResource extends Resource
{
    protected static ?string $model = RoleUser::class;
    protected static ?string $label = 'Role';

    protected static ?string $slug = 'role';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengguna';
    protected static ?string $navigationLabel = 'Role';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        return (bool) ($user && $user->hasRole('Super Admin'));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->description('Role User General Data')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        Select::make('need_approval')
                            ->required()
                            ->options(RoleUser::APPROVE_STATUS),
                    ])
                    ->aside()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('need_approval')
                    ->formatStateUsing(function (?string $state): string {
                        if (blank($state)) {
                            return '-';
                        }

                        return RoleUser::APPROVE_STATUS[$state] ?? (string) $state;
                    })
            ])
            ->filters([
                //
            ])
            ->recordActions([])
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
            'index' => Pages\ListRoleUsers::route('/'),
            'create' => Pages\CreateRoleUser::route('/create'),
            'edit' => Pages\EditRoleUser::route('/{record}/edit'),
        ];
    }
}
