<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PegawaiPiketResource\Pages;
use App\Models\DropdownOption;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PegawaiPiketResource extends Resource
{
    protected static ?string $model = DropdownOption::class;

    protected static ?string $slug = 'pegawai-piket';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Data Pegawai Piket';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?string $modelLabel = 'Pegawai Piket';
    protected static ?string $pluralModelLabel = 'Data Pegawai Piket';
    protected static ?int $navigationSort = 11;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->role_user && $user->role_user->hasPermission('pegawai_piket');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Pegawai Piket')
                ->description('Data pegawai yang bertugas sebagai petugas piket penerima tamu.')
                ->schema([
                    Forms\Components\Hidden::make('category'),
                    Forms\Components\TextInput::make('label')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Nama lengkap pegawai (contoh: Drs. H. Ahmad Suryadi, M.Pd.)'),
                    Forms\Components\TextInput::make('value')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Otomatis terisi dari nama...')
                        ->helperText('ID internal untuk database. Otomatis terisi dari nama, bisa diubah jika perlu.'),
                    Forms\Components\Toggle::make('is_active')
                        ->helperText('Nonaktifkan untuk menyembunyikan pegawai dari dropdown tanpa menghapus data.'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('value')
                    ->label('ID Internal')
                    ->searchable()
                    ->toggleable()
                    ->limit(40),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->since()
                    ->color('gray')
                    ->tooltip(fn($record) => $record->updated_at?->format('d/m/Y H:i'))
                    ->sortable(),
            ])
            ->defaultSort('label')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50])
            ->actionsColumnLabel('')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPegawaiPikets::route('/'),
            'create' => Pages\CreatePegawaiPiket::route('/create'),
            'edit' => Pages\EditPegawaiPiket::route('/{record}/edit'),
        ];
    }
}
