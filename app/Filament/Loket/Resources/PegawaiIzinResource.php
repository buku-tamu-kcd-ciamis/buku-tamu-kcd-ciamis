<?php

namespace App\Filament\Loket\Resources;

use App\Filament\Loket\Resources\PegawaiIzinResource\Pages;
use App\Models\PegawaiIzin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PegawaiIzinResource extends Resource
{
  protected static ?string $model = PegawaiIzin::class;

  protected static ?string $slug = 'pegawai-izin';
  protected static ?string $navigationIcon = 'heroicon-o-user-minus';
  protected static ?string $navigationLabel = 'Pegawai Izin';
  protected static ?string $navigationGroup = 'Kepegawaian';
  protected static ?string $modelLabel = 'Pegawai Izin';
  protected static ?string $pluralModelLabel = 'Pegawai Izin';
  protected static ?int $navigationSort = 4;

  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Section::make()->columns(2)->schema([
        Forms\Components\TextInput::make('nama_pegawai')
          ->label('Nama Pegawai')
          ->required()
          ->maxLength(255),
        Forms\Components\TextInput::make('nip')
          ->label('NIP')
          ->maxLength(30),
        Forms\Components\TextInput::make('jabatan')
          ->maxLength(255),
        Forms\Components\TextInput::make('unit_kerja')
          ->label('Unit Kerja')
          ->maxLength(255),
        Forms\Components\Select::make('jenis_izin')
          ->label('Jenis Izin')
          ->options(PegawaiIzin::JENIS_IZIN_LABELS)
          ->required(),
        Forms\Components\Select::make('status')
          ->options([
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
          ])
          ->default('aktif')
          ->required(),
        Forms\Components\DatePicker::make('tanggal_mulai')
          ->label('Tanggal Mulai')
          ->required(),
        Forms\Components\DatePicker::make('tanggal_selesai')
          ->label('Tanggal Selesai')
          ->required()
          ->afterOrEqual('tanggal_mulai'),
        Forms\Components\Textarea::make('keterangan')
          ->rows(3)
          ->columnSpanFull(),
      ]),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('nama_pegawai')
          ->label('Nama')
          ->searchable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('nip')
          ->label('NIP')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('jabatan')
          ->toggleable(),
        Tables\Columns\TextColumn::make('unit_kerja')
          ->label('Unit Kerja')
          ->toggleable(),
        Tables\Columns\BadgeColumn::make('jenis_izin')
          ->label('Jenis Izin')
          ->formatStateUsing(fn(string $state) => PegawaiIzin::JENIS_IZIN_LABELS[$state] ?? $state)
          ->color('info'),
        Tables\Columns\TextColumn::make('tanggal_mulai')
          ->label('Mulai')
          ->date('d/m/Y')
          ->sortable(),
        Tables\Columns\TextColumn::make('tanggal_selesai')
          ->label('Selesai')
          ->date('d/m/Y')
          ->sortable(),
        Tables\Columns\BadgeColumn::make('status')
          ->colors([
            'success' => 'aktif',
            'gray' => 'selesai',
          ])
          ->formatStateUsing(fn(string $state) => ucfirst($state)),
      ])
      ->defaultSort('tanggal_mulai', 'desc')
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->options([
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
          ]),
        Tables\Filters\SelectFilter::make('jenis_izin')
          ->label('Jenis Izin')
          ->options(PegawaiIzin::JENIS_IZIN_LABELS),
      ])
      ->actions([
        Tables\Actions\EditAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\DeleteBulkAction::make(),
      ]);
  }

  public static function getRelations(): array
  {
    return [];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListPegawaiIzin::route('/'),
      'create' => Pages\CreatePegawaiIzin::route('/create'),
      'edit' => Pages\EditPegawaiIzin::route('/{record}/edit'),
    ];
  }
}
