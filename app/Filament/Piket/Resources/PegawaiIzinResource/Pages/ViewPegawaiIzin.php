<?php

namespace App\Filament\Piket\Resources\PegawaiIzinResource\Pages;

use App\Filament\Piket\Resources\PegawaiIzinResource;
use App\Models\PegawaiIzin;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Actions;

class ViewPegawaiIzin extends ViewRecord
{
    protected static string $resource = PegawaiIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Cetak Surat Izin')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn() => route('piket.pegawai-izin.print', ['id' => $this->record->id]))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // === Informasi Pegawai ===
            Infolists\Components\Section::make('Informasi Pegawai')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('nama_pegawai')
                        ->label('Nama Pegawai')
                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                        ->weight('bold')
                        ->icon('heroicon-o-user')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('nip')
                        ->label('NIP')
                        ->icon('heroicon-o-identification')
                        ->copyable(),
                    Infolists\Components\TextEntry::make('nomor_hp')
                        ->label('No. HP')
                        ->icon('heroicon-o-phone')
                        ->formatStateUsing(function ($state) {
                            if (!$state) return '-';
                            $cleaned = preg_replace('/[^0-9]/', '', $state);
                            if (str_starts_with($cleaned, '0')) {
                                $cleaned = substr($cleaned, 1);
                            }
                            return '+62' . $cleaned;
                        })
                        ->copyable()
                        ->placeholder('-'),
                    Infolists\Components\TextEntry::make('jabatan')
                        ->icon('heroicon-o-briefcase')
                        ->placeholder('-'),
                    Infolists\Components\TextEntry::make('unit_kerja')
                        ->label('Unit Kerja')
                        ->icon('heroicon-o-building-office')
                        ->placeholder('-'),
                ]),

            // === Informasi Izin ===
            Infolists\Components\Section::make('Informasi Izin')
                ->icon('heroicon-o-calendar-days')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('jenis_izin')
                        ->label('Jenis Izin')
                        ->badge()
                        ->color('info')
                        ->formatStateUsing(fn($state) => PegawaiIzin::JENIS_IZIN_LABELS[$state] ?? $state)
                        ->icon('heroicon-o-clipboard-document-list'),
                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->color(fn($state) => $state === 'aktif' ? 'success' : 'gray')
                        ->formatStateUsing(fn($state) => ucfirst($state))
                        ->icon('heroicon-o-signal'),
                    Infolists\Components\TextEntry::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->date('d F Y')
                        ->icon('heroicon-o-calendar'),
                    Infolists\Components\TextEntry::make('tanggal_selesai')
                        ->label('Tanggal Selesai')
                        ->date('d F Y')
                        ->icon('heroicon-o-calendar'),
                    Infolists\Components\TextEntry::make('keterangan')
                        ->label('Keterangan')
                        ->icon('heroicon-o-document-text')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('nama_piket')
                        ->label('Nama Piket')
                        ->icon('heroicon-o-user-circle')
                        ->placeholder('-'),
                    Infolists\Components\ViewEntry::make('tanda_tangan_piket')
                        ->label('Tanda Tangan Piket')
                        ->view('filament.infolists.components.signature-base64-entry'),
                ]),

            // === Informasi Sistem ===
            Infolists\Components\Section::make('Informasi Sistem')
                ->icon('heroicon-o-information-circle')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Dibuat Pada')
                        ->dateTime('d F Y, H:i:s')
                        ->icon('heroicon-o-clock'),
                    Infolists\Components\TextEntry::make('updated_at')
                        ->label('Diperbarui Pada')
                        ->dateTime('d F Y, H:i:s')
                        ->icon('heroicon-o-clock'),
                ]),
        ]);
    }
}
