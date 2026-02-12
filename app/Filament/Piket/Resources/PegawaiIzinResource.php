<?php

namespace App\Filament\Piket\Resources;

use App\Filament\Piket\Resources\PegawaiIzinResource\Pages;
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
        Forms\Components\TextInput::make('nip')
          ->label('NIP')
          ->required()
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
            $status = $length === 18 ? 'valid' : 'invalid';
            return "{$length} digit — {$status}";
          })
          ->afterStateUpdated(function ($state, Forms\Set $set) {
            if (strlen($state) === 18) {
              $pegawai = PegawaiIzin::where('nip', $state)
                ->orderBy('tanggal_selesai', 'desc')
                ->first();

              if ($pegawai) {
                $set('nama_pegawai', $pegawai->nama_pegawai);
                $set('jabatan', $pegawai->jabatan);
                $set('unit_kerja', $pegawai->unit_kerja);

                // Set tanggal mulai berdasarkan tanggal selesai izin terakhir
                $tanggalMulai = \Carbon\Carbon::parse($pegawai->tanggal_selesai)->addDay();

                // Jika tanggal selesai terakhir kurang dari hari ini, pakai hari ini
                if ($tanggalMulai->lt(now())) {
                  $tanggalMulai = now();
                }

                $set('tanggal_mulai', $tanggalMulai->format('Y-m-d'));
              }
            }
          }),
        Forms\Components\TextInput::make('nama_pegawai')
          ->label('Nama Pegawai')
          ->required()
          ->maxLength(255),
        Forms\Components\TextInput::make('jabatan')
          ->maxLength(255),
        Forms\Components\TextInput::make('unit_kerja')
          ->label('Unit Kerja')
          ->maxLength(255),
        Forms\Components\TextInput::make('nomor_hp')
          ->label('No. HP')
          ->tel()
          ->prefix('+62')
          ->placeholder('8xxx')
          ->maxLength(15),
        Forms\Components\Select::make('jenis_izin')
          ->label('Jenis Izin')
          ->options(PegawaiIzin::JENIS_IZIN_LABELS)
          ->searchable()
          ->required(),
        Forms\Components\TextInput::make('status')
          ->label('Status')
          ->default('aktif')
          ->disabled()
          ->dehydrated()
          ->formatStateUsing(fn($state) => 'Aktif')
          ->visible(fn($context) => $context === 'create'),
        Forms\Components\Select::make('status')
          ->label('Status')
          ->options([
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
          ])
          ->required()
          ->visible(fn($context) => $context === 'edit'),
        Forms\Components\DatePicker::make('tanggal_mulai')
          ->label('Tanggal Mulai')
          ->required()
          ->default(now())
          ->disabled()
          ->dehydrated(),
        Forms\Components\DatePicker::make('tanggal_selesai')
          ->label('Tanggal Selesai')
          ->required()
          ->native(false)
          ->minDate(now())
          ->maxDate(now()->addDays(5))
          ->afterOrEqual('tanggal_mulai')
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
                if (!$value) return;

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
          ->helperText('Maksimal 5 hari dari tanggal mulai (tidak termasuk Sabtu & Minggu)'),
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
        Tables\Columns\TextColumn::make('nomor_hp')
          ->label('No. HP')
          ->formatStateUsing(function ($state) {
            if (!$state) return '-';
            $cleaned = preg_replace('/[^0-9]/', '', $state);
            if (str_starts_with($cleaned, '0')) {
              $cleaned = substr($cleaned, 1);
            }
            return '+62' . $cleaned;
          })
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\BadgeColumn::make('jenis_izin')
          ->label('Jenis Izin')
          ->formatStateUsing(fn(string $state) => PegawaiIzin::JENIS_IZIN_LABELS[$state] ?? $state)
          ->color('info'),
        Tables\Columns\TextColumn::make('tanggal_mulai')
          ->label('Mulai')
          ->since()
          ->tooltip(fn($record) => \Carbon\Carbon::parse($record->tanggal_mulai)->format('d/m/Y'))
          ->sortable(),
        Tables\Columns\TextColumn::make('tanggal_selesai')
          ->label('Selesai')
          ->since()
          ->tooltip(fn($record) => \Carbon\Carbon::parse($record->tanggal_selesai)->format('d/m/Y'))
          ->sortable(),
        Tables\Columns\BadgeColumn::make('status')
          ->colors([
            'success' => 'aktif',
            'gray' => 'selesai',
          ])
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
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make(),
          Tables\Actions\DeleteAction::make(),
        ])
          ->label(false)
          ->icon('heroicon-m-ellipsis-vertical')
          ->button()
          ->color('gray'),
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
      'view' => Pages\ViewPegawaiIzin::route('/{record}'),
      'edit' => Pages\EditPegawaiIzin::route('/{record}/edit'),
    ];
  }
}
