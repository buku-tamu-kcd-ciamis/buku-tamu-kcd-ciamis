<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataPegawaiResource\Pages;
use App\Models\Pegawai;
use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DataPegawaiResource extends Resource
{
  protected static ?string $model = Pegawai::class;

  protected static ?string $slug = 'data-pegawai';
  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
  protected static ?string $navigationLabel = 'Data Pegawai';
  protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
  protected static ?string $modelLabel = 'Pegawai';
  protected static ?string $pluralModelLabel = 'Data Pegawai';
  protected static ?int $navigationSort = 12;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->role_user && $user->role_user->hasPermission('data_pegawai');
  }

  public static function canViewAny(): bool
  {
    return static::shouldRegisterNavigation();
  }

  public static function form(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Informasi Pegawai')
        ->description('Data lengkap pegawai untuk otomatisasi formulir izin.')
        ->columnSpanFull()
        ->columns(2)
        ->schema([
          Forms\Components\TextInput::make('nama')
            ->label('Nama Lengkap')
            ->required()
            ->maxLength(255)
            ->placeholder('Contoh: Drs. H. Ahmad Suryadi, M.Pd.'),
          Forms\Components\TextInput::make('nip')
            ->label('NIP')
            ->required()
            ->unique(ignoreRecord: true)
            ->minLength(18)
            ->maxLength(18)
            ->placeholder('Masukkan 18 digit NIP')
            ->mask('999999999999999999')
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
              $status = $length === 18 ? '✓ sesuai' : '✗ belum lengkap';
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
            ->label('Nomor HP')
            ->tel()
            ->prefix('+62')
            ->placeholder('8xx-xxxx-xxxx')
            ->mask('999-9999-99999')
            ->maxLength(15)
            ->helperText('Format: 8xx-xxxx-xxxx (tanpa 0 di depan)'),
          Forms\Components\Toggle::make('is_active')
            ->label('Status Aktif')
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

            $cleaned = preg_replace('/[^0-9]/', '', (string) $state);

            if (!$cleaned) return '-';

            if (str_starts_with($cleaned, '62')) {
              return '0' . substr($cleaned, 2);
            }

            if (str_starts_with($cleaned, '8')) {
              return '0' . $cleaned;
            }

            if (str_starts_with($cleaned, '0')) {
              return $cleaned;
            }

            return '0' . $cleaned;
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
      ->recordActionsColumnLabel('')
      ->filters([
        Tables\Filters\TernaryFilter::make('is_active')
          ->label('Status')
          ->placeholder('Semua')
          ->trueLabel('Aktif')
          ->falseLabel('Nonaktif'),
      ])
      ->recordActions([
        ActionGroup::make([
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
            ->modalHeading('Aktifkan Pegawai Terpilih')
            ->modalDescription('Semua data pegawai yang dipilih akan diaktifkan.')
            ->modalSubmitActionLabel('Aktifkan')
            ->action(function ($records): void {
              $count = 0;

              $records->each(function (Pegawai $record) use (&$count): void {
                if (! $record->is_active) {
                  $record->update(['is_active' => true]);
                  $count++;
                }
              });

              Notification::make()
                ->success()
                ->title('Bulk update selesai')
                ->body($count . ' data pegawai berhasil diaktifkan.')
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
          BulkAction::make('bulk_deactivate')
            ->label('Nonaktifkan Terpilih')
            ->icon('heroicon-o-no-symbol')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Nonaktifkan Pegawai Terpilih')
            ->modalDescription('Semua data pegawai yang dipilih akan dinonaktifkan.')
            ->modalSubmitActionLabel('Nonaktifkan')
            ->action(function ($records): void {
              $count = 0;

              $records->each(function (Pegawai $record) use (&$count): void {
                if ($record->is_active) {
                  $record->update(['is_active' => false]);
                  $count++;
                }
              });

              Notification::make()
                ->success()
                ->title('Bulk update selesai')
                ->body($count . ' data pegawai berhasil dinonaktifkan.')
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
          BulkAction::make('bulk_delete')
            ->label('Hapus Terpilih')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Hapus Data Pegawai Terpilih')
            ->modalDescription('Semua data pegawai yang dipilih akan dihapus permanen.')
            ->modalSubmitActionLabel('Hapus')
            ->action(function ($records): void {
              $count = $records->count();
              $records->each->delete();

              Notification::make()
                ->success()
                ->title('Bulk hapus selesai')
                ->body($count . ' data pegawai berhasil dihapus.')
                ->send();
            })
            ->deselectRecordsAfterCompletion(),
        ]),
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
