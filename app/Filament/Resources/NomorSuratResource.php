<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NomorSuratResource\Pages;
use App\Models\NomorSuratSetting;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class NomorSuratResource extends Resource
{
  protected static ?string $model = NomorSuratSetting::class;

  protected static ?string $slug = 'nomor-surat';
  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel = 'Nomor Surat';
  protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
  protected static ?string $modelLabel = 'Nomor Surat';
  protected static ?string $pluralModelLabel = 'Pengaturan Nomor Surat';
  protected static ?int $navigationSort = 15;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->hasRole('Super Admin');
  }

  public static function form(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Informasi Jenis Surat')
        ->columnSpanFull()
        ->schema([
          Forms\Components\Select::make('jenis_surat')
            ->required()
            ->searchable()
            ->noOptionsMessage('Belum ada opsi tersedia.')
            ->noSearchResultsMessage('Data tidak ditemukan.')
            ->options([
              'buku_tamu' => 'buku_tamu - Bukti Kunjungan Tamu',
              'surat_izin' => 'surat_izin - Surat Izin Pegawai',
              'surat_tugas' => 'surat_tugas - Surat Tugas',
              'surat_keterangan' => 'surat_keterangan - Surat Keterangan',
              'surat_undangan' => 'surat_undangan - Surat Undangan',
              'surat_keputusan' => 'surat_keputusan - Surat Keputusan',
              'surat_perintah' => 'surat_perintah - Surat Perintah',
              'surat_pengajuan' => 'surat_pengajuan - Surat Pengajuan',
              'surat_permohonan' => 'surat_permohonan - Surat Permohonan',
              'surat_pemberitahuan' => 'surat_pemberitahuan - Surat Pemberitahuan',
              'nota_dinas' => 'nota_dinas - Nota Dinas',
              'berita_acara' => 'berita_acara - Berita Acara',
            ])
            ->createOptionForm([
              Forms\Components\TextInput::make('custom_jenis')
                ->required()
                ->placeholder('contoh: surat_rekomendasi')
                ->helperText('Gunakan underscore, lowercase, tanpa spasi')
                ->maxLength(50),
            ])
            ->createOptionUsing(function (array $data) {
              return $data['custom_jenis'];
            })
            ->createOptionAction(fn(Action $action): Action => $action
              ->label('Tambah Kode Baru')
              ->modalHeading('Tambah Kode Jenis Surat')
              ->modalSubmitActionLabel('Tambah'))
            ->helperText('Pilih dari daftar atau ketik untuk mencari. Klik "Tambah" untuk menambah kode baru.')
            ->unique(ignoreRecord: true),
          Forms\Components\TextInput::make('nama_jenis')
            ->required()
            ->maxLength(100)
            ->placeholder('contoh: Bukti Kunjungan Tamu'),
        ])
        ->columns(1),

      Section::make('Pengaturan Template Nomor')
        ->schema([
          Forms\Components\Select::make('template')
            ->required()
            ->searchable()
            ->noOptionsMessage('Belum ada template tersedia.')
            ->noSearchResultsMessage('Template tidak ditemukan.')
            ->options([
              // Format dengan slash (/)
              '{NOMOR}/{KODE}/{BULAN}/{TAHUN}' => '{NOMOR}/{KODE}/{BULAN}/{TAHUN} - contoh: 000001/BT/02/2026',
              '{NOMOR}/{KODE}/{ROMAWI}/{TAHUN}' => '{NOMOR}/{KODE}/{ROMAWI}/{TAHUN} - contoh: 000001/BT/II/2026',
              '{NOMOR}/{KODE}/{TAHUN}' => '{NOMOR}/{KODE}/{TAHUN} - contoh: 000001/BT/2026',
              '{KODE}/{NOMOR}/{BULAN}/{TAHUN}' => '{KODE}/{NOMOR}/{BULAN}/{TAHUN} - contoh: BT/000001/02/2026',
              '{KODE}/{NOMOR}/{ROMAWI}/{TAHUN}' => '{KODE}/{NOMOR}/{ROMAWI}/{TAHUN} - contoh: BT/000001/II/2026',
              '{KODE}/{NOMOR}/{TAHUN}' => '{KODE}/{NOMOR}/{TAHUN} - contoh: BT/000001/2026',
              '{NOMOR}/{BULAN}/{KODE}/{TAHUN}' => '{NOMOR}/{BULAN}/{KODE}/{TAHUN} - contoh: 000001/02/BT/2026',
              '{NOMOR}/{ROMAWI}/{KODE}/{TAHUN}' => '{NOMOR}/{ROMAWI}/{KODE}/{TAHUN} - contoh: 000001/II/BT/2026',

              // Format dengan dash/hyphen (-)
              '{KODE}-{NOMOR}/{BULAN}/{TAHUN}' => '{KODE}-{NOMOR}/{BULAN}/{TAHUN} - contoh: BT-000001/02/2026',
              '{KODE}-{NOMOR}/{ROMAWI}/{TAHUN}' => '{KODE}-{NOMOR}/{ROMAWI}/{TAHUN} - contoh: BT-000001/II/2026',
              '{KODE}-{NOMOR}/{TAHUN}' => '{KODE}-{NOMOR}/{TAHUN} - contoh: BT-000001/2026',
              '{NOMOR}/{KODE}-{BULAN}-{TAHUN}' => '{NOMOR}/{KODE}-{BULAN}-{TAHUN} - contoh: 000001/BT-02-2026',
              '{NOMOR}-{KODE}-{BULAN}-{TAHUN}' => '{NOMOR}-{KODE}-{BULAN}-{TAHUN} - contoh: 000001-BT-02-2026',
              '{NOMOR}-{KODE}-{ROMAWI}-{TAHUN}' => '{NOMOR}-{KODE}-{ROMAWI}-{TAHUN} - contoh: 000001-BT-II-2026',
              '{KODE}-{NOMOR}-{BULAN}-{TAHUN}' => '{KODE}-{NOMOR}-{BULAN}-{TAHUN} - contoh: BT-000001-02-2026',
              '{KODE}-{NOMOR}-{ROMAWI}-{TAHUN}' => '{KODE}-{NOMOR}-{ROMAWI}-{TAHUN} - contoh: BT-000001-II-2026',

              // Format dengan dot/titik (.)
              '{KODE}.{NOMOR}/{BULAN}/{TAHUN}' => '{KODE}.{NOMOR}/{BULAN}/{TAHUN} - contoh: BT.000001/02/2026',
              '{KODE}.{NOMOR}/{ROMAWI}/{TAHUN}' => '{KODE}.{NOMOR}/{ROMAWI}/{TAHUN} - contoh: BT.000001/II/2026',
              '{NOMOR}.{KODE}.{BULAN}.{TAHUN}' => '{NOMOR}.{KODE}.{BULAN}.{TAHUN} - contoh: 000001.BT.02.2026',
              '{NOMOR}.{KODE}.{ROMAWI}.{TAHUN}' => '{NOMOR}.{KODE}.{ROMAWI}.{TAHUN} - contoh: 000001.BT.II.2026',
              '{KODE}.{NOMOR}.{BULAN}.{TAHUN}' => '{KODE}.{NOMOR}.{BULAN}.{TAHUN} - contoh: BT.000001.02.2026',
              '{KODE}.{NOMOR}.{ROMAWI}.{TAHUN}' => '{KODE}.{NOMOR}.{ROMAWI}.{TAHUN} - contoh: BT.000001.II.2026',

              // Format dengan tahun pendek
              '{NOMOR}/{KODE}/{BULAN}/{TAHUN_PENDEK}' => '{NOMOR}/{KODE}/{BULAN}/{TAHUN_PENDEK} - contoh: 000001/BT/02/26',
              '{NOMOR}/{KODE}/{ROMAWI}/{TAHUN_PENDEK}' => '{NOMOR}/{KODE}/{ROMAWI}/{TAHUN_PENDEK} - contoh: 000001/BT/II/26',
              '{KODE}-{NOMOR}/{BULAN}/{TAHUN_PENDEK}' => '{KODE}-{NOMOR}/{BULAN}/{TAHUN_PENDEK} - contoh: BT-000001/02/26',
              '{KODE}-{NOMOR}/{ROMAWI}/{TAHUN_PENDEK}' => '{KODE}-{NOMOR}/{ROMAWI}/{TAHUN_PENDEK} - contoh: BT-000001/II/26',

              // Format compact/singkat
              '{KODE}{NOMOR}/{BULAN}{TAHUN_PENDEK}' => '{KODE}{NOMOR}/{BULAN}{TAHUN_PENDEK} - contoh: BT000001/0226',
              '{KODE}{NOMOR}.{BULAN}.{TAHUN_PENDEK}' => '{KODE}{NOMOR}.{BULAN}.{TAHUN_PENDEK} - contoh: BT000001.02.26',
              '{NOMOR}{KODE}/{BULAN}/{TAHUN_PENDEK}' => '{NOMOR}{KODE}/{BULAN}/{TAHUN_PENDEK} - contoh: 000001BT/02/26',

              // Format dengan pemisah kosong
              '{NOMOR} {KODE} {BULAN} {TAHUN}' => '{NOMOR} {KODE} {BULAN} {TAHUN} - contoh: 000001 BT 02 2026',
              '{KODE} {NOMOR} {ROMAWI} {TAHUN}' => '{KODE} {NOMOR} {ROMAWI} {TAHUN} - contoh: BT 000001 II 2026',
            ])
            ->createOptionForm([
              Forms\Components\TextInput::make('custom_template')
                ->required()
                ->placeholder('{NOMOR}/{KODE}/{BULAN}/{TAHUN}')
                ->helperText('Gunakan placeholder: {NOMOR}, {KODE}, {BULAN}, {TAHUN}, {TAHUN_PENDEK}, {ROMAWI}')
                ->maxLength(255),
            ])
            ->createOptionUsing(function (array $data) {
              return $data['custom_template'];
            })
            ->createOptionAction(fn(Action $action): Action => $action
              ->label('Tambah Template')
              ->modalHeading('Tambah Template Custom')
              ->modalSubmitActionLabel('Tambah'))
            ->helperText('Pilih format template atau ketik untuk mencari. Klik "Tambah" untuk template custom.'),
          Forms\Components\Select::make('kode_surat')
            ->required()
            ->searchable()
            ->noOptionsMessage('Belum ada kode surat tersedia.')
            ->noSearchResultsMessage('Kode surat tidak ditemukan.')
            ->options([
              'BT' => 'BT - Buku Tamu',
              'SI' => 'SI - Surat Izin',
              'ST' => 'ST - Surat Tugas',
              'SK' => 'SK - Surat Keputusan',
              'SKET' => 'SKET - Surat Keterangan',
              'SU' => 'SU - Surat Undangan',
              'SPR' => 'SPR - Surat Perintah',
              'SPJ' => 'SPJ - Surat Pengajuan',
              'SPH' => 'SPH - Surat Permohonan',
              'SPB' => 'SPB - Surat Pemberitahuan',
              'ND' => 'ND - Nota Dinas',
              'BA' => 'BA - Berita Acara',
              'SR' => 'SR - Surat Rekomendasi',
              'SPPD' => 'SPPD - Surat Perintah Perjalanan Dinas',
            ])
            ->createOptionForm([
              Forms\Components\TextInput::make('custom_kode')
                ->required()
                ->placeholder('contoh: SPM')
                ->helperText('Singkatan surat (huruf kapital)')
                ->maxLength(20),
            ])
            ->createOptionUsing(function (array $data) {
              return strtoupper($data['custom_kode']);
            })
            ->createOptionAction(fn(Action $action): Action => $action
              ->label('Tambah Kode Surat')
              ->modalHeading('Tambah Kode Surat Baru')
              ->modalSubmitActionLabel('Tambah'))
            ->helperText('Pilih atau ketik kode singkatan surat. Klik "Tambah" untuk menambah kode baru.'),
          Forms\Components\TextInput::make('padding_length')
            ->label('Panjang Padding')
            ->numeric()
            ->required()
            ->minValue(1)
            ->maxValue(10)
            ->helperText('Jumlah digit nomor urut (misal: 6 = 000001).'),
        ])
        ->columns(2),

      Section::make('Keterangan & Status')
        ->schema([
          Forms\Components\Textarea::make('keterangan')
            ->rows(3)
            ->placeholder('Informasi tambahan tentang format nomor surat...'),
          Forms\Components\Toggle::make('is_active')
            ->label('Status Aktif')
            ->helperText('Hanya template aktif yang akan digunakan.')
            ->inline(false),
        ]),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('nama_jenis')
          ->label('Jenis Surat')
          ->searchable()
          ->sortable()
          ->weight('medium'),
        Tables\Columns\TextColumn::make('template')
          ->label('Template')
          ->searchable()
          ->copyable()
          ->badge()
          ->color('info'),
        Tables\Columns\TextColumn::make('kode_surat')
          ->label('Kode')
          ->badge()
          ->color('primary'),
        Tables\Columns\TextColumn::make('padding_length')
          ->label('Padding')
          ->alignCenter()
          ->badge()
          ->color('gray'),
        Tables\Columns\IconColumn::make('is_active')
          ->label('Aktif')
          ->boolean()
          ->sortable(),
        Tables\Columns\TextColumn::make('updated_at')
          ->label('Terakhir diubah')
          ->since()
          ->color('gray')
          ->sortable(),
      ])
      ->defaultSort('nama_jenis')
      ->filters([
        Tables\Filters\TernaryFilter::make('is_active')
          ->label('Status')
          ->trueLabel('Aktif')
          ->falseLabel('Nonaktif'),
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
      'index' => Pages\ListNomorSurats::route('/'),
      'create' => Pages\CreateNomorSurat::route('/create'),
      'edit' => Pages\EditNomorSurat::route('/{record}/edit'),
    ];
  }
}
