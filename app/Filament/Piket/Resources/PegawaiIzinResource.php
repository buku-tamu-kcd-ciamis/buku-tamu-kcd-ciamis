<?php

namespace App\Filament\Piket\Resources;

use App\Filament\Piket\Resources\PegawaiIzinResource\Pages;
use App\Models\Pegawai;
use App\Models\DropdownOption;
use App\Models\PegawaiIzin;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PegawaiIzinResource extends Resource
{
  protected static ?string $model = PegawaiIzin::class;

  protected static ?string $slug = 'pegawai-izin';
  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-minus';
  protected static ?string $navigationLabel = 'Izin Pegawai';
  protected static string|\UnitEnum|null $navigationGroup = 'Kepegawaian';
  protected static ?string $modelLabel = 'Izin Pegawai';
  protected static ?string $pluralModelLabel = 'Izin Pegawai';
  protected static ?int $navigationSort = 4;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->role_user && $user->role_user->hasPermission('pegawai_izin');
  }

  public static function form(Schema $schema): Schema
  {
    return $schema->components([
      Section::make()->columns(2)->schema([
        Forms\Components\Select::make('pegawai_id')
          ->options(function () {
            return Pegawai::active()
              ->orderBy('nama')
              ->get()
              ->mapWithKeys(fn($p) => [$p->id => "{$p->nama} — {$p->nip}"]);
          })
          ->searchable()
          ->preload()
          ->placeholder('Cari nama atau NIP pegawai...')
          ->helperText('Pilih pegawai dari daftar untuk mengisi data otomatis.'),
        Forms\Components\TextInput::make('nama_pegawai')
          ->required()
          ->maxLength(255)
          ->readOnly(),
        Forms\Components\TextInput::make('nip')
          ->required()
          ->minLength(18)
          ->maxLength(18)
          ->placeholder('Masukkan 18 digit NIP')
          ->mask('999999999999999999')
          ->readOnly()
          ->suffixIcon(function ($state) {
            if (!$state)
              return null;
            return strlen($state) === 18 ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle';
          })
          ->suffixIconColor(function ($state) {
            if (!$state)
              return null;
            return strlen($state) === 18 ? 'success' : 'danger';
          })
          ->helperText(function ($state) {
            if (!$state)
              return 'Otomatis terisi dari pilihan pegawai';
            $length = strlen($state);
            $status = $length === 18 ? 'valid' : 'invalid';
            return "{$length} digit — {$status}";
          }),
        Forms\Components\TextInput::make('jabatan')
          ->maxLength(255)
          ->readOnly(),
        Forms\Components\TextInput::make('unit_kerja')
          ->maxLength(255)
          ->readOnly(),
        Forms\Components\TextInput::make('nomor_hp')
          ->tel()
          ->prefix('+62')
          ->placeholder('8xx-xxxx-xxxx')
          ->mask('999-9999-99999')
          ->maxLength(15)
          ->readOnly()
          ->helperText('Otomatis terisi dari data pegawai'),
        Forms\Components\Select::make('jenis_izin')
          ->options(PegawaiIzin::JENIS_IZIN_LABELS)
          ->searchable()
          ->required(),
        Forms\Components\Select::make('status')
          ->options([
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
          ])
          ->required(),
        Forms\Components\DatePicker::make('tanggal_mulai')
          ->required(),
        Forms\Components\DatePicker::make('tanggal_selesai')
          ->required()
          ->native(false)
          ->minDate(now())
          ->maxDate(now()->addDays(5))
          ->afterOrEqual('tanggal_mulai')
          ->validationMessages([
            'after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai.',
          ])
          ->disabledDates(function () {
            $dates = [];
            // Check for 60 days to cover all visible dates in calendar
            for ($i = 0; $i <= 60; $i++) {
              $date = now()->addDays($i);
              if ($date->isWeekend()) {
                $dates[] = $date->format('Y-m-d');
              }
            }
            return $dates;
          })
          ->rules([
            function () {
              return function (string $attribute, $value, \Closure $fail) {
                if (!$value)
                  return;

                try {
                  $date = \Carbon\Carbon::parse($value);
                } catch (\Exception $e) {
                  $fail('Format tanggal tidak valid.');
                  return;
                }

                // Check if weekend
                if ($date->isWeekend()) {
                  $fail('Tanggal selesai tidak boleh di hari Sabtu atau Minggu (hari libur).');
                  return;
                }

                // Check if before today
                if ($date->lt(now()->startOfDay())) {
                  $fail('Tanggal selesai tidak boleh kurang dari hari ini.');
                  return;
                }

                // Check if beyond 5 days
                if ($date->gt(now()->addDays(5)->endOfDay())) {
                  $fail('Tanggal selesai maksimal 5 hari dari hari ini.');
                  return;
                }
              };
            },
          ])
          ->helperText('Pilih tanggal selesai izin dengan durasi maksimal 5 hari kerja dari hari ini. Sabtu dan Minggu tidak dapat dipilih karena hari libur.'),
        Forms\Components\Textarea::make('keterangan')
          ->rows(3),
        Forms\Components\Select::make('nama_piket')
          ->options(DropdownOption::getOptions(DropdownOption::CATEGORY_PEGAWAI_PIKET))
          ->searchable()
          ->placeholder('Pilih nama petugas piket')
          ->required(),
        Forms\Components\Textarea::make('tanda_tangan_piket')
          ->rows(2)
          ->required(),
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
        Tables\Columns\TextColumn::make('nomor_hp')
          ->label('No. HP')
          ->formatStateUsing(function ($state) {
            if (!$state)
              return '-';
            $cleaned = preg_replace('/[^0-9]/', '', $state);
            if (str_starts_with($cleaned, '0')) {
              $cleaned = substr($cleaned, 1);
            }
            return '+62' . $cleaned;
          })
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('jenis_izin')
          ->label('Jenis Izin')
          ->badge()
          ->formatStateUsing(fn(string $state) => PegawaiIzin::JENIS_IZIN_LABELS[$state] ?? $state)
          ->color('info'),
        Tables\Columns\TextColumn::make('tanggal_mulai')
          ->label('Mulai')
          ->since()
          ->color('gray')
          ->tooltip(fn($record) => \Carbon\Carbon::parse($record->tanggal_mulai)->format('d/m/Y'))
          ->sortable(),
        Tables\Columns\TextColumn::make('tanggal_selesai')
          ->label('Selesai')
          ->since()
          ->color('gray')
          ->tooltip(fn($record) => \Carbon\Carbon::parse($record->tanggal_selesai)->format('d/m/Y'))
          ->sortable(),
        Tables\Columns\TextColumn::make('status')
          ->badge()
          ->color(fn(string $state): string => match ($state) {
            'aktif' => 'success',
            'selesai' => 'gray',
            default => 'gray',
          })
          ->formatStateUsing(fn(string $state) => ucfirst($state)),
      ])
      ->defaultSort('tanggal_mulai', 'desc')
      ->defaultPaginationPageOption(10)
      ->paginationPageOptions([10])
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
      ->recordActions([])
      ->toolbarActions([]);
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
      'view' => Pages\ViewPegawaiIzin::route('/{record}'),
      'edit' => Pages\EditPegawaiIzin::route('/{record}/edit'),
    ];
  }
}
