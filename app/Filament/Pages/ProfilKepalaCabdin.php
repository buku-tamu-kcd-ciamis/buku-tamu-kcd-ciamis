<?php

namespace App\Filament\Pages;

use App\Models\PengaturanKcd;
use App\Models\ProfileChangeRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use Livewire\WithPagination;

class ProfilKepalaCabdin extends Page implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Profil Kepala Cabang Dinas';
    protected static string|\UnitEnum|null $navigationGroup = 'Akun';
    protected static ?string $title = 'Profil Data Diri Kepala Cabang Dinas';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.profil-kepala-cabdin';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        /** @var User $user */
        $user = Filament::auth()->user();
        if (!$user) abort(403);

        $isSuperAdmin = $user->hasRole('Super Admin');
        $isKepalaCabdin = $user->hasRole('Kepala Cabang Dinas');

        if (!$isSuperAdmin && !$isKepalaCabdin) abort(403);
        if ($isKepalaCabdin && !$user->role_user?->hasPermission('profil_kepala_cabdin')) abort(403);

        $settings = PengaturanKcd::getSettings();

        $this->form->fill([
            'nama_kepala' => $settings->nama_kepala,
            'nip_kepala' => $settings->nip_kepala,
            'jabatan' => $settings->jabatan,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Data Diri Kepala Cabang Dinas')
                    ->description('Data ini digunakan pada tanda tangan di halaman cetak/print.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\TextInput::make('nama_kepala')
                            ->placeholder('Contoh: Drs. H. Ahmad Suryadi, M.Pd.')
                            ->maxLength(255)
                            ->helperText('Nama lengkap beserta gelar depan dan belakang.')
                            ->required(),
                        Forms\Components\TextInput::make('nip_kepala')
                            ->placeholder('Contoh: 196712051992031005')
                            ->mask('999999999999999999')
                            ->length(18)
                            ->helperText('NIP harus terdiri dari 18 digit.')
                            ->required(),
                        Forms\Components\TextInput::make('jabatan')
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
        $user = Filament::auth()->user();
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
                ->log('Data Kepala Cabang Dinas diperbarui langsung oleh Super Admin');

            Notification::make()
                ->success()
                ->title('Data berhasil disimpan!')
                ->body('Perubahan langsung diterapkan.')
                ->send();

            return;
        }

        // Kepala Cabang Dinas → cek apakah masih ada pengajuan pending
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
            'nama_kepala' => $settings->nama_kepala,
            'nip_kepala' => $settings->nip_kepala,
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
        $user = Filament::auth()->user();
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
        $user = Filament::auth()->user();
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
        $user = Filament::auth()->user();
        return $user?->hasRole('Super Admin') ?? false;
    }
}
