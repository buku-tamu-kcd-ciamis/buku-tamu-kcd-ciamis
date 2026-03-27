<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Concerns\ChecksStaffPermission;
use App\Filament\Staff\Resources\PegawaiIzinResource\Pages;
use App\Models\User;
use App\Models\PegawaiIzin;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PegawaiIzinResource extends Resource
{
    use ChecksStaffPermission;

    protected static ?string $model = PegawaiIzin::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Izin Saya';
    protected static string|\UnitEnum|null $navigationGroup = 'Kepegawaian';
    protected static ?string $modelLabel = 'Izin';
    protected static ?string $pluralModelLabel = 'Izin Saya';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return static::hasStaffPermission('pegawai_izin');
    }

    public static function canViewAny(): bool
    {
        return static::hasStaffPermission('pegawai_izin');
    }

    public static function canCreate(): bool
    {
        return static::hasStaffPermission('pegawai_izin');
    }

    public static function resolveIdentityData(?User $user = null): array
    {
        /** @var User|null $user */
        $user = $user ?? Auth::user();
        $pegawai = $user?->pegawai;

        return [
            'nama_pegawai' => $pegawai?->nama ?? $user?->name ?? '',
            'nip' => $pegawai?->nip ?? null,
            'jabatan' => $pegawai?->jabatan ?? null,
            'unit_kerja' => $pegawai?->unit_kerja ?? null,
            'nomor_hp' => $pegawai?->nomor_hp ?? null,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $identityData = static::resolveIdentityData($user);

        return $schema
            ->components([
                Forms\Components\Section::make('Informasi Pegawai')
                    ->description('Data identitas dikunci dari akun login Anda')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('nama_pegawai')
                            ->label('Nama Pegawai')
                            ->default($identityData['nama_pegawai'])
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->default($identityData['nip'])
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(18),
                        Forms\Components\TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->default($identityData['jabatan'])
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('unit_kerja')
                            ->label('Unit Kerja')
                            ->default($identityData['unit_kerja'])
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nomor_hp')
                            ->label('Nomor HP')
                            ->default($identityData['nomor_hp'])
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Izin')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Forms\Components\Select::make('jenis_izin')
                            ->label('Jenis Izin')
                            ->options(PegawaiIzin::JENIS_IZIN_LABELS)
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now())
                            ->disabled()
                            ->dehydrated()
                            ->native(false),
                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->native(false)
                            ->minDate(now())
                            ->maxDate(now()->addDays(5))
                            ->afterOrEqual('tanggal_mulai')
                            ->disabledDates(function () {
                                $dates = [];
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

                                        if ($date->isWeekend()) {
                                            $fail('Tanggal selesai tidak boleh di hari Sabtu atau Minggu (hari libur).');
                                            return;
                                        }

                                        if ($date->lt(now()->startOfDay())) {
                                            $fail('Tanggal selesai tidak boleh kurang dari hari ini.');
                                            return;
                                        }

                                        if ($date->gt(now()->addDays(5)->endOfDay())) {
                                            $fail('Tanggal selesai maksimal 5 hari dari hari ini.');
                                            return;
                                        }
                                    };
                                },
                            ])
                            ->helperText('Pilih tanggal selesai izin dengan durasi maksimal 5 hari kerja dari hari ini. Sabtu dan Minggu tidak dapat dipilih karena hari libur.'),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $identityData = static::resolveIdentityData($user);

        return $table
            ->query(
                PegawaiIzin::query()
                    ->when(filled($identityData['nip']), function ($query) use ($identityData) {
                        $query->where('nip', $identityData['nip']);
                    }, function ($query) use ($identityData) {
                        if (blank($identityData['nama_pegawai'])) {
                            $query->whereRaw('1 = 0');

                            return;
                        }

                        $query->where('nama_pegawai', $identityData['nama_pegawai']);
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('jenis_izin')
                    ->label('Jenis Izin')
                    ->badge()
                    ->formatStateUsing(fn($state) => PegawaiIzin::JENIS_IZIN_LABELS[$state] ?? $state)
                    ->colors([
                        'danger' => 'sakit',
                        'primary' => 'cuti',
                        'success' => 'dinas_luar',
                        'warning' => 'izin_pribadi',
                        'gray' => 'lainnya',
                    ]),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => PegawaiIzin::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'warning' => 'menunggu',
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                        'primary' => 'aktif',
                        'gray' => 'selesai',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Pengajuan')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
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
        ];
    }
}
