<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Log Aktivitas';
    protected static ?string $navigationGroup = 'Bantuan';
    protected static ?string $modelLabel = 'Log Aktivitas';
    protected static ?string $pluralModelLabel = 'Log Aktivitas';
    protected static ?int $navigationSort = 98;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('log_name')
                    ->label('Nama Log'),
                Forms\Components\TextInput::make('description')
                    ->label('Deskripsi'),
                Forms\Components\TextInput::make('subject_type')
                    ->label('Tipe Subject'),
                Forms\Components\TextInput::make('subject_id')
                    ->label('ID Subject'),
                Forms\Components\Textarea::make('properties')
                    ->label('Properties')
                    ->rows(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->searchable()
                    ->default('System'),
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Kategori')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'buku_tamu' => 'Buku Tamu',
                        'pegawai_izin' => 'Izin Pegawai',
                        'auth' => 'Login/Logout',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'buku_tamu' => 'success',
                        'pegawai_izin' => 'info',
                        'auth' => 'warning',
                        'default' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Aktivitas')
                    ->searchable()
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Kategori')
                    ->options([
                        'buku_tamu' => 'Buku Tamu',
                        'pegawai_izin' => 'Izin Pegawai',
                        'auth' => 'Login/Logout',
                    ]),
                Tables\Filters\SelectFilter::make('event')
                    ->label('Event')
                    ->options([
                        'created' => 'Dibuat',
                        'updated' => 'Diubah',
                        'deleted' => 'Dihapus',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_dari')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('tanggal_sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['tanggal_dari'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['tanggal_sampai'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->icon('heroicon-m-ellipsis-horizontal'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
