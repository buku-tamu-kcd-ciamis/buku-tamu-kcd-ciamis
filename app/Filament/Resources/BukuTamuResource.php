<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BukuTamuResource\Pages;
use App\Models\BukuTamu;
use App\Models\DropdownOption;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class BukuTamuResource extends Resource
{
    protected static ?string $model = BukuTamu::class;

    protected static ?string $slug = 'buku-tamu';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Buku Tamu';
    protected static string|\UnitEnum|null $navigationGroup = 'Layanan Tamu';
    protected static ?string $modelLabel = 'Buku Tamu';
    protected static ?string $pluralModelLabel = 'Data Buku Tamu';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->role_user && $user->role_user->hasPermission('buku_tamu');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('status')
                ->options(BukuTamu::STATUS_LABELS)
                ->required(),
            Forms\Components\Textarea::make('catatan')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('foto_selfie')
                    ->label('Foto')
                    ->view('filament.tables.columns.avatar-column'),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->weight('bold')
                    ->verticallyAlignCenter(),
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->verticallyAlignCenter(),
                Tables\Columns\TextColumn::make('instansi')
                    ->searchable()
                    ->toggleable()
                    ->verticallyAlignCenter(),
                Tables\Columns\TextColumn::make('keperluan')
                    ->limit(40)
                    ->toggleable()
                    ->verticallyAlignCenter(),
                Tables\Columns\TextColumn::make('staff_dituju')
                    ->label('Staff Yang Dituju')
                    ->toggleable()
                    ->verticallyAlignCenter(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'menunggu' => 'warning',
                        'diproses' => 'info',
                        'selesai' => 'success',
                        'ditolak' => 'danger',
                        'dibatalkan' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => BukuTamu::STATUS_LABELS[$state] ?? ucfirst($state))
                    ->verticallyAlignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since()
                    ->color('gray')
                    ->tooltip(fn($record) => $record->created_at->format('d/m/Y H:i'))
                    ->sortable()
                    ->verticallyAlignCenter(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(BukuTamu::STATUS_LABELS),
                Tables\Filters\Filter::make('tanggal')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['tanggal'], fn($q, $date) => $q->whereDate('created_at', $date));
                    }),
            ])
            ->recordActions([])
            ->headerActions([])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBukuTamus::route('/'),
            'view' => Pages\ViewBukuTamu::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
