<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BukuTamuResource\Pages;
use App\Filament\Resources\BukuTamuResource\RelationManagers;
use App\Models\BukuTamu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BukuTamuResource extends Resource
{
    protected static ?string $model = BukuTamu::class;

    protected static ?string $slug = 'buku-tamu';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Buku Tamu';

    protected static ?string $modelLabel = 'Buku Tamu';

    protected static ?string $pluralModelLabel = 'Data Buku Tamu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengunjung')
                    ->schema([
                        Forms\Components\TextInput::make('jenis_id')
                            ->label('Jenis ID')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('nik')
                            ->label('Nomor ID')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('instansi')
                            ->label('Instansi')
                            ->disabled(),
                        Forms\Components\TextInput::make('nomor_hp')
                            ->label('Nomor HP')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->disabled(),
                        Forms\Components\TextInput::make('kabupaten_kota')
                            ->label('Kabupaten/Kota')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('bagian_dituju')
                            ->label('Bagian Yang Dituju')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->disabled(),
                        Forms\Components\Textarea::make('keperluan')
                            ->label('Keperluan')
                            ->required()
                            ->disabled()
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Dokumen')
                    ->schema([
                        Forms\Components\ViewField::make('foto_selfie')
                            ->label('Foto Selfie')
                            ->view('filament.forms.components.image-base64'),
                        Forms\Components\ViewField::make('foto_penerimaan')
                            ->label('Foto Penerimaan')
                            ->view('filament.forms.components.image-base64'),
                        Forms\Components\ViewField::make('tanda_tangan')
                            ->label('Tanda Tangan')
                            ->view('filament.forms.components.image-base64'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_id')
                    ->label('Jenis ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nik')
                    ->label('Nomor ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('instansi')
                    ->label('Instansi')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nomor_hp')
                    ->label('No. HP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kabupaten_kota')
                    ->label('Kab/Kota')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bagian_dituju')
                    ->label('Bagian Dituju')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('keperluan')
                    ->label('Keperluan')
                    ->limit(50)
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBukuTamus::route('/'),
            'view' => Pages\ViewBukuTamu::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
