<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PegawaiIzinResource\Pages;
use App\Models\Pegawai;
use App\Models\DropdownOption;
use App\Models\PegawaiIzin;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PegawaiIzinResource extends Resource
{
  protected static ?string $model = PegawaiIzin::class;

  protected static ?string $slug = 'pegawai-izin';
  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-minus';
  protected static ?string $navigationLabel = 'Izin Pegawai';
  protected static string|\UnitEnum|null $navigationGroup = 'Kepegawaian';
  protected static ?string $modelLabel = 'Izin Pegawai';
  protected static ?string $pluralModelLabel = 'Izin Pegawai';
  protected static ?int $navigationSort = 2;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->role_user && $user->role_user->hasPermission('pegawai_izin');
  }

  public static function canViewAny(): bool
  {
    return static::shouldRegisterNavigation();
  }

  public static function canCreate(): bool
  {
    return false;
  }

  public static function canEdit($record): bool
  {
    return false;
  }

  public static function canDelete($record): bool
  {
    return false;
  }

  public static function canDeleteAny(): bool
  {
    return false;
  }

  protected static function canVerifyByCurrentUser(): bool
  {
    /** @var User|null $user */
    $user = Auth::user();

    return (bool) $user && $user->hasAnyRole(['Kepala Cabang Dinas', 'Super Admin']);
  }

  protected static function canVerifyRecord(PegawaiIzin $record): bool
  {
    return static::canVerifyByCurrentUser() && $record->status === PegawaiIzin::STATUS_MENUNGGU;
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
          ->options(PegawaiIzin::STATUS_LABELS)
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
            PegawaiIzin::STATUS_DISETUJUI => 'success',
            PegawaiIzin::STATUS_AKTIF => 'success',
            PegawaiIzin::STATUS_SELESAI => 'gray',
            PegawaiIzin::STATUS_MENUNGGU => 'warning',
            PegawaiIzin::STATUS_DITOLAK => 'danger',
            default => 'gray',
          })
          ->formatStateUsing(fn(string $state) => PegawaiIzin::STATUS_LABELS[$state] ?? ucfirst($state)),
        Tables\Columns\TextColumn::make('diverifikasi_oleh')
          ->label('Verifikator KCD')
          ->placeholder('-')
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('diverifikasi_pada')
          ->label('Waktu Verifikasi')
          ->since()
          ->tooltip(fn($record) => $record->diverifikasi_pada?->format('d/m/Y H:i') ?? '-')
          ->placeholder('-')
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->modifyQueryUsing(fn(Builder $query): Builder => $query->latest())
      ->defaultSort('created_at', 'desc')
      ->defaultPaginationPageOption(10)
      ->paginationPageOptions([10])
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->options(PegawaiIzin::STATUS_LABELS),
        Tables\Filters\SelectFilter::make('jenis_izin')
          ->label('Jenis Izin')
          ->options(PegawaiIzin::JENIS_IZIN_LABELS),
      ])
      ->recordActions([
        ActionGroup::make([
          ViewAction::make()
            ->label('Lihat')
            ->icon('heroicon-o-eye')
            ->color('gray'),
          Action::make('approve')
            ->label('Setujui')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->schema([
              Textarea::make('catatan_verifikasi')
                ->label('Catatan Verifikasi')
                ->placeholder('Opsional')
                ->rows(3)
                ->maxLength(500),
            ])
            ->requiresConfirmation()
            ->modalHeading('Setujui Pengajuan Izin')
            ->modalDescription('Pengajuan ini akan diverifikasi oleh Kepala Cabang Dinas.')
            ->modalSubmitActionLabel('Setujui')
            ->visible(fn(PegawaiIzin $record): bool => static::canVerifyRecord($record))
            ->action(function (PegawaiIzin $record, array $data): void {
              if ($record->status !== PegawaiIzin::STATUS_MENUNGGU) {
                Notification::make()
                  ->warning()
                  ->title('Pengajuan sudah diproses')
                  ->body('Status pengajuan ini sudah berubah dan tidak bisa diverifikasi ulang.')
                  ->send();

                return;
              }

              $izinBerjalan = PegawaiIzin::query()
                ->where('id', '!=', $record->id)
                ->when(filled($record->nip), function ($query) use ($record) {
                  $query->where('nip', $record->nip);
                }, function ($query) use ($record) {
                  $query->where('nama_pegawai', $record->nama_pegawai);
                })
                ->whereIn('status', [PegawaiIzin::STATUS_DISETUJUI, PegawaiIzin::STATUS_AKTIF])
                ->whereDate('tanggal_selesai', '>=', now()->toDateString())
                ->exists();

              if ($izinBerjalan) {
                Notification::make()
                  ->danger()
                  ->title('Gagal menyetujui pengajuan')
                  ->body('Pegawai ini masih memiliki izin yang sedang berjalan.')
                  ->send();

                return;
              }

              $today = now()->startOfDay();
              $mulaiTanggal = $record->tanggal_mulai?->copy()?->startOfDay();
              $statusSetelahVerifikasi = $mulaiTanggal?->lessThanOrEqualTo($today)
                ? PegawaiIzin::STATUS_AKTIF
                : PegawaiIzin::STATUS_DISETUJUI;

              $record->update([
                'status' => $statusSetelahVerifikasi,
                'diverifikasi_oleh' => Auth::user()?->name,
                'diverifikasi_pada' => now(),
                'catatan_verifikasi' => blank($data['catatan_verifikasi'] ?? null)
                  ? null
                  : $data['catatan_verifikasi'],
              ]);

              Notification::make()
                ->success()
                ->title('Pengajuan disetujui')
                ->body('Pengajuan izin berhasil diverifikasi Kepala Cabang Dinas. Barcode verifikasi akan muncul otomatis di surat izin.')
                ->send();
            }),
          Action::make('reject')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->schema([
              Textarea::make('catatan_verifikasi')
                ->label('Alasan Penolakan')
                ->rows(3)
                ->required()
                ->maxLength(500),
            ])
            ->requiresConfirmation()
            ->modalHeading('Tolak Pengajuan Izin')
            ->modalDescription('Pengajuan ini akan ditandai sebagai ditolak.')
            ->modalSubmitActionLabel('Tolak')
            ->visible(fn(PegawaiIzin $record): bool => static::canVerifyRecord($record))
            ->action(function (PegawaiIzin $record, array $data): void {
              if ($record->status !== PegawaiIzin::STATUS_MENUNGGU) {
                Notification::make()
                  ->warning()
                  ->title('Pengajuan sudah diproses')
                  ->body('Status pengajuan ini sudah berubah dan tidak bisa ditolak ulang.')
                  ->send();

                return;
              }

              $record->update([
                'status' => PegawaiIzin::STATUS_DITOLAK,
                'diverifikasi_oleh' => Auth::user()?->name,
                'diverifikasi_pada' => now(),
                'catatan_verifikasi' => $data['catatan_verifikasi'],
              ]);

              Notification::make()
                ->success()
                ->title('Pengajuan ditolak')
                ->body('Status izin pegawai berhasil diperbarui menjadi ditolak.')
                ->send();
            }),
          Action::make('print')
            ->label('Cetak')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->visible(fn(PegawaiIzin $record): bool => $record->isVerifiedByKcd())
            ->url(fn(PegawaiIzin $record): string => route('admin.pegawai-izin.print', ['id' => $record->id]))
            ->openUrlInNewTab(),
        ])
          ->label(false)
          ->icon('heroicon-m-ellipsis-vertical')
          ->color('gray'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          BulkAction::make('bulk_print')
            ->label('Print PDF Terpilih')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->url(route('admin.pegawai-izin.print-bulk'))
            ->livewireClickHandlerEnabled(false)
            ->accessSelectedRecords(false)
            ->openUrlInNewTab(true)
            ->extraAttributes([
              'x-bind:href' => '`${window.location.origin}/print/pegawai-izin-bulk?ids=${[...selectedRecords].join(",")}`',
            ])
            ->visible(fn(): bool => static::canVerifyByCurrentUser())
            ->deselectRecordsAfterCompletion(),
          BulkAction::make('bulk_delete')
            ->label('Hapus Terpilih')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Hapus Data Izin Terpilih')
            ->modalDescription('Data yang dipilih akan dihapus permanen.')
            ->modalSubmitActionLabel('Hapus Semua')
            ->visible(fn(): bool => static::canVerifyByCurrentUser())
            ->action(function ($records): void {
              $deleted = 0;

              $records->each(function (PegawaiIzin $record) use (&$deleted): void {
                if ($record->delete()) {
                  $deleted++;
                }
              });

              Notification::make()
                ->title('Bulk hapus selesai')
                ->body($deleted . ' data izin berhasil dihapus.')
                ->success()
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
      'index' => Pages\ListPegawaiIzin::route('/'),
      'view' => Pages\ViewPegawaiIzin::route('/{record}'),
    ];
  }
}
