<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PegawaiIzinResource\Pages;
use App\Models\Pegawai;
use App\Models\PegawaiIzin;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PegawaiIzinResource extends Resource
{
  protected static ?string $model = PegawaiIzin::class;

  protected static ?string $slug = 'pegawai-izin';
  protected static ?string $navigationIcon = 'heroicon-o-user-minus';
  protected static ?string $navigationLabel = 'Izin Pegawai';
  protected static ?string $navigationGroup = 'Kepegawaian';
  protected static ?string $modelLabel = 'Izin Pegawai';
  protected static ?string $pluralModelLabel = 'Izin Pegawai';
  protected static ?int $navigationSort = 2;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->hasRole('Super Admin');
  }

  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Section::make()->columns(2)->schema([
        Forms\Components\Select::make('pegawai_id')
          ->label('Pilih Pegawai')
          ->options(function () {
            return Pegawai::active()
              ->orderBy('nama')
              ->get()
              ->mapWithKeys(fn($p) => [$p->id => "{$p->nama} — {$p->nip}"]);
          })
          ->searchable()
          ->preload()
          ->placeholder('Cari nama atau NIP pegawai...')
          ->live()
          ->afterStateUpdated(function ($state, Forms\Set $set) {
            if (!$state) return;

            $pegawai = Pegawai::find($state);
            if (!$pegawai) return;

            $set('nama_pegawai', $pegawai->nama);
            $set('nip', $pegawai->nip);
            $set('jabatan', $pegawai->jabatan);
            $set('unit_kerja', $pegawai->unit_kerja);
            $set('nomor_hp', $pegawai->nomor_hp);

            // Cek apakah ada surat izin aktif
            $suratAktif = PegawaiIzin::where('nip', $pegawai->nip)
              ->where('status', 'aktif')
              ->where('tanggal_selesai', '>=', now()->toDateString())
              ->orderBy('tanggal_selesai', 'desc')
              ->first();

            if ($suratAktif) {
              $jenisIzin = ucfirst($suratAktif->jenis_izin);
              $tanggalMulai = $suratAktif->tanggal_mulai->translatedFormat('d F Y');
              $tanggalSelesai = $suratAktif->tanggal_selesai->translatedFormat('d F Y');
              $besok = $suratAktif->tanggal_selesai->addDay()->translatedFormat('d F Y');

              \Filament\Notifications\Notification::make()
                ->warning()
                ->title('⚠️ Pegawai Masih Memiliki Surat Izin Aktif')
                ->body("Pegawai {$suratAktif->nama_pegawai} masih memiliki surat izin {$jenisIzin} yang berlaku dari {$tanggalMulai} sampai {$tanggalSelesai}. Surat izin baru dapat dibuat mulai tanggal {$besok}.")
                ->persistent()
                ->send();
            }
          })
          ->helperText('Pilih pegawai dari daftar untuk mengisi data otomatis.')
          ->columnSpanFull()
          ->dehydrated(false),
        Forms\Components\TextInput::make('nama_pegawai')
          ->label('Nama Pegawai')
          ->required()
          ->maxLength(255)
          ->readOnly(),
        Forms\Components\TextInput::make('nip')
          ->label('NIP')
          ->required()
          ->minLength(18)
          ->maxLength(18)
          ->placeholder('Masukkan 18 digit NIP')
          ->mask('999999999999999999')
          ->readOnly()
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
            if (!$state) return 'Otomatis terisi dari pilihan pegawai';
            $length = strlen($state);
            $status = $length === 18 ? 'valid' : 'invalid';
            return "{$length} digit — {$status}";
          }),
        Forms\Components\TextInput::make('jabatan')
          ->maxLength(255)
          ->readOnly(),
        Forms\Components\TextInput::make('unit_kerja')
          ->label('Unit Kerja')
          ->maxLength(255)
          ->readOnly(),
        Forms\Components\TextInput::make('nomor_hp')
          ->label('Nomor Handphone')
          ->tel()
          ->prefix('+62')
          ->placeholder('8xx-xxxx-xxxx')
          ->mask('999-9999-99999')
          ->maxLength(15)
          ->readOnly()
          ->helperText('Otomatis terisi dari data pegawai'),
        Forms\Components\Select::make('jenis_izin')
          ->label('Jenis Izin')
          ->options(PegawaiIzin::JENIS_IZIN_LABELS)
          ->searchable()
          ->required(),
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
          ->helperText('Pilih tanggal selesai izin dengan durasi maksimal 5 hari kerja dari hari ini. Sabtu dan Minggu tidak dapat dipilih karena hari libur.'),
        Forms\Components\Textarea::make('keterangan')
          ->rows(3)
          ->columnSpanFull(),
        Forms\Components\TextInput::make('nama_piket')
          ->label('Nama Piket')
          ->placeholder('Masukkan nama petugas piket')
          ->required()
          ->maxLength(255)
          ->columnSpanFull(),
        Forms\Components\ViewField::make('tanda_tangan_piket')
          ->label('Tanda Tangan Piket (Konfirmasi)')
          ->view('filament.forms.components.signature-pad')
          ->required()
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
        Tables\Columns\TextColumn::make('jenis_izin')
          ->label('Jenis Izin')
          ->badge()
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
