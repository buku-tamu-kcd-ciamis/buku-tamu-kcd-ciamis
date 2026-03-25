<?php

namespace App\Filament\Piket\Resources\PegawaiIzinResource\Pages;

use App\Filament\Piket\Resources\PegawaiIzinResource;
use App\Models\PegawaiIzin;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ViewPegawaiIzin extends ViewRecord
{
    protected static string $resource = PegawaiIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            // === Informasi Pegawai ===
            Section::make('Informasi Pegawai')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->components([
                    Infolists\Components\TextEntry::make('nama_pegawai')
                        ->size('lg')
                        ->icon('heroicon-o-user'),
                    Infolists\Components\TextEntry::make('nip')
                        ->icon('heroicon-o-identification')
                        ->copyable(),
                    Infolists\Components\TextEntry::make('nomor_hp')
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
                        ->icon('heroicon-o-building-office')
                        ->placeholder('-'),
                ]),

            // === Informasi Izin ===
            Section::make('Informasi Izin')
                ->icon('heroicon-o-calendar-days')
                ->columns(2)
                ->components([
                    Infolists\Components\TextEntry::make('jenis_izin')
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
                        ->date('d F Y')
                        ->icon('heroicon-o-calendar'),
                    Infolists\Components\TextEntry::make('tanggal_selesai')
                        ->date('d F Y')
                        ->icon('heroicon-o-calendar'),
                    Infolists\Components\TextEntry::make('nama_piket')
                        ->icon('heroicon-o-user-circle')
                        ->placeholder('-'),
                    Infolists\Components\TextEntry::make('keterangan')
                        ->icon('heroicon-o-document-text')
                        ->placeholder('-'),
                ]),

            // === Tanda Tangan Piket ===
            Section::make('Tanda Tangan Piket')
                ->icon('heroicon-o-pencil-square')
                ->components([
                    Infolists\Components\ImageEntry::make('tanda_tangan_piket'),
                ]),

            // === Informasi Sistem ===
            Section::make('Informasi Sistem')
                ->icon('heroicon-o-information-circle')
                ->columns(2)
                ->collapsed()
                ->components([
                    Infolists\Components\TextEntry::make('created_at')
                        ->dateTime('d F Y, H:i:s')
                        ->icon('heroicon-o-clock'),
                    Infolists\Components\TextEntry::make('updated_at')
                        ->dateTime('d F Y, H:i:s')
                        ->icon('heroicon-o-clock'),
                ]),
        ]);
    }
}
