<?php

namespace App\Filament\Pages;

use App\Models\PengaturanKcd;
use App\Models\ProfileChangeRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class ProfilKetuaKcd extends Page implements HasForms
{
  use InteractsWithForms;
  use WithPagination;

  protected static ?string $navigationIcon = 'heroicon-o-user-circle';
  protected static ?string $navigationLabel = 'Profil Ketua KCD';
  protected static ?string $navigationGroup = 'Akun';
  protected static ?string $title = 'Profil Data Diri Ketua KCD';
  protected static ?int $navigationSort = 1;
  protected static string $view = 'filament.pages.profil-ketua-kcd';

  public ?array $data = [];

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    if (!$user) return false;

    // Super Admin selalu bisa akses
    if ($user->hasRole('Super Admin')) return true;

    // Ketua KCD cek permission
    if ($user->hasRole('Ketua KCD')) {
      return $user->role_user && $user->role_user->hasPermission('profil_ketua_kcd');
    }

    return false;
  }

  public function mount(): void
  {
    /** @var User $user */
    $user = Auth::user();
    if (!$user) abort(403);

    $isSuperAdmin = $user->hasRole('Super Admin');
    $isKetuaKcd = $user->hasRole('Ketua KCD');

    if (!$isSuperAdmin && !$isKetuaKcd) abort(403);
    if ($isKetuaKcd && !$user->role_user?->hasPermission('profil_ketua_kcd')) abort(403);

    $settings = PengaturanKcd::getSettings();

    $this->form->fill([
      'nama_ketua' => $settings->nama_ketua,
      'nip_ketua' => $settings->nip_ketua,
      'jabatan' => $settings->jabatan,
    ]);
  }

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make('Data Diri Ketua KCD')
          ->description('Data ini digunakan pada tanda tangan di halaman cetak/print.')
          ->icon('heroicon-o-identification')
          ->schema([
            Forms\Components\TextInput::make('nama_ketua')
              ->label('Nama Lengkap')
              ->placeholder('Contoh: Drs. H. Ahmad Suryadi, M.Pd.')
              ->maxLength(255)
              ->helperText('Nama lengkap beserta gelar depan dan belakang.')
              ->required(),
            Forms\Components\TextInput::make('nip_ketua')
              ->label('NIP')
              ->placeholder('Contoh: 196712051992031005')
              ->mask('999999999999999999')
              ->length(18)
              ->helperText('NIP harus terdiri dari 18 digit.')
              ->required(),
            Forms\Components\TextInput::make('jabatan')
              ->label('Jabatan')
              ->default('Kepala Cabang Dinas Pendidikan Wilayah XIII')
              ->maxLength(255)
              ->helperText('Jabatan yang ditampilkan di halaman cetak.')
              ->required(),
          ])
          ->columns(1),
      ])
      ->statePath('data');
  }

  /**
   * Simpan / Ajukan perubahan
   */
  public function save(): void
  {
    $formData = $this->form->getState();

    /** @var User $user */
    $user = Auth::user();
    $settings = PengaturanKcd::getSettings();

    // Super Admin → langsung simpan tanpa verifikasi
    if ($user->hasRole('Super Admin')) {
      $settings->update($formData);

      activity()
        ->causedBy($user)
        ->performedOn($settings)
        ->useLog('pengaturan')
        ->event('updated')
        ->withProperties(['attributes' => $formData])
        ->log('Data Ketua KCD diperbarui langsung oleh Super Admin');

      Notification::make()
        ->success()
        ->title('Data berhasil disimpan!')
        ->body('Perubahan langsung diterapkan.')
        ->send();

      return;
    }

    // Ketua KCD → cek apakah masih ada pengajuan pending
    $existingPending = ProfileChangeRequest::where('user_id', $user->id)
      ->where('status', ProfileChangeRequest::STATUS_PENDING)
      ->exists();

    if ($existingPending) {
      Notification::make()
        ->warning()
        ->title('Masih ada pengajuan yang menunggu verifikasi')
        ->body('Tunggu Super Admin memproses pengajuan sebelumnya terlebih dahulu.')
        ->send();
      return;
    }

    // Cek apakah data berubah
    $oldData = [
      'nama_ketua' => $settings->nama_ketua,
      'nip_ketua' => $settings->nip_ketua,
      'jabatan' => $settings->jabatan,
    ];

    if ($oldData === $formData) {
      Notification::make()
        ->info()
        ->title('Tidak ada perubahan')
        ->body('Data yang diisi sama dengan data saat ini.')
        ->send();
      return;
    }

    // Buat pengajuan baru
    ProfileChangeRequest::create([
      'user_id' => $user->id,
      'old_data' => $oldData,
      'new_data' => $formData,
      'status' => ProfileChangeRequest::STATUS_PENDING,
      'catatan' => null,
    ]);

    // Reset form ke data lama (belum diterapkan)
    $this->form->fill($oldData);

    Notification::make()
      ->success()
      ->title('Pengajuan perubahan berhasil dikirim!')
      ->body('Perubahan data Anda akan diterapkan setelah disetujui oleh Super Admin.')
      ->icon('heroicon-o-paper-airplane')
      ->send();
  }

  /**
   * Dapatkan pending request untuk user ini (jika ada)
   */
  public function getPendingRequest(): ?ProfileChangeRequest
  {
    /** @var User $user */
    $user = Auth::user();
    if (!$user || $user->hasRole('Super Admin')) return null;

    return ProfileChangeRequest::where('user_id', $user->id)
      ->where('status', ProfileChangeRequest::STATUS_PENDING)
      ->latest()
      ->first();
  }

  /**
   * Riwayat pengajuan terakhir
   */
  public function getLatestRequests()
  {
    /** @var User $user */
    $user = Auth::user();
    if (!$user) return collect();

    return ProfileChangeRequest::where('user_id', $user->id)
      ->orderBy('created_at', 'desc')
      ->paginate(5);
  }

  /**
   * Cek apakah user saat ini adalah Super Admin
   */
  public function getIsSuperAdminProperty(): bool
  {
    /** @var User|null $user */
    $user = Auth::user();
    return $user?->hasRole('Super Admin') ?? false;
  }
}
