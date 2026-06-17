<?php

namespace App\Filament\Staff\Pages;

use App\Filament\Staff\Concerns\ChecksStaffPermission;
use App\Models\BukuTamu;
use App\Models\User;
use App\Services\BookingChatManager;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Page;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Facades\Filament;

class PengantarBerkas extends Page implements HasTable
{
    use InteractsWithTable;
    use ChecksStaffPermission;

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Pengantar Berkas';
    protected static string|\UnitEnum|null $navigationGroup = 'Layanan Tamu';
    protected static ?string $title = 'Daftar Pengantar Berkas';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.staff.pages.pengantar-berkas';

    public static function shouldRegisterNavigation(): bool
    {
        return static::hasStaffPermission('pengantar_berkas');
    }

    public static function canAccess(): bool
    {
        return static::hasStaffPermission('pengantar_berkas');
    }

    public ?string $activeTab = 'hari_ini';

    protected function hasCustomDateFilters(): bool
    {
        return !empty($this->tableFilters['tanggal']['dari'] ?? null) || !empty($this->tableFilters['tanggal']['sampai'] ?? null);
    }

    public function getTabBadge(string $tab): int
    {
        $query = BukuTamu::query()
            ->where('staff_dituju', $this->getStaffNama())
            ->whereNotNull('foto_penerimaan')
            ->where('foto_penerimaan', '!=', '');

        return match ($tab) {
            'hari_ini' => $query->whereDate('created_at', now()->toDateString())->count(),
            'kemarin' => $query->whereDate('created_at', now()->subDay()->toDateString())->count(),
            'minggu_ini' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'bulan_ini' => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'semua' => $query->count(),
            default => 0,
        };
    }

    public function table(Table $table): Table
    {
        $staffNama = $this->getStaffNama();

        return $table
            ->query(
                BukuTamu::query()
                    ->where('staff_dituju', $staffNama)
                    ->whereNotNull('foto_penerimaan')
                    ->where('foto_penerimaan', '!=', '')
                    ->when($this->activeTab === 'hari_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereDate('created_at', now()->toDateString()))
                    ->when($this->activeTab === 'kemarin' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereDate('created_at', now()->subDay()->toDateString()))
                    ->when($this->activeTab === 'minggu_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->when($this->activeTab === 'bulan_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]))
            )
            ->columns([
                Tables\Columns\ViewColumn::make('foto_selfie')
                    ->label('Foto')
                    ->view('filament.tables.columns.avatar-column'),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('instansi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('keperluan'),
                Tables\Columns\TextColumn::make('staff_dituju')
                    ->label('Staff Yang Dituju'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'menunggu' => 'warning',
                        'diproses' => 'info',
                        'selesai' => 'success',
                        'ditolak' => 'danger',
                        'dibatalkan' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => BukuTamu::STATUS_LABELS[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since()
                    ->color('gray')
                    ->tooltip(fn(BukuTamu $record) => $record->created_at->format('d/m/Y H:i'))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10])
            ->recordActionsColumnLabel('')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(BukuTamu::STATUS_LABELS),
                Tables\Filters\SelectFilter::make('keperluan')
                    ->label('Keperluan')
                    ->options(fn(): array => BukuTamu::query()
                        ->where('staff_dituju', $staffNama)
                        ->whereNotNull('foto_penerimaan')
                        ->where('foto_penerimaan', '!=', '')
                        ->select('keperluan')
                        ->whereNotNull('keperluan')
                        ->where('keperluan', '!=', '')
                        ->orderBy('keperluan')
                        ->distinct()
                        ->pluck('keperluan', 'keperluan')
                        ->all())
                    ->searchable(),
                Tables\Filters\Filter::make('tanggal')
                    ->schema([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'], fn($query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['sampai'], fn($query, $date) => $query->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['dari'])->translatedFormat('d M Y');
                        }
                        if ($data['sampai'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai'])->translatedFormat('d M Y');
                        }
                        return $indicators;
                    })
                    ->columns(2)
                    ->columnSpan(2),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->recordActions([
                ActionGroup::make([
                    Action::make('chat')
                        ->label('Chat Piket')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('primary')
                        ->url(function (BukuTamu $record): string {
                            $chat = $record->bookingChats()->first();

                            if (! $chat) {
                                $chat = app(BookingChatManager::class)->bootstrapForBooking($record, Filament::auth()->user())->first();
                            }

                            if (! $chat) {
                                return ChatBooking::getUrl() . '?booking=' . $record->id;
                            }

                            return ChatBooking::getUrl() . '?chat=' . $chat->id;
                        })
                        ->openUrlInNewTab(false),
                    Action::make('detail')
                        ->label('Lihat Detail')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->modalContent(fn(BukuTamu $record) => view('filament.piket.pages.detail-pengantar-berkas', ['record' => $record]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup'),
                ])
                    ->label(false)
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->headerActions([])
            ->toolbarActions([]);
    }

    private function getStaffNama(): string
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        $nama = trim((string) ($user?->pegawai?->nama ?? ''));

        return $nama;
    }
}
