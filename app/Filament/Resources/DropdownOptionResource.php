<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DropdownOptionResource\Pages;
use App\Models\DropdownOption;
use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DropdownOptionResource extends Resource
{
  protected static ?string $model = DropdownOption::class;

  protected static ?string $slug = 'dropdown-options';
  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';
  protected static ?string $navigationLabel = 'Manajemen Buku Tamu';
  protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
  protected static ?string $modelLabel = 'Opsi Dropdown';
  protected static ?string $pluralModelLabel = 'Opsi Dropdown';
  protected static ?int $navigationSort = 10;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->role_user && $user->role_user->hasPermission('dropdown_options');
  }

  public static function form(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Informasi Opsi')
        ->description('Atur data opsi dropdown yang akan ditampilkan di form buku tamu.')
        ->schema([
          Forms\Components\Select::make('category')
            ->label('Kategori')
            ->options(DropdownOption::CATEGORY_LABELS)
            ->required()
            ->native(false),
          Forms\Components\TextInput::make('label')
            ->label('Label Tampilan')
            ->required()
            ->maxLength(255)
            ->helperText('Label yang ditampilkan ke pengguna (contoh: KTP, Kartu Pegawai / ASN).'),
          Forms\Components\TextInput::make('value')
            ->label('Nilai Tersimpan')
            ->required()
            ->maxLength(255)
            ->placeholder('Otomatis terisi dari label...')
            ->helperText('Nilai yang disimpan ke database. Otomatis terisi dari label, bisa diubah jika perlu berbeda.')
            ->unique(table: DropdownOption::class, column: 'value', ignoreRecord: true)
            ->validationMessages([
              'unique' => 'Nilai ini sudah ada di kategori yang sama. Gunakan nilai yang berbeda.',
            ]),
          Forms\Components\TextInput::make('sort_order')
            ->label('Urutan Tampil')
            ->numeric()
            ->placeholder('Otomatis diurutkan...')
            ->helperText('Urutan tampil dalam dropdown (kecil = lebih atas). Otomatis terisi dengan urutan berikutnya.'),
          Forms\Components\Toggle::make('is_active')
            ->label('Status Aktif')
            ->helperText('Nonaktifkan untuk menyembunyikan opsi tanpa menghapusnya.'),
        ])
        ->columns(2),

      Section::make('Konfigurasi Jenis ID')
        ->description('Pengaturan tambahan khusus untuk opsi Jenis ID.')
        ->schema([
          Forms\Components\TextInput::make('metadata.id_label')
            ->label('Label Identitas')
            ->placeholder('Contoh: NIK, No. SIM, No. Passport')
            ->helperText('Label yang tampil di field nomor ID (contoh: NIK, No. SIM).'),
          Forms\Components\TextInput::make('metadata.placeholder')
            ->label('Placeholder Input')
            ->placeholder('Contoh: Masukkan 16 digit NIK')
            ->helperText('Teks placeholder di field nomor ID.'),
          Forms\Components\TextInput::make('metadata.digits')
            ->label('Jumlah Digit Wajib')
            ->numeric()
            ->nullable()
            ->placeholder('Contoh: 16 (untuk KTP/NIK)')
            ->helperText('Jumlah digit wajib (kosongkan jika bebas).'),
          Forms\Components\TextInput::make('metadata.max_repeated_digits')
            ->label('Maksimal Digit Berulang')
            ->numeric()
            ->minValue(1)
            ->helperText('Jumlah maksimal angka sama berturut-turut yang diizinkan (misal: 3 berarti 000 boleh, 0000 tidak).'),
          Forms\Components\TextInput::make('metadata.max_sequential_digits')
            ->label('Maksimal Digit Berurutan')
            ->numeric()
            ->minValue(1)
            ->helperText('Jumlah maksimal angka berurutan yang diizinkan (misal: 2 berarti 12 boleh, 123 tidak).'),
        ])
        ->columns(2),
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
            DropdownOption::CATEGORY_JENIS_ID => 'info',
            DropdownOption::CATEGORY_KEPERLUAN => 'success',
            DropdownOption::CATEGORY_KABUPATEN_KOTA => 'warning',
            DropdownOption::CATEGORY_STAFF_DITUJU => 'danger',
            DropdownOption::CATEGORY_PEGAWAI_PIKET => 'primary',
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
          ->color('gray')
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
      ->recordActions([
        ActionGroup::make([
          ViewAction::make()
            ->label('Lihat')
            ->icon('heroicon-o-eye')
            ->color('primary'),
          EditAction::make()
            ->label('Edit')
            ->icon('heroicon-o-pencil-square')
            ->color('warning'),
          DeleteAction::make()
            ->label('Hapus')
            ->icon('heroicon-o-trash')
            ->color('danger'),
        ])
          ->label(false)
          ->icon('heroicon-m-ellipsis-vertical')
          ->color('gray'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          BulkAction::make('bulk_activate')
            ->label('Aktifkan Terpilih')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Aktifkan Opsi Terpilih')
            ->modalDescription('Semua opsi dropdown yang dipilih akan diaktifkan.')
            ->modalSubmitActionLabel('Aktifkan')
            ->action(function ($records): void {
              $count = 0;

              $records->each(function (DropdownOption $record) use (&$count): void {
                if (! $record->is_active) {
                  $record->update(['is_active' => true]);
                  $count++;
                }
              });

              Notification::make()
                ->success()
                ->title('Bulk update selesai')
                ->body($count . ' opsi berhasil diaktifkan.')
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
          BulkAction::make('bulk_deactivate')
            ->label('Nonaktifkan Terpilih')
            ->icon('heroicon-o-no-symbol')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Nonaktifkan Opsi Terpilih')
            ->modalDescription('Semua opsi dropdown yang dipilih akan dinonaktifkan.')
            ->modalSubmitActionLabel('Nonaktifkan')
            ->action(function ($records): void {
              $count = 0;

              $records->each(function (DropdownOption $record) use (&$count): void {
                if ($record->is_active) {
                  $record->update(['is_active' => false]);
                  $count++;
                }
              });

              Notification::make()
                ->warning()
                ->title('Bulk update selesai')
                ->body($count . ' opsi berhasil dinonaktifkan.')
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
          BulkAction::make('bulk_delete')
            ->label('Hapus Terpilih')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Hapus Opsi Terpilih')
            ->modalDescription('Semua opsi dropdown yang dipilih akan dihapus permanen.')
            ->modalSubmitActionLabel('Hapus')
            ->action(function ($records): void {
              $count = $records->count();
              $records->each->delete();

              Notification::make()
                ->success()
                ->title('Bulk hapus selesai')
                ->body($count . ' opsi berhasil dihapus.')
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
        ]),
      ]);
  }

  public static function infolist(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Informasi Opsi')
        ->icon('heroicon-o-adjustments-horizontal')
        ->description('Ringkasan data opsi dropdown yang digunakan pada form Buku Tamu.')
        ->components([
          Infolists\Components\TextEntry::make('category')
            ->label('Kategori')
            ->badge()
            ->color(fn(string $state): string => match ($state) {
              DropdownOption::CATEGORY_JENIS_ID => 'info',
              DropdownOption::CATEGORY_KEPERLUAN => 'success',
              DropdownOption::CATEGORY_KABUPATEN_KOTA => 'warning',
              DropdownOption::CATEGORY_STAFF_DITUJU => 'danger',
              DropdownOption::CATEGORY_PEGAWAI_PIKET => 'primary',
              default => 'gray',
            })
            ->formatStateUsing(fn(string $state) => DropdownOption::CATEGORY_LABELS[$state] ?? $state),
          Infolists\Components\TextEntry::make('label')
            ->label('Label Tampilan')
            ->weight('bold'),
          Infolists\Components\TextEntry::make('value')
            ->label('Nilai Tersimpan')
            ->copyable(),
          Infolists\Components\TextEntry::make('sort_order')
            ->label('Urutan')
            ->badge()
            ->color('gray')
            ->formatStateUsing(fn($state) => '#' . $state),
          Infolists\Components\TextEntry::make('is_active')
            ->label('Status')
            ->badge()
            ->formatStateUsing(fn(bool $state): string => $state ? 'Aktif' : 'Nonaktif')
            ->color(fn(bool $state): string => $state ? 'success' : 'gray'),
          Infolists\Components\TextEntry::make('created_at')
            ->label('Dibuat')
            ->dateTime('d/m/Y H:i'),
          Infolists\Components\TextEntry::make('updated_at')
            ->label('Terakhir Diubah')
            ->dateTime('d/m/Y H:i'),
        ])
        ->columns(2),

      Section::make('Konfigurasi Jenis ID')
        ->icon('heroicon-o-identification')
        ->description('Pengaturan validasi tambahan untuk opsi kategori Jenis ID.')
        ->visible(fn(DropdownOption $record): bool => $record->category === DropdownOption::CATEGORY_JENIS_ID)
        ->components([
          Infolists\Components\TextEntry::make('metadata.id_label')
            ->label('Label ID')
            ->placeholder('-'),
          Infolists\Components\TextEntry::make('metadata.placeholder')
            ->label('Placeholder Input')
            ->placeholder('-'),
          Infolists\Components\TextEntry::make('metadata.digits')
            ->label('Jumlah Digit Wajib')
            ->placeholder('-'),
          Infolists\Components\TextEntry::make('metadata.max_repeated_digits')
            ->label('Maks. Digit Berulang')
            ->placeholder('-'),
          Infolists\Components\TextEntry::make('metadata.max_sequential_digits')
            ->label('Maks. Digit Berurutan')
            ->placeholder('-'),
        ])
        ->columns(2)
        ->collapsible(),
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
