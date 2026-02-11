<?php

namespace App\Filament\Loket\Resources;

use App\Filament\Loket\Resources\KunjunganResource\Pages;
use App\Models\BukuTamu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KunjunganResource extends Resource
{
  protected static ?string $model = BukuTamu::class;

  protected static ?string $slug = 'kunjungan';
  protected static ?string $navigationIcon = 'heroicon-o-users';
  protected static ?string $navigationLabel = 'Kunjungan Tamu';
  protected static ?string $navigationGroup = 'Layanan Tamu';
  protected static ?string $modelLabel = 'Kunjungan';
  protected static ?string $pluralModelLabel = 'Kunjungan Tamu';
  protected static ?int $navigationSort = 1;

  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Select::make('status')
        ->options(BukuTamu::STATUS_LABELS)
        ->required(),
      Forms\Components\Textarea::make('catatan')
        ->label('Catatan')
        ->rows(3),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ImageColumn::make('foto_selfie')
          ->label('Foto')
          ->circular()
          ->size(40)
          ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=G&background=0F9455&color=fff'),
        Tables\Columns\TextColumn::make('nama_lengkap')
          ->label('Nama')
          ->searchable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('nik')
          ->label('NIK')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('instansi')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('keperluan')
          ->limit(40)
          ->toggleable(),
        Tables\Columns\TextColumn::make('bagian_dituju')
          ->label('Bagian Dituju')
          ->toggleable(),
        Tables\Columns\BadgeColumn::make('status')
          ->colors([
            'warning' => 'menunggu',
            'info' => 'diproses',
            'success' => 'selesai',
            'danger' => 'ditolak',
            'gray' => 'dibatalkan',
          ])
          ->formatStateUsing(fn(string $state) => BukuTamu::STATUS_LABELS[$state] ?? ucfirst($state)),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Waktu')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
      ])
      ->defaultSort('created_at', 'desc')
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->options(BukuTamu::STATUS_LABELS),
        Tables\Filters\Filter::make('tanggal')
          ->form([
            Forms\Components\DatePicker::make('tanggal')
              ->label('Tanggal'),
          ])
          ->query(function ($query, array $data) {
            return $query->when($data['tanggal'], fn($q, $date) => $q->whereDate('created_at', $date));
          }),
      ])
      ->actions([
        Tables\Actions\Action::make('ubah_status')
          ->label('Ubah Status')
          ->icon('heroicon-o-pencil-square')
          ->form([
            Forms\Components\Select::make('status')
              ->options(BukuTamu::STATUS_LABELS)
              ->required(),
            Forms\Components\Textarea::make('catatan')
              ->label('Catatan')
              ->rows(3),
          ])
          ->fillForm(fn(BukuTamu $record) => [
            'status' => $record->status,
            'catatan' => $record->catatan,
          ])
          ->action(function (BukuTamu $record, array $data) {
            $record->update($data);
          })
          ->modalHeading('Ubah Status Kunjungan')
          ->modalButton('Simpan'),
        Tables\Actions\ViewAction::make(),
      ])
      ->bulkActions([]);
  }

  public static function getRelations(): array
  {
    return [];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListKunjungan::route('/'),
      'view' => Pages\ViewKunjungan::route('/{record}'),
    ];
  }

  public static function canCreate(): bool
  {
    return false;
  }
}
