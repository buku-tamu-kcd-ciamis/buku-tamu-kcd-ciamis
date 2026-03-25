<?php

namespace App\Filament\Staff\Pages;

use App\Models\Pegawai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class KetersediaanStatus extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-signal';
    protected static ?string $navigationLabel = 'Status Ketersediaan';
    protected static string|\UnitEnum|null $navigationGroup = 'Kepegawaian';
    protected static ?string $title = 'Status Ketersediaan';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.staff.pages.ketersediaan-status';

    public ?string $availability_status = 'available';

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if ($pegawai) {
            $this->availability_status = $pegawai->availability_status ?? 'available';
        }
    }

    public function updateStatus(string $status): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $pegawai = $user->pegawai;

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
