<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\PegawaiIzinResource\Pages;
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
    protected static ?string $model = PegawaiIzin::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Izin Saya';
    protected static string|\UnitEnum|null $navigationGroup = 'Kepegawaian';
    protected static ?string $modelLabel = 'Izin';
    protected static ?string $pluralModelLabel = 'Izin Saya';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $pegawai = $user->pegawai;

        return $schema
            ->components([
                Forms\Components\Section::make('Informasi Pegawai')
                    ->description('Data diambil dari profil pegawai Anda')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('nama_pegawai')
                            ->label('Nama Pegawai')
                            ->default($pegawai?->nama ?? $user->name)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->default($pegawai?->nip ?? '')
                            ->maxLength(18),
                        Forms\Components\TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->default($pegawai?->jabatan ?? '')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('unit_kerja')
                            ->label('Unit Kerja')
                            ->default($pegawai?->unit_kerja ?? '')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nomor_hp')
                            ->label('Nomor HP')
                            ->default($pegawai?->nomor_hp ?? '')
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
        $pegawai = $user->pegawai;

        return $table
            ->query(
                PegawaiIzin::query()
                    ->when($pegawai, function ($query) use ($pegawai) {
                        // Filter by pegawai's own data
                        $query->where('nama_pegawai', $pegawai->nama)
                            ->orWhere('nip', $pegawai->nip);
                    }, function ($query) use ($user) {
                        // Fallback: filter by user name
                        $query->where('nama_pegawai', $user->name);
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
