<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleUserResource\Pages;
use App\Filament\Resources\RoleUserResource\RelationManagers;
use App\Models\RoleUser;
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
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RoleUserResource extends Resource
{
    protected static ?string $model = RoleUser::class;
    protected static ?string $label = 'Role';

    protected static ?string $slug = 'role';
    protected static ?string $navigationGroup = 'Pengguna';
    protected static ?string $navigationLabel = 'Role';
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General')
                    ->description('Role User General Data')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true),
                            ]),
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
                    ->formatStateUsing(fn(string $state): string => RoleUser::APPROVE_STATUS[$state])
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
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListRoleUsers::route('/'),
            'create' => Pages\CreateRoleUser::route('/create'),
            'edit' => Pages\EditRoleUser::route('/{record}/edit'),
        ];
    }
}
