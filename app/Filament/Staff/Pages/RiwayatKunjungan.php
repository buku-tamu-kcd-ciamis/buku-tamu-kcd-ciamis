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
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function table(Table $table): Table
    {
        $staffNama = $this->getStaffNama();
        $dateRangeDefaults = $this->getExportDateRangeDefaults();

        return $table
            ->query(
                BukuTamu::query()
                    ->where('staff_dituju', $staffNama)
                    ->latest()
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
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => BukuTamu::STATUS_LABELS[$state] ?? $state)
                    ->colors([
                        'warning' => 'menunggu',
                        'primary' => 'diproses',
                        'success' => 'selesai',
                        'danger' => 'ditolak',
                        'gray' => 'dibatalkan',
                    ]),
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
                Tables\Filters\Filter::make('bulan_ini')
                    ->label('Bulan Ini')
                    ->query(
                        fn($query) => $query
                            ->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                    )
                    ->default(false),
            ])
            ->headerActions([
                Action::make('export_csv')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('Export Riwayat Kunjungan')
                    ->modalDescription('Filter data yang ingin diunduh.')
                    ->form([
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
            ->actions([
                ActionGroup::make([
                    Action::make('status_menunggu')
                        ->label('Menunggu')
                        ->icon('heroicon-o-clock')
                        ->color('gray')
                        ->visible(fn(BukuTamu $record) => $record->status !== BukuTamu::STATUS_MENUNGGU)
                        ->action(fn(BukuTamu $record) => $this->updateStatus($record, BukuTamu::STATUS_MENUNGGU)),
                    Action::make('status_diproses')
                        ->label('Diproses')
                        ->icon('heroicon-o-arrow-path')
                        ->color('gray')
                        ->visible(fn(BukuTamu $record) => $record->status !== BukuTamu::STATUS_DIPROSES)
                        ->action(fn(BukuTamu $record) => $this->updateStatus($record, BukuTamu::STATUS_DIPROSES)),
                    Action::make('status_selesai')
                        ->label('Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('gray')
                        ->visible(fn(BukuTamu $record) => $record->status !== BukuTamu::STATUS_SELESAI)
                        ->action(fn(BukuTamu $record) => $this->updateStatus($record, BukuTamu::STATUS_SELESAI)),
                    Action::make('status_ditolak')
                        ->label('Ditolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->visible(fn(BukuTamu $record) => $record->status !== BukuTamu::STATUS_DITOLAK)
                        ->action(fn(BukuTamu $record) => $this->updateStatus($record, BukuTamu::STATUS_DITOLAK)),
                    Action::make('status_dibatalkan')
                        ->label('Dibatalkan')
                        ->icon('heroicon-o-no-symbol')
                        ->color('gray')
                        ->visible(fn(BukuTamu $record) => $record->status !== BukuTamu::STATUS_DIBATALKAN)
                        ->action(fn(BukuTamu $record) => $this->updateStatus($record, BukuTamu::STATUS_DIBATALKAN)),
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
        $user = Auth::user();
        $pegawai = $user->pegawai;

        return $pegawai?->nama ?? $user->name;
    }
}
