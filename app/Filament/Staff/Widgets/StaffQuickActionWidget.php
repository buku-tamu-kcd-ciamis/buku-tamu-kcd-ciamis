<?php

namespace App\Filament\Staff\Widgets;

use App\Filament\Staff\Pages\ChatBooking;
use App\Filament\Staff\Pages\RiwayatKunjungan;
use App\Filament\Staff\Resources\PegawaiIzinResource;
use App\Models\BookingChatMessage;
use App\Models\BukuTamu;
use App\Models\PegawaiIzin;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

class StaffQuickActionWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        /** @var \App\Models\User $user */
        $user = Filament::auth()->user();
        $pegawaiNama = $user?->pegawai?->nama ?? $user?->name;

        $chatBelumDibacaQuery = BookingChatMessage::query()
            ->whereNull('read_at')
            ->where('is_system', false)
            ->where(function (Builder $query) use ($user) {
                $query->whereNull('sender_user_id')->orWhere('sender_user_id', '!=', $user?->id);
            })
            ->whereHas('chat', fn(Builder $query) => $query->where('staff_user_id', $user?->id));

        $notifikasiBelumDibaca = (clone $chatBelumDibacaQuery)->count();

        $chatTerbaru = (clone $chatBelumDibacaQuery)
            ->with(['sender:id,name'])
            ->latest('created_at')
            ->first();

        $deskripsiChat = $chatTerbaru
            ? sprintf(
                '%s • %s',
                $chatTerbaru->created_at?->format('H:i') ?? '--:--',
                Str::limit($chatTerbaru->message ?? 'Pesan baru masuk', 38)
            )
            : 'belum ada chat baru';

        $izinMenunggu = PegawaiIzin::where('nama_pegawai', $pegawaiNama)
            ->where('status', PegawaiIzin::STATUS_MENUNGGU)
            ->count();

        $riwayatKunjungan = BukuTamu::where('staff_dituju', $pegawaiNama)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('Aksi Cepat: Chat Booking', $notifikasiBelumDibaca)
                ->description($deskripsiChat)
                ->url(ChatBooking::getUrl())
                ->openUrlInNewTab(false)
                ->extraAttributes(['class' => 'staff-quick-action'])
                ->color($notifikasiBelumDibaca > 0 ? 'danger' : 'primary'),

            Stat::make('Aksi Cepat: Izin Saya', $izinMenunggu)
                ->description('pengajuan menunggu persetujuan')
                ->url(PegawaiIzinResource::getUrl('index'))
                ->openUrlInNewTab(false)
                ->extraAttributes(['class' => 'staff-quick-action'])
                ->color($izinMenunggu > 0 ? 'warning' : 'primary'),

            Stat::make('Aksi Cepat: Riwayat', $riwayatKunjungan)
                ->description('kunjungan bulan ini')
                ->url(RiwayatKunjungan::getUrl())
                ->openUrlInNewTab(false)
                ->extraAttributes(['class' => 'staff-quick-action'])
                ->color('success'),
        ];
    }
}
