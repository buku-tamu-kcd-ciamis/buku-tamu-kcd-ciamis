<?php

namespace App\Filament\Staff\Pages;

use App\Filament\Staff\Concerns\ChecksStaffPermission;
use App\Models\BukuTamu;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Facades\Filament;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;

class RiwayatKunjungan extends Page implements HasTable
{
    use InteractsWithTable;
    use ChecksStaffPermission;

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Riwayat Kunjungan';
    protected static string|\UnitEnum|null $navigationGroup = 'Informasi';
    protected static ?string $title = 'Riwayat Kunjungan Saya';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.staff.pages.riwayat-kunjungan';

    public static function shouldRegisterNavigation(): bool
    {
        return static::hasStaffPermission('riwayat_tamu');
    }

    public static function canAccess(): bool
    {
        return static::hasStaffPermission('riwayat_tamu');
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->id;
    }

    public ?string $activeTab = 'hari_ini';

    protected function hasCustomDateFilters(): bool
    {
        return !empty($this->tableFilters['tanggal']['dari'] ?? null) || !empty($this->tableFilters['tanggal']['sampai'] ?? null);
    }

    public function getTabBadge(string $tab): int
    {
        $query = BukuTamu::query()
            ->where('staff_dituju', $this->getStaffNama());

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
        $dateRangeDefaults = $this->getExportDateRangeDefaults();

        return $table
            ->query(
                BukuTamu::query()
                    ->where('staff_dituju', $staffNama)
                    ->when($this->activeTab === 'hari_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereDate('created_at', now()->toDateString()))
                    ->when($this->activeTab === 'kemarin' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereDate('created_at', now()->subDay()->toDateString()))
                    ->when($this->activeTab === 'minggu_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->when($this->activeTab === 'bulan_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]))
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Tamu')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('instansi')
                    ->label('Instansi')
                    ->searchable()
                    ->toggleable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->instansi),
                Tables\Columns\TextColumn::make('keperluan')
                    ->label('Keperluan')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn($record) => $record->keperluan),
                Tables\Columns\TextColumn::make('nomor_hp')
                    ->label('No. HP')
                    ->formatStateUsing(function ($state) {
                        if (!$state)
                            return '-';
                        $cleaned = preg_replace('/[^0-9]/', '', $state);
                        if (str_starts_with($cleaned, '0')) {
                            $cleaned = substr($cleaned, 1);
                        }
                        return '+62' . $cleaned;
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => BukuTamu::STATUS_LABELS[$state] ?? $state)
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        BukuTamu::STATUS_MENUNGGU => 'warning',
                        BukuTamu::STATUS_DIPROSES => 'primary',
                        BukuTamu::STATUS_SELESAI => 'success',
                        BukuTamu::STATUS_DITOLAK => 'danger',
                        BukuTamu::STATUS_DIBATALKAN => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Kunjungan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->tooltip(fn($record) => $record->created_at?->diffForHumans()),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(BukuTamu::STATUS_LABELS),
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
                            ->when($data['dari'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['sampai'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
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
            ->filtersFormColumns(3)
            ->headerActions([
                Action::make('export_csv')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('Export Riwayat Kunjungan')
                    ->modalDescription('Filter data yang ingin diunduh.')
                    ->schema([
                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->default($dateRangeDefaults['mulai'])
                            ->native(false),
                        DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->default($dateRangeDefaults['selesai'])
                            ->native(false),
                        Select::make('nama_tamu')
                            ->label('Nama Tamu')
                            ->options($this->getNamaTamuOptions())
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Semua tamu'),
                        Select::make('instansi')
                            ->label('Instansi')
                            ->options($this->getInstansiOptions())
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Semua instansi'),
                    ])
                    ->action(fn(array $data) => $this->exportCsv($data)),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('status_selesai')
                        ->label('Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(BukuTamu $record) => $record->status !== BukuTamu::STATUS_SELESAI)
                        ->action(fn(BukuTamu $record) => $this->updateStatus($record, BukuTamu::STATUS_SELESAI)),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Aksi status'),
            ]);
    }

    public function exportCsv(array $filters = []): StreamedResponse
    {
        $staffNama = $this->getStaffNama();
        $tanggalMulai = !empty($filters['tanggal_mulai']) ? Carbon::parse($filters['tanggal_mulai'])->startOfDay() : null;
        $tanggalSelesai = !empty($filters['tanggal_selesai']) ? Carbon::parse($filters['tanggal_selesai'])->endOfDay() : null;
        $namaTamu = trim((string) ($filters['nama_tamu'] ?? ''));
        $instansi = trim((string) ($filters['instansi'] ?? ''));

        if ($tanggalMulai && $tanggalSelesai && $tanggalMulai->gt($tanggalSelesai)) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai->copy()->startOfDay(), $tanggalMulai->copy()->endOfDay()];
        }

        $query = BukuTamu::query()
            ->where('staff_dituju', $staffNama)
            ->latest('created_at');

        if ($tanggalMulai) {
            $query->where('created_at', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->where('created_at', '<=', $tanggalSelesai);
        }

        if ($namaTamu !== '') {
            $query->where('nama_lengkap', 'like', '%' . $namaTamu . '%');
        }

        if ($instansi !== '') {
            $query->where('instansi', $instansi);
        }

        $rows = $query->get([
                'nama_lengkap',
                'instansi',
                'keperluan',
                'nomor_hp',
                'status',
                'created_at',
            ]);

        $dateToken = 'semua';

        if ($tanggalMulai && $tanggalSelesai) {
            $dateToken = $tanggalMulai->format('Ymd') . '-sampai-' . $tanggalSelesai->format('Ymd');
        } elseif ($tanggalMulai) {
            $dateToken = 'mulai-' . $tanggalMulai->format('Ymd');
        } elseif ($tanggalSelesai) {
            $dateToken = 'sampai-' . $tanggalSelesai->format('Ymd');
        }

        $fileName = 'riwayat-kunjungan-' . $dateToken . '-' . now()->format('His') . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            // UTF-8 BOM for better Excel compatibility.
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Nama Tamu',
                'Instansi',
                'Keperluan',
                'No. HP',
                'Status',
                'Waktu Kunjungan',
            ]);

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->nama_lengkap,
                    $row->instansi,
                    $row->keperluan,
                    $row->nomor_hp,
                    BukuTamu::STATUS_LABELS[$row->status] ?? $row->status,
                    $row->created_at?->format('d/m/Y H:i'),
                ]);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function getExportDateRangeDefaults(): array
    {
        $staffNama = $this->getStaffNama();
        $firstVisit = BukuTamu::query()
            ->where('staff_dituju', $staffNama)
            ->orderBy('created_at')
            ->value('created_at');
        $lastVisit = BukuTamu::query()
            ->where('staff_dituju', $staffNama)
            ->orderByDesc('created_at')
            ->value('created_at');

        return [
            'mulai' => $firstVisit ? Carbon::parse($firstVisit)->toDateString() : null,
            'selesai' => $lastVisit ? Carbon::parse($lastVisit)->toDateString() : now()->toDateString(),
        ];
    }

    private function getNamaTamuOptions(): array
    {
        return BukuTamu::query()
            ->where('staff_dituju', $this->getStaffNama())
            ->whereNotNull('nama_lengkap')
            ->where('nama_lengkap', '!=', '')
            ->distinct()
            ->orderBy('nama_lengkap')
            ->pluck('nama_lengkap', 'nama_lengkap')
            ->toArray();
    }

    private function getInstansiOptions(): array
    {
        return BukuTamu::query()
            ->where('staff_dituju', $this->getStaffNama())
            ->whereNotNull('instansi')
            ->where('instansi', '!=', '')
            ->distinct()
            ->orderBy('instansi')
            ->pluck('instansi', 'instansi')
            ->toArray();
    }

    public function updateStatus(BukuTamu $record, string $status): void
    {
        if ($status !== BukuTamu::STATUS_SELESAI) {
            Notification::make()
                ->title('Aksi tidak diizinkan')
                ->body('Staff hanya dapat menyelesaikan kunjungan.')
                ->danger()
                ->send();

            return;
        }

        if (!array_key_exists($status, BukuTamu::STATUS_LABELS)) {
            return;
        }

        if ($record->staff_dituju !== $this->getStaffNama()) {
            Notification::make()
                ->title('Akses ditolak')
                ->body('Anda tidak memiliki izin untuk mengubah data kunjungan ini.')
                ->danger()
                ->send();

            return;
        }

        $record->update(['status' => $status]);

        Notification::make()
            ->title('Status diperbarui')
            ->body('Status kunjungan diubah menjadi ' . (BukuTamu::STATUS_LABELS[$status] ?? $status) . '.')
            ->success()
            ->send();
    }

    private function getStaffNama(): string
    {
        /** @var \App\Models\User $user */
        $user = Filament::auth()->user();
        $pegawai = $user->pegawai;

        return $pegawai?->nama ?? $user->name;
    }
}
