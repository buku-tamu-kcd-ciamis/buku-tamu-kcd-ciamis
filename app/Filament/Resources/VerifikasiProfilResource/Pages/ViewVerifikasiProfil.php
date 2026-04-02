<?php

namespace App\Filament\Resources\VerifikasiProfilResource\Pages;

use App\Filament\Resources\VerifikasiProfilResource;
use App\Models\ProfileChangeRequest;
use Filament\Actions;
use Filament\Infolists;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ViewVerifikasiProfil extends ViewRecord
{
    protected static string $resource = VerifikasiProfilResource::class;

    protected string $view = 'filament.resources.verifikasi-profil-resource.pages.view-verifikasi-profil';

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Pengajuan')
                ->icon('heroicon-o-information-circle')
                ->columns(3)
                ->components([
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('Pengaju')
                        ->icon('heroicon-o-user')
                        ->placeholder('-'),
                    Infolists\Components\TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn(?string $state): string => ProfileChangeRequest::STATUS_LABELS[$state] ?? ucfirst((string) $state))
                        ->color(fn(?string $state): string => ProfileChangeRequest::STATUS_COLORS[$state] ?? 'gray')
                        ->icon(fn(?string $state): ?string => ProfileChangeRequest::STATUS_ICONS[$state] ?? null),
                    Infolists\Components\TextEntry::make('reviewer.name')
                        ->label('Diproses Oleh')
                        ->icon('heroicon-o-shield-check')
                        ->placeholder('-'),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Tanggal Pengajuan')
                        ->dateTime('d/m/Y H:i')
                        ->icon('heroicon-o-calendar-days'),
                    Infolists\Components\TextEntry::make('reviewed_at')
                        ->label('Tanggal Proses')
                        ->dateTime('d/m/Y H:i')
                        ->icon('heroicon-o-clock')
                        ->placeholder('-'),
                    Infolists\Components\TextEntry::make('changed_fields_summary')
                        ->label('Field yang Berubah')
                        ->state(function (ProfileChangeRequest $record): string {
                            $changed = $record->getChangedFields();
                            $labels = ProfileChangeRequest::getFieldLabels();

                            if (empty($changed)) {
                                return '-';
                            }

                            return collect($changed)
                                ->keys()
                                ->map(fn(string $field): string => $labels[$field] ?? $field)
                                ->implode(', ');
                        })
                        ->badge()
                        ->color('gray')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('catatan')
                        ->label('Catatan Pengaju')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('alasan_reject')
                        ->label('Alasan Penolakan')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ]),

            Section::make('Detail Perubahan Data')
                ->icon('heroicon-o-arrows-right-left')
                ->columns(2)
                ->components([
                    Infolists\Components\TextEntry::make('old_data.nama_kepala')
                        ->label('Nama Sebelumnya')
                        ->placeholder('-'),
                    Infolists\Components\TextEntry::make('new_data.nama_kepala')
                        ->label('Nama Baru')
                        ->placeholder('-'),
                    Infolists\Components\TextEntry::make('old_data.nip_kepala')
                        ->label('NIP Sebelumnya')
                        ->placeholder('-'),
                    Infolists\Components\TextEntry::make('new_data.nip_kepala')
                        ->label('NIP Baru')
                        ->placeholder('-'),
                    Infolists\Components\TextEntry::make('old_data.jabatan')
                        ->label('Jabatan Sebelumnya')
                        ->placeholder('-'),
                    Infolists\Components\TextEntry::make('new_data.jabatan')
                        ->label('Jabatan Baru')
                        ->placeholder('-'),
                ]),
        ]);
    }

    public function getTitle(): string
    {
        return 'Detail Verifikasi Profil';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('kembali')
                ->label('Kembali')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(VerifikasiProfilResource::getUrl('index')),
        ];
    }
}
