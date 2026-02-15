<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataPegawaiResource\Pages;
use App\Models\Pegawai;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DataPegawaiResource extends Resource
{
  protected static ?string $model = Pegawai::class;

  protected static ?string $slug = 'data-pegawai';
  protected static ?string $navigationIcon = 'heroicon-o-users';
  protected static ?string $navigationLabel = 'Data Pegawai';
  protected static ?string $navigationGroup = 'Pengaturan';
  protected static ?string $modelLabel = 'Pegawai';
  protected static ?string $pluralModelLabel = 'Data Pegawai';
  protected static ?int $navigationSort = 12;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->role_user && $user->role_user->hasPermission('data_pegawai');
  }

  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Section::make('Informasi Pegawai')
        ->description('Data lengkap pegawai untuk otomatisasi formulir izin.')
        ->columns(2)
        ->schema([
          Forms\Components\TextInput::make('nama')
            ->label('Nama Lengkap')
            ->required()
            ->maxLength(255)
            ->placeholder('Contoh: Drs. H. Ahmad Suryadi, M.Pd.')
            ->columnSpanFull(),
          Forms\Components\TextInput::make('nip')
            ->label('NIP')
            ->required()
            ->unique(ignoreRecord: true)
            ->minLength(18)
            ->maxLength(18)
            ->placeholder('Masukkan 18 digit NIP')
            ->mask('999999999999999999')
            ->live()
            ->suffixIcon(function ($state) {
              if (!$state) return null;
              return strlen($state) === 18 ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle';
            })
            ->suffixIconColor(function ($state) {
              if (!$state) return null;
              return strlen($state) === 18 ? 'success' : 'danger';
            })
            ->helperText(function ($state) {
              if (!$state) return 'NIP harus tepat 18 digit angka';
              $length = strlen($state);
              $status = $length === 18 ? '✓ valid' : '✗ belum lengkap';
              return "{$length}/18 digit — {$status}";
            }),
          Forms\Components\TextInput::make('jabatan')
            ->label('Jabatan')
            ->required()
            ->maxLength(255)
            ->placeholder('Contoh: Kepala Cabang Dinas'),
          Forms\Components\TextInput::make('unit_kerja')
            ->label('Unit Kerja')
            ->required()
            ->maxLength(255)
            ->placeholder('Contoh: Sub Bagian Tata Usaha'),
          Forms\Components\TextInput::make('nomor_hp')
            ->label('Nomor Handphone')
            ->tel()
            ->prefix('+62')
            ->placeholder('8xx-xxxx-xxxx')
            ->mask('999-9999-99999')
            ->maxLength(15)
            ->helperText('Format: 8xx-xxxx-xxxx (tanpa 0 di depan)'),
          Forms\Components\Toggle::make('is_active')
            ->label('Status Aktif')
            ->default(true)
            ->helperText('Nonaktifkan jika pegawai sudah tidak bertugas.')
            ->inline(false),
        ]),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('nama')
          ->label('Nama Lengkap')
          ->searchable()
          ->sortable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('nip')
          ->label('NIP')
          ->searchable()
          ->copyable()
          ->fontFamily('mono'),
        Tables\Columns\TextColumn::make('jabatan')
          ->label('Jabatan')
          ->searchable()
          ->limit(30)
          ->tooltip(fn($record) => $record->jabatan),
        Tables\Columns\TextColumn::make('unit_kerja')
          ->label('Unit Kerja')
          ->searchable()
          ->toggleable()
          ->limit(30)
          ->tooltip(fn($record) => $record->unit_kerja),
        Tables\Columns\TextColumn::make('nomor_hp')
          ->label('No. HP')
          ->formatStateUsing(function ($state) {
            if (!$state) return '-';
            return '+62' . $state;
          })
          ->toggleable(),
        Tables\Columns\IconColumn::make('is_active')
          ->label('Status')
          ->boolean()
          ->trueIcon('heroicon-o-check-circle')
          ->falseIcon('heroicon-o-x-circle')
          ->trueColor('success')
          ->falseColor('danger')
          ->alignCenter(),
      ])
      ->defaultSort('nama')
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
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\EditAction::make(),
          Tables\Actions\DeleteAction::make()
            ->requiresConfirmation()
            ->modalHeading('Hapus Data Pegawai')
            ->modalDescription('Apakah Anda yakin ingin menghapus data pegawai ini? Data yang dihapus tidak dapat dikembalikan.')
            ->successNotificationTitle('Data pegawai berhasil dihapus'),
        ])
          ->iconButton()
          ->icon('heroicon-m-ellipsis-vertical')
          ->color('gray'),
      ])
      ->bulkActions([
        Tables\Actions\DeleteBulkAction::make()
          ->requiresConfirmation(),
      ]);
  }

  public static function getRelations(): array
  {
    return [];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListDataPegawai::route('/'),
      'create' => Pages\CreateDataPegawai::route('/create'),
      'edit' => Pages\EditDataPegawai::route('/{record}/edit'),
    ];
  }
}
