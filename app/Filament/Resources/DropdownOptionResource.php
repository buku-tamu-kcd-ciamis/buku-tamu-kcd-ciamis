<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DropdownOptionResource\Pages;
use App\Models\DropdownOption;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DropdownOptionResource extends Resource
{
  protected static ?string $model = DropdownOption::class;

  protected static ?string $slug = 'dropdown-options';
  protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
  protected static ?string $navigationLabel = 'Manajemen Buku Tamu';
  protected static ?string $navigationGroup = 'Pengaturan';
  protected static ?string $modelLabel = 'Opsi Dropdown';
  protected static ?string $pluralModelLabel = 'Opsi Dropdown';
  protected static ?int $navigationSort = 10;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->hasRole('Super Admin');
  }

  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Section::make('Informasi Opsi')
        ->description('Atur data opsi dropdown yang akan ditampilkan di form buku tamu.')
        ->schema([
          Forms\Components\Select::make('category')
            ->label('Kategori')
            ->options(DropdownOption::CATEGORY_LABELS)
            ->required()
            ->native(false)
            ->reactive()
            ->afterStateUpdated(function ($state, Forms\Set $set, string $operation) {
              // Auto-set sort_order to next available number for this category
              if ($operation === 'create' && $state) {
                $maxOrder = DropdownOption::where('category', $state)->max('sort_order') ?? 0;
                $set('sort_order', $maxOrder + 1);
              }
            })
            ->columnSpanFull()
            ->disabled(fn(string $operation): bool => $operation === 'edit')
            ->helperText(fn(string $operation): ?string => $operation === 'edit' ? 'Kategori tidak dapat diubah setelah dibuat.' : null),
          Forms\Components\TextInput::make('label')
            ->label('Label Tampilan')
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get, string $operation) {
              // Auto-fill value from label only on create and if value is empty
              if ($operation === 'create' && empty($get('value'))) {
                $set('value', $state);
              }
            })
            ->helperText('Label yang ditampilkan ke pengguna (contoh: KTP, Kartu Pegawai / ASN).')
            ->columnSpanFull(),
          Forms\Components\TextInput::make('value')
            ->label('Nilai (Kode Internal)')
            ->required()
            ->maxLength(255)
            ->placeholder('Otomatis terisi dari label...')
            ->helperText('Nilai yang disimpan ke database. Otomatis terisi dari label, bisa diubah jika perlu berbeda.')
            ->columnSpanFull(),
          Forms\Components\TextInput::make('sort_order')
            ->label('Urutan')
            ->numeric()
            ->default(
              fn(Forms\Get $get, string $operation) =>
              $operation === 'create' && $get('category')
                ? (DropdownOption::where('category', $get('category'))->max('sort_order') ?? 0) + 1
                : 0
            )
            ->placeholder('Otomatis diurutkan...')
            ->helperText('Urutan tampil dalam dropdown (kecil = lebih atas). Otomatis terisi dengan urutan berikutnya.'),
          Forms\Components\Toggle::make('is_active')
            ->label('Aktif')
            ->default(true)
            ->helperText('Nonaktifkan untuk menyembunyikan opsi tanpa menghapusnya.'),
        ])
        ->columns(2),

      Forms\Components\Section::make('Konfigurasi Jenis ID')
        ->description('Pengaturan tambahan khusus untuk opsi Jenis ID.')
        ->schema([
          Forms\Components\TextInput::make('metadata.id_label')
            ->label('Label Input ID')
            ->default('Nomor Identitas')
            ->placeholder('Contoh: NIK, No. SIM, No. Passport')
            ->helperText('Label yang tampil di field nomor ID (contoh: NIK, No. SIM).'),
          Forms\Components\TextInput::make('metadata.placeholder')
            ->label('Placeholder')
            ->default('Masukkan nomor identitas')
            ->placeholder('Contoh: Masukkan 16 digit NIK')
            ->helperText('Teks placeholder di field nomor ID.'),
          Forms\Components\TextInput::make('metadata.digits')
            ->label('Jumlah Digit')
            ->numeric()
            ->nullable()
            ->placeholder('Contoh: 16 (untuk KTP/NIK)')
            ->helperText('Jumlah digit wajib (kosongkan jika bebas).'),
        ])
        ->columns(2)
        ->visible(fn(Forms\Get $get): bool => $get('category') === DropdownOption::CATEGORY_JENIS_ID),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('category')
          ->label('Kategori')
          ->badge()
          ->color(fn(string $state): string => match ($state) {
            'jenis_id' => 'info',
            'keperluan' => 'success',
            'kabupaten_kota' => 'warning',
            'bagian_dituju' => 'orange',
            'pegawai_piket' => 'primary',
            default => 'gray',
          })
          ->formatStateUsing(fn(string $state) => DropdownOption::CATEGORY_LABELS[$state] ?? $state)
          ->sortable(),
        Tables\Columns\TextColumn::make('value')
          ->label('Nilai')
          ->searchable()
          ->limit(40),
        Tables\Columns\TextColumn::make('label')
          ->label('Label Tampilan')
          ->searchable()
          ->limit(40),
        Tables\Columns\TextColumn::make('sort_order')
          ->label('Urutan')
          ->sortable()
          ->alignCenter(),
        Tables\Columns\IconColumn::make('is_active')
          ->label('Aktif')
          ->boolean()
          ->alignCenter(),
        Tables\Columns\TextColumn::make('updated_at')
          ->label('Terakhir Diubah')
          ->since()
          ->tooltip(fn($record) => $record->updated_at?->format('d/m/Y H:i'))
          ->sortable(),
      ])
      ->defaultSort('sort_order')
      ->defaultPaginationPageOption(25)
      ->paginationPageOptions([10, 25, 50, 100])
      ->filters([
        Tables\Filters\SelectFilter::make('category')
          ->label('Kategori')
          ->options(DropdownOption::CATEGORY_LABELS),
        Tables\Filters\TernaryFilter::make('is_active')
          ->label('Status'),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->label('Detail'),
          Tables\Actions\EditAction::make()
            ->label('Edit'),
          Tables\Actions\DeleteAction::make()
            ->label('Hapus')
            ->requiresConfirmation()
            ->modalHeading('Hapus Opsi Dropdown')
            ->modalDescription('Apakah Anda yakin? Data yang menggunakan opsi ini tetap tersimpan, tetapi opsi tidak akan muncul lagi di dropdown.')
            ->successNotificationTitle('Opsi berhasil dihapus'),
        ])
          ->icon('heroicon-m-ellipsis-vertical')
          ->tooltip('Aksi'),
      ])
      ->bulkActions([
        Tables\Actions\DeleteBulkAction::make()
          ->label('Hapus Terpilih')
          ->requiresConfirmation()
          ->modalHeading('Hapus Data Terpilih')
          ->modalDescription('Apakah Anda yakin ingin menghapus data yang dipilih? Data yang dihapus tidak dapat dikembalikan.')
          ->successNotificationTitle('Data berhasil dihapus'),
      ]);
  }

  public static function infolist(Infolist $infolist): Infolist
  {
    return $infolist->schema([
      Infolists\Components\Section::make('Informasi Opsi')
        ->schema([
          Infolists\Components\TextEntry::make('category')
            ->label('Kategori')
            ->badge()
            ->color(fn(string $state): string => match ($state) {
              'jenis_id' => 'info',
              'keperluan' => 'success',
              'kabupaten_kota' => 'warning',
              'bagian_dituju' => 'orange',
              'pegawai_piket' => 'primary',
              default => 'gray',
            })
            ->formatStateUsing(fn(string $state) => DropdownOption::CATEGORY_LABELS[$state] ?? $state),
          Infolists\Components\TextEntry::make('value')
            ->label('Nilai'),
          Infolists\Components\TextEntry::make('label')
            ->label('Label Tampilan'),
          Infolists\Components\TextEntry::make('sort_order')
            ->label('Urutan'),
          Infolists\Components\IconEntry::make('is_active')
            ->label('Aktif')
            ->boolean(),
          Infolists\Components\TextEntry::make('created_at')
            ->label('Dibuat')
            ->dateTime('d/m/Y H:i'),
          Infolists\Components\TextEntry::make('updated_at')
            ->label('Terakhir Diubah')
            ->dateTime('d/m/Y H:i'),
        ])
        ->columns(2),

      Infolists\Components\Section::make('Konfigurasi Jenis ID')
        ->schema([
          Infolists\Components\TextEntry::make('metadata.id_label')
            ->label('Label Input ID')
            ->default('-'),
          Infolists\Components\TextEntry::make('metadata.placeholder')
            ->label('Placeholder')
            ->default('-'),
          Infolists\Components\TextEntry::make('metadata.digits')
            ->label('Jumlah Digit')
            ->default('-'),
        ])
        ->columns(2)
        ->visible(fn($record): bool => $record->category === DropdownOption::CATEGORY_JENIS_ID),
    ]);
  }

  public static function getRelations(): array
  {
    return [];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListDropdownOptions::route('/'),
      'create' => Pages\CreateDropdownOption::route('/create'),
      'view' => Pages\ViewDropdownOption::route('/{record}'),
      'edit' => Pages\EditDropdownOption::route('/{record}/edit'),
    ];
  }
}
