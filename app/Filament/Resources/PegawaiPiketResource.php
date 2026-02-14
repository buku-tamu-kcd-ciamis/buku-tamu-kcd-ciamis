<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PegawaiPiketResource\Pages;
use App\Models\DropdownOption;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PegawaiPiketResource extends Resource
{
    protected static ?string $model = DropdownOption::class;

    protected static ?string $slug = 'pegawai-piket';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Data Pegawai Piket';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $modelLabel = 'Pegawai Piket';
    protected static ?string $pluralModelLabel = 'Data Pegawai Piket';
    protected static ?int $navigationSort = 11;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->hasRole('Super Admin');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pegawai Piket')
                ->description('Data pegawai yang bertugas sebagai petugas piket penerima tamu.')
                ->schema([
                    Forms\Components\Hidden::make('category')
                        ->default(DropdownOption::CATEGORY_PEGAWAI_PIKET),
                    Forms\Components\TextInput::make('label')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get, string $operation) {
                            if ($operation === 'create' && empty($get('value'))) {
                                $set('value', $state);
                            }
                        })
                        ->helperText('Nama lengkap pegawai (contoh: Drs. H. Ahmad Suryadi, M.Pd.)')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('value')
                        ->label('Nilai (ID Internal)')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Otomatis terisi dari nama...')
                        ->helperText('ID internal untuk database. Otomatis terisi dari nama, bisa diubah jika perlu.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Urutan')
                        ->numeric()
                        ->default(
                            fn(Forms\Get $get, string $operation) =>
                            $operation === 'create'
                                ? (DropdownOption::where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)->max('sort_order') ?? 0) + 1
                                : 0
                        )
                        ->placeholder('Otomatis diurutkan...')
                        ->helperText('Urutan tampil dalam dropdown (kecil = lebih atas).'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
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
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter(),
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
                    ->tooltip(fn($record) => $record->updated_at?->format('d/m/Y H:i'))
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Pegawai Piket')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data ini? Data yang dihapus tidak dapat dikembalikan.')
                        ->successNotificationTitle('Data berhasil dihapus'),
                ])
                    ->label(false)
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->iconButton()
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data yang dipilih? Data yang dihapus tidak dapat dikembalikan.')
                        ->successNotificationTitle('Data berhasil dihapus'),
                ]),
            ]);
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
