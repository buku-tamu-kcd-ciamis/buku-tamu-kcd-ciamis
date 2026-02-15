<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Log Aktivitas';
    protected static ?string $navigationGroup = 'Bantuan';
    protected static ?string $modelLabel = 'Log Aktivitas';
    protected static ?string $pluralModelLabel = 'Log Aktivitas';
    protected static ?int $navigationSort = 98;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->role_user && $user->role_user->hasPermission('activity_log');
    }

    public static function canViewAny(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->role_user && $user->role_user->hasPermission('activity_log');
    }

    public static function getLogNameLabel(string $state): string
    {
        return match ($state) {
            'buku_tamu' => 'Buku Tamu',
            'pegawai_izin' => 'Izin Pegawai',
            'auth' => 'Autentikasi',
            'cetak' => 'Cetak/Print',
            'user' => 'User',
            'dropdown_option' => 'Dropdown Option',
            'pegawai' => 'Data Pegawai',
            'pengaturan' => 'Pengaturan',
            default => ucfirst(str_replace('_', ' ', $state)),
        };
    }

    public static function getLogNameColor(string $state): string
    {
        return match ($state) {
            'buku_tamu' => 'success',
            'pegawai_izin' => 'info',
            'auth' => 'warning',
            'cetak' => 'gray',
            'user' => 'danger',
            'dropdown_option' => 'primary',
            'pegawai' => 'info',
            'pengaturan' => 'warning',
            default => 'gray',
        };
    }

    public static function getEventIcon(string $state): string
    {
        return match ($state) {
            'created' => 'heroicon-o-plus-circle',
            'updated' => 'heroicon-o-pencil-square',
            'deleted' => 'heroicon-o-trash',
            default => 'heroicon-o-information-circle',
        };
    }

    public static function getLogNameLabels(): array
    {
        return [
            'buku_tamu' => 'Buku Tamu',
            'pegawai_izin' => 'Pegawai Izin',
            'auth' => 'Authentication',
            'cetak' => 'Cetak',
            'user' => 'User',
            'dropdown_option' => 'Dropdown Option',
            'pegawai' => 'Data Pegawai',
            'pengaturan' => 'Pengaturan',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small)
                    ->color('gray'),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->searchable()
                    ->default('System')
                    ->icon('heroicon-o-user')
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small),
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Modul')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => self::getLogNameLabel($state))
                    ->color(fn(string $state): string => self::getLogNameColor($state)),
                Tables\Columns\TextColumn::make('event')
                    ->label('Aksi')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'created' => 'Dibuat',
                        'updated' => 'Diubah',
                        'deleted' => 'Dihapus',
                        default => $state ? ucfirst($state) : '-',
                    })
                    ->icon(fn(?string $state): ?string => $state ? self::getEventIcon($state) : null)
                    ->color(fn(?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Aktivitas')
                    ->searchable()
                    ->wrap()
                    ->limit(80)
                    ->tooltip(fn($record) => $record->description),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Model')
                    ->formatStateUsing(fn(?string $state) => $state ? class_basename($state) : '-')
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small)
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50, 100])
            ->poll('30s')
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Modul')
                    ->multiple()
                    ->options(fn() => Activity::query()
                        ->distinct()
                        ->pluck('log_name', 'log_name')
                        ->mapWithKeys(fn($val, $key) => [$key => self::getLogNameLabel($key)])
                        ->toArray()),
                Tables\Filters\SelectFilter::make('event')
                    ->label('Jenis Aksi')
                    ->options([
                        'created' => 'Dibuat',
                        'updated' => 'Diubah',
                        'deleted' => 'Dihapus',
                    ]),
                Tables\Filters\SelectFilter::make('causer_id')
                    ->label('User')
                    ->searchable()
                    ->preload()
                    ->options(fn() => User::pluck('name', 'id')->toArray()),
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['tanggal_dari'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['tanggal_dari'])->translatedFormat('d M Y');
                        }
                        if ($data['tanggal_sampai'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['tanggal_sampai'])->translatedFormat('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('primary'),
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
