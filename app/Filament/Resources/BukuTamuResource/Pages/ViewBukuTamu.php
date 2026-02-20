<?php

namespace App\Filament\Resources\BukuTamuResource\Pages;

use App\Filament\Resources\BukuTamuResource;
use App\Models\BukuTamu;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewBukuTamu extends ViewRecord
{
    protected static string $resource = BukuTamuResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // === Header: Foto Selfie + Info Utama ===
            Infolists\Components\Section::make()
                ->schema([
                    Infolists\Components\Grid::make(3)
                        ->schema([
                            Infolists\Components\Group::make([
                                Infolists\Components\ViewEntry::make('foto_selfie')
                                    ->label('Foto Selfie')
                                    ->view('filament.infolists.components.image-base64-entry'),
                            ])->columnSpan(1),

                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('nama_lengkap')
                                    ->label('Nama Lengkap')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('jenis_id')
                                            ->label('Jenis ID')
                                            ->icon('heroicon-o-identification'),
                                        Infolists\Components\TextEntry::make('nik')
                                            ->label('Nomor ID')
                                            ->icon('heroicon-o-finger-print')
                                            ->copyable(),
                                        Infolists\Components\TextEntry::make('instansi')
                                            ->icon('heroicon-o-building-office-2')
                                            ->placeholder('-'),
                                        Infolists\Components\TextEntry::make('jabatan')
                                            ->icon('heroicon-o-briefcase')
                                            ->placeholder('-'),
                                        Infolists\Components\TextEntry::make('nomor_hp')
                                            ->label('No. HP')
                                            ->icon('heroicon-o-phone')
                                            ->formatStateUsing(function ($state) {
                                                if (!$state)
                                                    return '-';
                                                $cleaned = preg_replace('/[^0-9]/', '', $state);
                                                if (str_starts_with($cleaned, '0')) {
                                                    $cleaned = substr($cleaned, 1);
                                                }
                                                return '+62' . $cleaned;
                                            })
                                            ->copyable(),
                                        Infolists\Components\TextEntry::make('email')
                                            ->icon('heroicon-o-envelope')
                                            ->copyable()
                                            ->placeholder('-'),
                                    ]),
                            ])->columnSpan(2),
                        ]),
                ]),

            // === Informasi Kunjungan ===
            Infolists\Components\Section::make('Informasi Kunjungan')
                ->icon('heroicon-o-clipboard-document-list')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('kabupaten_kota')
                        ->label('Kabupaten / Kota')
                        ->icon('heroicon-o-map-pin'),
                    Infolists\Components\TextEntry::make('bagian_dituju')
                        ->label('Bagian Yang Dituju')
                        ->icon('heroicon-o-building-office'),
                    Infolists\Components\TextEntry::make('keperluan')
                        ->label('Keperluan')
                        ->icon('heroicon-o-document-text')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Waktu Kunjungan')
                        ->icon('heroicon-o-clock')
                        ->dateTime('d F Y, H:i:s'),
                ]),

            // === Status ===
            Infolists\Components\Section::make('Status Kunjungan')
                ->icon('heroicon-o-signal')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn(string $state) => BukuTamu::STATUS_LABELS[$state] ?? ucfirst($state))
                        ->color(fn(string $state) => match ($state) {
                            'menunggu' => 'warning',
                            'diproses' => 'info',
                            'selesai' => 'success',
                            'ditolak' => 'danger',
                            'dibatalkan' => 'gray',
                            default => 'secondary',
                        }),
                    Infolists\Components\TextEntry::make('nama_penerima')
                        ->label('Nama Penerima')
                        ->icon('heroicon-o-user')
                        ->placeholder('Belum ada penerima'),
                    Infolists\Components\TextEntry::make('catatan')
                        ->placeholder('Tidak ada catatan')
                        ->columnSpanFull(),
                ]),

            // === Dokumen ===
            Infolists\Components\Section::make('Dokumen')
                ->icon('heroicon-o-camera')
                ->columns(2)
                ->schema([
                    Infolists\Components\ViewEntry::make('foto_penerimaan')
                        ->label('Foto Penerimaan Berkas')
                        ->view('filament.infolists.components.image-base64-entry'),
                    Infolists\Components\ViewEntry::make('tanda_tangan')
                        ->label('Tanda Tangan')
                        ->view('filament.infolists.components.signature-base64-entry'),
                ]),
        ]);
    }
}
