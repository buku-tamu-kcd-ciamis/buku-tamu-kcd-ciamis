<?php

namespace App\Filament\Staff\Pages;

use App\Filament\Staff\Concerns\ChecksStaffPermission;
use App\Models\Pegawai;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class KetersediaanStatus extends Page
{
    use ChecksStaffPermission;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-signal';
    protected static ?string $navigationLabel = 'Status Ketersediaan';
    protected static string|\UnitEnum|null $navigationGroup = 'Kepegawaian';
    protected static ?string $title = 'Status Ketersediaan';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.staff.pages.ketersediaan-status';

    public static function shouldRegisterNavigation(): bool
    {
        return static::hasStaffPermission('data_pegawai');
    }

    public static function canAccess(): bool
    {
        return static::hasStaffPermission('data_pegawai');
    }

    public ?string $availability_status = 'available';

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $allowedStatuses = array_keys(Pegawai::AVAILABILITY_LABELS);

        if ($pegawai) {
            $currentStatus = $pegawai->availability_status ?? Pegawai::AVAILABILITY_AVAILABLE;

            if (!in_array($currentStatus, $allowedStatuses, true)) {
                $currentStatus = Pegawai::AVAILABILITY_AVAILABLE;
            }

            $this->availability_status = $currentStatus;
        }
    }

    public function updateStatus(string $status): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $allowedStatuses = array_keys(Pegawai::AVAILABILITY_LABELS);

        if (!in_array($status, $allowedStatuses, true)) {
            Notification::make()
                ->title('Status tidak valid')
                ->body('Pilihan status ketersediaan tidak diizinkan.')
                ->danger()
                ->send();

            return;
        }

        if (!$pegawai) {
            Notification::make()
                ->title('Error')
                ->body('Akun Anda belum terhubung dengan data pegawai. Hubungi admin.')
                ->danger()
                ->send();
            return;
        }

        $pegawai->update(['availability_status' => $status]);
        $this->availability_status = $status;

        $label = Pegawai::AVAILABILITY_LABELS[$status] ?? $status;

        Notification::make()
            ->title('Status Diperbarui')
            ->body("Status ketersediaan Anda telah diubah menjadi: {$label}")
            ->success()
            ->send();
    }
}
