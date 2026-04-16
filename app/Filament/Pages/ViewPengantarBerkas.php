<?php

namespace App\Filament\Pages;

use App\Models\BukuTamu;
use App\Models\User;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Panel;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Facades\Filament;

class ViewPengantarBerkas extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected string $view = 'filament.pages.view-pengantar-berkas';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Detail Pengantar Berkas';

    public ?string $record = null;

    public function getMaxContentWidth(): string | Width | null
    {
        return Width::Full;
    }

    public static function getRoutePath(Panel $panel): string
    {
        return 'view-pengantar-berkas/{record}';
    }

    public static function getUrl(
        array $parameters = [],
        bool $isAbsolute = true,
        ?string $panel = null,
        ?\Illuminate\Database\Eloquent\Model $tenant = null,
        bool $shouldGuessMissingParameters = false,
        ?string $configuration = null,
    ): string {
        return url('/admin/view-pengantar-berkas/' . ($parameters['record'] ?? ''));
    }

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        if (! $user || ! $user->hasRole('Super Admin')) {
            abort(403);
        }

        $exists = BukuTamu::query()
            ->whereKey($this->record)
            ->whereNotNull('foto_penerimaan')
            ->where('foto_penerimaan', '!=', '')
            ->exists();

        if (! $exists) {
            abort(404);
        }
    }

    public function getTamu(): BukuTamu
    {
        return BukuTamu::query()
            ->whereKey($this->record)
            ->firstOrFail();
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->getTamu())
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->components([
                        Grid::make(3)
                            ->components([
                                Group::make([
                                    Infolists\Components\ImageEntry::make('foto_selfie')
                                        ->label('Foto selfie')
                                        ->imageWidth('100%')
                                        ->imageHeight(260)
                                        ->disk('public')
                                        ->extraImgAttributes([
                                            'class' => 'object-cover w-full rounded-2xl shadow-lg border-2 border-white hover:scale-[1.02] transition-all duration-300 cursor-pointer',
                                        ])
                                        ->action(
                                            Actions\Action::make('preview_foto_selfie')
                                                ->label('Lihat Foto')
                                                ->modalHeading('Preview Foto Selfie')
                                                ->modalWidth('4xl')
                                                ->modalSubmitAction(false)
                                                ->modalCancelAction(false)
                                                ->modalContent(fn($record) => view('filament.components.image-preview', [
                                                    'url' => $record->foto_selfie_url,
                                                    'name' => $record->nama_lengkap,
                                                ]))
                                        ),
                                ])->columnSpan(1),
                                Group::make([
                                    Infolists\Components\TextEntry::make('nama_lengkap')
                                        ->size('lg'),
                                    Grid::make(2)
                                        ->components([
                                            Infolists\Components\TextEntry::make('jenis_id')
                                                ->label('Jenis ID')
                                                ->icon('heroicon-o-identification')
                                                ->formatStateUsing(fn($state): string => filled($state) ? strtoupper((string) $state) : '-')
                                                ->placeholder('-'),
                                            Infolists\Components\TextEntry::make('nik')
                                                ->label(fn($record): string => filled($record?->jenis_id) ? strtoupper((string) $record->jenis_id) : 'Nomor ID')
                                                ->icon('heroicon-o-finger-print')
                                                ->copyable(),
                                            Infolists\Components\TextEntry::make('instansi')
                                                ->icon('heroicon-o-building-office-2')
                                                ->placeholder('-'),
                                            Infolists\Components\TextEntry::make('jabatan')
                                                ->icon('heroicon-o-briefcase')
                                                ->placeholder('-'),
                                            Infolists\Components\TextEntry::make('nomor_hp')
                                                ->icon('heroicon-o-phone')
                                                ->formatStateUsing(function ($state) {
                                                    if (! $state) {
                                                        return '-';
                                                    }

                                                    $cleaned = preg_replace('/[^0-9]/', '', (string) $state);

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

                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                    ->columnSpanFull()
                    ->components([
                        Section::make('Status Kunjungan')
                            ->icon('heroicon-o-signal')
                            ->columns(2)
                            ->components([
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
                                    ->icon('heroicon-o-user')
                                    ->placeholder('Belum ada penerima'),
                                Infolists\Components\TextEntry::make('catatan')
                                    ->placeholder('Tidak ada catatan'),
                            ]),
                        Section::make('Informasi Kunjungan')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->columns(2)
                            ->components([
                                Infolists\Components\TextEntry::make('kabupaten_kota')
                                    ->icon('heroicon-o-map-pin'),
                                Infolists\Components\TextEntry::make('staff_dituju')
                                    ->icon('heroicon-o-building-office'),
                                Infolists\Components\TextEntry::make('keperluan')
                                    ->icon('heroicon-o-document-text'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Waktu kunjungan')
                                    ->icon('heroicon-o-clock')
                                    ->dateTime('d F Y, H:i:s'),
                            ]),
                    ]),

                Section::make('Dokumen')
                    ->icon('heroicon-o-camera')
                    ->columnSpanFull()
                    ->columns(2)
                    ->components([
                        Infolists\Components\ImageEntry::make('foto_penerimaan')
                            ->label('Foto penerimaan')
                            ->imageWidth('100%')
                            ->imageHeight(260)
                            ->disk('public')
                            ->extraImgAttributes([
                                'class' => 'object-cover w-full rounded-2xl shadow-lg border-2 border-white hover:scale-[1.02] transition-all duration-300 cursor-pointer',
                            ])
                            ->visible(fn(?BukuTamu $record): bool => filled($record?->foto_penerimaan))
                            ->action(
                                Actions\Action::make('preview_foto_penerimaan')
                                    ->label('Lihat Foto')
                                    ->modalHeading('Preview Foto Penerimaan')
                                    ->modalWidth('4xl')
                                    ->modalSubmitAction(false)
                                    ->modalCancelAction(false)
                                    ->modalContent(fn($record) => view('filament.components.image-preview', [
                                        'url' => $record->foto_penerimaan_url,
                                        'name' => "Penerimaan - {$record->nama_lengkap}",
                                    ]))
                            ),
                        Infolists\Components\ImageEntry::make('tanda_tangan')
                            ->label('Tanda tangan')
                            ->disk('public')
                            ->extraAttributes([
                                'class' => 'bt-signature-entry',
                            ]),
                    ]),
            ]);
    }
}
