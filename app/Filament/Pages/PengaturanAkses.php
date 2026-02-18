<?php

namespace App\Filament\Pages;

use App\Models\RoleUser;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PengaturanAkses extends Page implements HasForms
{
  use InteractsWithForms;

  protected static ?string $navigationIcon = 'heroicon-o-shield-check';
  protected static ?string $navigationLabel = 'Pengaturan Akses';
  protected static ?string $navigationGroup = 'Pengguna';
  protected static ?int $navigationSort = 3;
  protected static ?string $title = 'Pengaturan Akses & Visibilitas';
  protected static string $view = 'filament.pages.pengaturan-akses';

  public ?array $kepalaCabdin = [];
  public ?array $piket = [];

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->hasRole('Super Admin');
  }

  public function mount(): void
  {
    /** @var User $user */
    $user = Auth::user();
    if (!$user || !$user->hasRole('Super Admin')) {
      abort(403);
    }

    $kepalaCabdinRole = RoleUser::where('name', 'Kepala Cabang Dinas')->first();
    $piketRole = RoleUser::where('name', 'Piket')->first();

    $kepalaCabdinPermissions = array_merge(RoleUser::getDefaultPermissions(), $kepalaCabdinRole?->permissions ?? []);
    $piketPermissions = array_merge(RoleUser::getDefaultPermissions(), $piketRole?->permissions ?? []);

    $this->form->fill([
      'kepalaCabdin' => $kepalaCabdinPermissions,
      'piket' => $piketPermissions,
    ]);
  }

  public function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Section::make('Role: Kepala Cabang Dinas')
        ->description('Atur menu yang bisa diakses dan aksi yang diizinkan untuk role Kepala Cabang Dinas di Panel Admin.')
        ->icon('heroicon-o-user')
        ->collapsible()
        ->schema([
          Forms\Components\Fieldset::make('Visibilitas Menu')
            ->schema([
              Forms\Components\Toggle::make('kepalaCabdin.buku_tamu')
                ->label('Buku Tamu')
                ->helperText('Halaman data buku tamu'),
              Forms\Components\Toggle::make('kepalaCabdin.activity_log')
                ->label('Log Aktivitas')
                ->helperText('Halaman log aktivitas sistem'),
              Forms\Components\Toggle::make('kepalaCabdin.pegawai_izin')
                ->label('Izin Pegawai')
                ->helperText('Halaman data izin pegawai'),
              Forms\Components\Toggle::make('kepalaCabdin.rekap_izin')
                ->label('Rekap Izin Pegawai')
                ->helperText('Halaman rekap dan statistik izin pegawai'),
              Forms\Components\Toggle::make('kepalaCabdin.data_pegawai')
                ->label('Data Pegawai')
                ->helperText('Halaman data pegawai'),
              Forms\Components\Toggle::make('kepalaCabdin.dropdown_options')
                ->label('Manajemen Buku Tamu')
                ->helperText('Halaman pengaturan dropdown buku tamu'),
              Forms\Components\Toggle::make('kepalaCabdin.pegawai_piket')
                ->label('Data Pegawai Piket')
                ->helperText('Halaman data pegawai piket'),
              Forms\Components\Toggle::make('kepalaCabdin.user_management')
                ->label('Manajemen User')
                ->helperText('Halaman kelola user dan role'),
              Forms\Components\Toggle::make('kepalaCabdin.profil_kepala_cabdin')
                ->label('Profil Kepala Cabang Dinas')
                ->helperText('Halaman profil data diri Kepala Cabang Dinas (perubahan perlu verifikasi Super Admin)'),
              Forms\Components\Toggle::make('kepalaCabdin.riwayat_tamu')
                ->label('Riwayat Pengunjung')
                ->helperText('Halaman riwayat pengunjung di panel admin'),
            ])
            ->columns(2),
          Forms\Components\Fieldset::make('Aksi')
            ->schema([
              Forms\Components\Toggle::make('kepalaCabdin.can_print')
                ->label('Cetak / Print')
                ->helperText('Izinkan mencetak data buku tamu dan laporan'),
              Forms\Components\Toggle::make('kepalaCabdin.can_change_status')
                ->label('Ubah Status Tamu')
                ->helperText('Izinkan mengubah status kunjungan tamu'),
            ])
            ->columns(2),
        ]),

      Forms\Components\Section::make('Role: Piket')
        ->description('Atur menu yang bisa diakses dan aksi yang diizinkan untuk role Piket di Panel Piket.')
        ->icon('heroicon-o-user-group')
        ->collapsible()
        ->schema([
          Forms\Components\Fieldset::make('Visibilitas Menu')
            ->schema([
              Forms\Components\Toggle::make('piket.buku_tamu')
                ->label('Kunjungan Tamu')
                ->helperText('Halaman kunjungan tamu di panel piket'),
              Forms\Components\Toggle::make('piket.pengantar_berkas')
                ->label('Pengantar Berkas')
                ->helperText('Halaman daftar pengantar berkas di panel piket'),
              Forms\Components\Toggle::make('piket.riwayat_tamu')
                ->label('Riwayat Pengunjung')
                ->helperText('Halaman riwayat pengunjung di panel piket'),
              Forms\Components\Toggle::make('piket.pegawai_izin')
                ->label('Izin Pegawai')
                ->helperText('Halaman izin pegawai di panel piket'),
            ])
            ->columns(2),
          Forms\Components\Fieldset::make('Aksi')
            ->schema([
              Forms\Components\Toggle::make('piket.can_print')
                ->label('Cetak / Print')
                ->helperText('Izinkan mencetak data dari panel piket'),
              Forms\Components\Toggle::make('piket.can_change_status')
                ->label('Ubah Status')
                ->helperText('Izinkan mengubah status kunjungan'),
            ])
            ->columns(2),
        ]),
    ]);
  }

  public function save(): void
  {
    $data = $this->form->getState();

    $kepalaCabdinRole = RoleUser::where('name', 'Kepala Cabang Dinas')->first();
    $piketRole = RoleUser::where('name', 'Piket')->first();

    if ($kepalaCabdinRole) {
      $kepalaCabdinRole->update(['permissions' => $data['kepalaCabdin']]);
    }

    if ($piketRole) {
      $piketRole->update(['permissions' => $data['piket']]);
    }

    activity()
      ->causedBy(Auth::user())
      ->useLog('pengaturan_akses')
      ->event('updated')
      ->log('Pengaturan akses role diperbarui');

    Notification::make()
      ->success()
      ->title('Pengaturan akses berhasil disimpan!')
      ->body('Visibilitas menu dan pengaturan aksi telah diperbarui untuk semua role.')
      ->send();
  }
}
