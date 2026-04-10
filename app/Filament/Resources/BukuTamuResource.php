<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BukuTamuResource\Pages;
use App\Models\BukuTamu;
use App\Models\DropdownOption;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BukuTamuResource extends Resource
{
    protected static ?string $model = BukuTamu::class;

    protected static ?string $slug = 'buku-tamu';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Buku Tamu';
    protected static string|\UnitEnum|null $navigationGroup = 'Layanan Tamu';
    protected static ?string $modelLabel = 'Buku Tamu';
    protected static ?string $pluralModelLabel = 'Data Buku Tamu';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->role_user && $user->role_user->hasPermission('buku_tamu');
    }

    public static function canViewAny(): bool
    {
        return static::shouldRegisterNavigation();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('status')
                ->options(BukuTamu::STATUS_LABELS)
                ->required(),
            Forms\Components\Textarea::make('catatan')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query
                ->where(function (Builder $q): void {
                    $q->whereNull('foto_penerimaan')
                        ->orWhere('foto_penerimaan', '');
                }))
            ->columns([
                Tables\Columns\ViewColumn::make('foto_selfie')
                    ->label('Foto')
                    ->view('filament.tables.columns.avatar-column'),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->weight('bold')
                    ->verticallyAlignCenter(),
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->verticallyAlignCenter(),
                Tables\Columns\TextColumn::make('instansi')
                    ->searchable()
                    ->toggleable()
                    ->verticallyAlignCenter(),
                Tables\Columns\TextColumn::make('keperluan')
                    ->limit(40)
                    ->toggleable()
                    ->verticallyAlignCenter(),
                Tables\Columns\TextColumn::make('staff_dituju')
                    ->label('Staff Yang Dituju')
                    ->toggleable()
                    ->verticallyAlignCenter(),
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
                    ->formatStateUsing(fn(string $state) => BukuTamu::STATUS_LABELS[$state] ?? ucfirst($state))
                    ->verticallyAlignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since()
                    ->color('gray')
                    ->tooltip(fn($record) => $record->created_at->format('d/m/Y H:i'))
                    ->sortable()
                    ->verticallyAlignCenter(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(BukuTamu::STATUS_LABELS),
                Tables\Filters\SelectFilter::make('keperluan')
                    ->label('Keperluan')
                    ->options(fn(): array => BukuTamu::query()
                        ->select('keperluan')
                        ->whereNotNull('keperluan')
                        ->where('keperluan', '!=', '')
                        ->orderBy('keperluan')
                        ->distinct()
                        ->pluck('keperluan', 'keperluan')
                        ->all())
                    ->searchable(),
                Tables\Filters\SelectFilter::make('staff_dituju')
                    ->label('Staff Yang Dituju')
                    ->options(fn(): array => BukuTamu::query()
                        ->select('staff_dituju')
                        ->whereNotNull('staff_dituju')
                        ->where('staff_dituju', '!=', '')
                        ->orderBy('staff_dituju')
                        ->distinct()
                        ->pluck('staff_dituju', 'staff_dituju')
                        ->all())
                    ->searchable(),
                Tables\Filters\Filter::make('tanggal')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['tanggal'], fn($q, $date) => $q->whereDate('created_at', $date));
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat Detail')
                        ->icon('heroicon-o-eye')
                        ->color('primary'),
                    Action::make('print')
                        ->label('Print')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->url(fn(BukuTamu $record): string => route('buku-tamu.print', ['id' => $record->id]))
                        ->openUrlInNewTab(true)
                        ->visible(fn(): bool => static::canPrint()),
                    Action::make('delete')
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->extraAttributes([
                            'style' => 'padding: 10px 16px !important;',
                        ])
                        ->modalHeading('Hapus Data Buku Tamu')
                        ->modalDescription(fn(BukuTamu $record): string => static::hasDeletePasswordVerification()
                            ? "Data '{$record->nama_lengkap}' akan dihapus permanen."
                            : "Data '{$record->nama_lengkap}' akan dihapus permanen. Verifikasi password hanya diminta sekali per sesi login.")
                        ->schema(fn(): array => static::hasDeletePasswordVerification()
                            ? []
                            : [
                                Forms\Components\TextInput::make('password')
                                    ->label('Password Super Admin')
                                    ->password()
                                    ->required()
                                    ->autocomplete('current-password')
                                    ->helperText('Ketik password akun Super Admin. Verifikasi ini hanya diminta sekali per sesi login.'),
                            ])
                        ->action(function (array $data, BukuTamu $record): void {
                            static::verifyDeletePasswordForSession($data);

                            $nama = $record->nama_lengkap;
                            $record->delete();

                            Notification::make()
                                ->title('Data berhasil dihapus')
                                ->body("Data tamu '{$nama}' telah dihapus.")
                                ->success()
                                ->send();
                        })
                        ->visible(fn(): bool => static::isSuperAdmin()),
                    Action::make('set_diproses')
                        ->label('Tandai Diproses')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn(BukuTamu $record): bool => static::canChangeStatus() && !static::isSuperAdmin() && $record->status === BukuTamu::STATUS_MENUNGGU)
                        ->action(function (BukuTamu $record): void {
                            $record->update(['status' => BukuTamu::STATUS_DIPROSES]);

                            Notification::make()
                                ->title('Status diperbarui')
                                ->body('Data tamu ditandai sebagai Diproses.')
                                ->success()
                                ->send();
                        }),
                    Action::make('set_selesai')
                        ->label('Tandai Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(BukuTamu $record): bool => static::canChangeStatus() && !static::isSuperAdmin() && $record->status !== BukuTamu::STATUS_SELESAI)
                        ->action(function (BukuTamu $record): void {
                            $record->update(['status' => BukuTamu::STATUS_SELESAI]);

                            Notification::make()
                                ->title('Status diperbarui')
                                ->body('Data tamu ditandai sebagai Selesai.')
                                ->success()
                                ->send();
                        }),
                    Action::make('set_ditolak')
                        ->label('Tandai Ditolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn(BukuTamu $record): bool => static::canChangeStatus() && !static::isSuperAdmin() && $record->status !== BukuTamu::STATUS_DITOLAK)
                        ->action(function (BukuTamu $record): void {
                            $record->update(['status' => BukuTamu::STATUS_DITOLAK]);

                            Notification::make()
                                ->title('Status diperbarui')
                                ->body('Data tamu ditandai sebagai Ditolak.')
                                ->warning()
                                ->send();
                        }),
                ])
                    ->label(false)
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->headerActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_print')
                        ->label('Print')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->visible(fn(): bool => static::isSuperAdmin() && static::canPrint())
                        ->url(route('buku-tamu.print-bulk'))
                        ->livewireClickHandlerEnabled(false)
                        ->accessSelectedRecords(false)
                        ->openUrlInNewTab(true)
                        ->extraAttributes([
                            'style' => 'padding: 10px 16px !important;',
                            'x-bind:href' => "`\${window.location.origin}/print/buku-tamu-bulk?ids=\${[...selectedRecords].join(',')}`",
                        ]),
                    BulkAction::make('bulk_delete')
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->extraAttributes([
                            'style' => 'padding: 10px 16px !important;',
                        ])
                        ->modalHeading('Hapus Data Buku Tamu Terpilih')
                        ->modalDescription(fn(): string => static::hasDeletePasswordVerification()
                            ? 'Semua data buku tamu yang dipilih akan dihapus permanen.'
                            : 'Semua data buku tamu yang dipilih akan dihapus permanen. Verifikasi password hanya diminta sekali per sesi login.')
                        ->schema(fn(): array => static::hasDeletePasswordVerification()
                            ? []
                            : [
                                Forms\Components\TextInput::make('password')
                                    ->label('Password Super Admin')
                                    ->password()
                                    ->required()
                                    ->autocomplete('current-password')
                                    ->helperText('Ketik password akun Super Admin. Verifikasi ini hanya diminta sekali per sesi login.'),
                            ])
                        ->action(function (array $data, $records): void {
                            static::verifyDeletePasswordForSession($data);

                            $count = $records->count();
                            $records->each(fn(BukuTamu $record): bool => $record->delete());

                            Notification::make()
                                ->title('Data berhasil dihapus')
                                ->body("{$count} data tamu berhasil dihapus.")
                                ->success()
                                ->send();
                        })
                        ->visible(fn(): bool => static::isSuperAdmin())
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('bulk_set_diproses')
                        ->label('Tandai Diproses')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn(): bool => static::canChangeStatus() && !static::isSuperAdmin())
                        ->action(function ($records): void {
                            $count = 0;

                            $records->each(function (BukuTamu $record) use (&$count): void {
                                if ($record->status !== BukuTamu::STATUS_DIPROSES) {
                                    $record->update(['status' => BukuTamu::STATUS_DIPROSES]);
                                    $count++;
                                }
                            });

                            Notification::make()
                                ->title('Bulk update selesai')
                                ->body("{$count} data tamu berhasil ditandai Diproses.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('bulk_set_selesai')
                        ->label('Tandai Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(): bool => static::canChangeStatus() && !static::isSuperAdmin())
                        ->action(function ($records): void {
                            $count = 0;

                            $records->each(function (BukuTamu $record) use (&$count): void {
                                if ($record->status !== BukuTamu::STATUS_SELESAI) {
                                    $record->update(['status' => BukuTamu::STATUS_SELESAI]);
                                    $count++;
                                }
                            });

                            Notification::make()
                                ->title('Bulk update selesai')
                                ->body("{$count} data tamu berhasil ditandai Selesai.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('bulk_set_ditolak')
                        ->label('Tandai Ditolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn(): bool => static::canChangeStatus() && !static::isSuperAdmin())
                        ->action(function ($records): void {
                            $count = 0;

                            $records->each(function (BukuTamu $record) use (&$count): void {
                                if ($record->status !== BukuTamu::STATUS_DITOLAK) {
                                    $record->update(['status' => BukuTamu::STATUS_DITOLAK]);
                                    $count++;
                                }
                            });

                            Notification::make()
                                ->title('Bulk update selesai')
                                ->body("{$count} data tamu berhasil ditandai Ditolak.")
                                ->warning()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function makeExportExcelAction(): Action
    {
        $dateRangeDefaults = static::getExportDateRangeDefaults();

        return Action::make('export_excel')
            ->label('Export Excel')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->extraAttributes([
                'style' => 'padding: 10px 16px !important;',
            ])
            ->modalHeading('Export Data Buku Tamu (Excel)')
            ->modalDescription('Pilih filter data. Anda bisa memilih lebih dari satu nama, keperluan, dan staff tujuan.')
            ->schema([
                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->default($dateRangeDefaults['mulai'])
                    ->native(false),
                Forms\Components\DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->default($dateRangeDefaults['selesai'])
                    ->native(false),
                Forms\Components\Select::make('nama_tamu')
                    ->label('Per Orang (Nama Tamu)')
                    ->options(static::getNamaTamuOptions())
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->noOptionsMessage('Belum ada data tamu yang tersedia.')
                    ->noSearchResultsMessage('Data tamu tidak ditemukan.')
                    ->native(false)
                    ->placeholder('Semua tamu'),
                Forms\Components\Select::make('keperluan')
                    ->label('Keperluan')
                    ->options(static::getKeperluanOptions())
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->noOptionsMessage('Belum ada data keperluan yang tersedia.')
                    ->noSearchResultsMessage('Data keperluan tidak ditemukan.')
                    ->native(false)
                    ->placeholder('Semua keperluan'),
                Forms\Components\Select::make('staff_dituju')
                    ->label('Ke Mana / Staff Tujuan')
                    ->options(static::getStaffDitujuOptions())
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->noOptionsMessage('Belum ada data staff tujuan yang tersedia.')
                    ->noSearchResultsMessage('Data staff tujuan tidak ditemukan.')
                    ->native(false)
                    ->placeholder('Semua staff tujuan'),
            ])
            ->action(fn(array $data) => static::exportExcel($data));
    }

    protected static function exportExcel(array $filters = []): StreamedResponse
    {
        $tanggalMulai = !empty($filters['tanggal_mulai']) ? Carbon::parse($filters['tanggal_mulai'])->startOfDay() : null;
        $tanggalSelesai = !empty($filters['tanggal_selesai']) ? Carbon::parse($filters['tanggal_selesai'])->endOfDay() : null;
        $namaTamuDipilih = static::normalizeMultiValue($filters['nama_tamu'] ?? []);
        $keperluanDipilih = static::normalizeMultiValue($filters['keperluan'] ?? []);
        $staffDitujuDipilih = static::normalizeMultiValue($filters['staff_dituju'] ?? []);

        if ($tanggalMulai && $tanggalSelesai && $tanggalMulai->gt($tanggalSelesai)) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai->copy()->startOfDay(), $tanggalMulai->copy()->endOfDay()];
        }

        $query = BukuTamu::query()
            ->where(function (Builder $q): void {
                $q->whereNull('foto_penerimaan')
                    ->orWhere('foto_penerimaan', '');
            })
            ->latest('created_at');

        if ($tanggalMulai) {
            $query->where('created_at', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->where('created_at', '<=', $tanggalSelesai);
        }

        if ($namaTamuDipilih !== []) {
            $query->whereIn('nama_lengkap', $namaTamuDipilih);
        }

        if ($keperluanDipilih !== []) {
            $query->whereIn('keperluan', $keperluanDipilih);
        }

        if ($staffDitujuDipilih !== []) {
            $query->whereIn('staff_dituju', $staffDitujuDipilih);
        }

        $rows = $query->get([
            'nama_lengkap',
            'jenis_id',
            'nik',
            'instansi',
            'keperluan',
            'staff_dituju',
            'status',
            'created_at',
        ]);

        $spreadsheet = new Spreadsheet();
        $usedTitles = [];

        $mainSheet = $spreadsheet->getActiveSheet();
        $mainSheet->setTitle('Semua Data');
        $usedTitles[] = 'Semua Data';

        static::fillExportSheet(
            $mainSheet,
            $rows,
            'Rekap hasil filter export Buku Tamu',
            [
                'Tanggal Mulai' => $tanggalMulai?->format('d/m/Y') ?? 'Semua',
                'Tanggal Selesai' => $tanggalSelesai?->format('d/m/Y') ?? 'Semua',
                'Nama Tamu' => $namaTamuDipilih !== [] ? implode(', ', $namaTamuDipilih) : 'Semua',
                'Keperluan' => $keperluanDipilih !== [] ? implode(', ', $keperluanDipilih) : 'Semua',
                'Staff Tujuan' => $staffDitujuDipilih !== [] ? implode(', ', $staffDitujuDipilih) : 'Semua',
            ]
        );

        foreach ($namaTamuDipilih as $nama) {
            $sheet = $spreadsheet->createSheet();
            $sheetTitle = static::makeUniqueSheetTitle('Nama - ' . $nama, $usedTitles);
            $sheet->setTitle($sheetTitle);
            static::fillExportSheet($sheet, $rows->where('nama_lengkap', $nama)->values(), 'Data untuk nama tamu: ' . $nama);
        }

        foreach ($keperluanDipilih as $keperluan) {
            $sheet = $spreadsheet->createSheet();
            $sheetTitle = static::makeUniqueSheetTitle('Keperluan - ' . $keperluan, $usedTitles);
            $sheet->setTitle($sheetTitle);
            static::fillExportSheet($sheet, $rows->where('keperluan', $keperluan)->values(), 'Data untuk keperluan: ' . $keperluan);
        }

        foreach ($staffDitujuDipilih as $staff) {
            $sheet = $spreadsheet->createSheet();
            $sheetTitle = static::makeUniqueSheetTitle('Tujuan - ' . $staff, $usedTitles);
            $sheet->setTitle($sheetTitle);
            static::fillExportSheet($sheet, $rows->where('staff_dituju', $staff)->values(), 'Data untuk staff tujuan: ' . $staff);
        }

        $dateToken = 'semua';

        if ($tanggalMulai && $tanggalSelesai) {
            $dateToken = $tanggalMulai->format('Ymd') . '-sampai-' . $tanggalSelesai->format('Ymd');
        } elseif ($tanggalMulai) {
            $dateToken = 'mulai-' . $tanggalMulai->format('Ymd');
        } elseif ($tanggalSelesai) {
            $dateToken = 'sampai-' . $tanggalSelesai->format('Ymd');
        }

        $fileName = 'buku-tamu-' . $dateToken . '-' . now()->format('His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer): void {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    protected static function normalizeMultiValue(mixed $value): array
    {
        if (is_string($value)) {
            $value = [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($value as $item) {
            $item = trim((string) $item);

            if ($item === '') {
                continue;
            }

            $normalized[$item] = $item;
        }

        return array_values($normalized);
    }

    protected static function fillExportSheet(Worksheet $sheet, $rows, string $title, array $filters = []): void
    {
        $headers = ['No', 'Nama Tamu', 'Jenis ID', 'No. Identitas', 'Instansi', 'Keperluan', 'Staff Tujuan', 'Status', 'Waktu Kunjungan'];
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:I1');

        $filterSummary = 'Filter: ';
        $summaryParts = [];

        foreach ($filters as $filterLabel => $filterValue) {
            $summaryParts[] = $filterLabel . ' = ' . $filterValue;
        }

        $filterSummary .= $summaryParts !== [] ? implode(' | ', $summaryParts) : 'Semua data';
        $sheet->setCellValue('A2', $filterSummary);
        $sheet->mergeCells('A2:I2');

        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columns[$index] . '4', $header);
        }

        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
                'color' => ['rgb' => '111827'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:I2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
                'color' => ['rgb' => '4B5563'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A4:I4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '111827'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        $rowIndex = 5;

        foreach ($rows as $index => $row) {
            $sheet->setCellValue("A{$rowIndex}", $index + 1);
            $sheet->setCellValue("B{$rowIndex}", (string) ($row->nama_lengkap ?? '-'));
            $sheet->setCellValue("C{$rowIndex}", strtoupper((string) ($row->jenis_id ?? '-')));
            $sheet->setCellValueExplicit("D{$rowIndex}", (string) ($row->nik ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue("E{$rowIndex}", (string) ($row->instansi ?? '-'));
            $sheet->setCellValue("F{$rowIndex}", (string) ($row->keperluan ?? '-'));
            $sheet->setCellValue("G{$rowIndex}", (string) ($row->staff_dituju ?? '-'));
            $sheet->setCellValue("H{$rowIndex}", (string) (BukuTamu::STATUS_LABELS[$row->status] ?? $row->status ?? '-'));
            $sheet->setCellValue("I{$rowIndex}", $row->created_at?->format('d/m/Y H:i') ?? '-');
            $rowIndex++;
        }

        $lastDataRow = max(5, $rowIndex - 1);

        $sheet->getStyle("A4:I{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(28);
        $sheet->getColumnDimension('F')->setWidth(28);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(16);
        $sheet->getColumnDimension('I')->setWidth(20);

        $sheet->freezePane('A5');
    }

    protected static function makeUniqueSheetTitle(string $baseTitle, array &$usedTitles): string
    {
        $title = trim(preg_replace('/[\\\\\/?*\[\]:]/', '-', $baseTitle) ?? '');

        if ($title === '') {
            $title = 'Sheet';
        }

        $title = mb_substr($title, 0, 31);

        if (! in_array($title, $usedTitles, true)) {
            $usedTitles[] = $title;

            return $title;
        }

        $counter = 2;

        do {
            $suffix = ' (' . $counter . ')';
            $candidate = mb_substr($title, 0, 31 - mb_strlen($suffix)) . $suffix;
            $counter++;
        } while (in_array($candidate, $usedTitles, true));

        $usedTitles[] = $candidate;

        return $candidate;
    }

    protected static function getExportDateRangeDefaults(): array
    {
        $firstVisit = BukuTamu::query()
            ->where(function (Builder $query): void {
                $query->whereNull('foto_penerimaan')
                    ->orWhere('foto_penerimaan', '');
            })
            ->orderBy('created_at')
            ->value('created_at');

        $lastVisit = BukuTamu::query()
            ->where(function (Builder $query): void {
                $query->whereNull('foto_penerimaan')
                    ->orWhere('foto_penerimaan', '');
            })
            ->orderByDesc('created_at')
            ->value('created_at');

        return [
            'mulai' => $firstVisit ? Carbon::parse($firstVisit)->toDateString() : null,
            'selesai' => $lastVisit ? Carbon::parse($lastVisit)->toDateString() : now()->toDateString(),
        ];
    }

    protected static function getNamaTamuOptions(): array
    {
        return BukuTamu::query()
            ->where(function (Builder $query): void {
                $query->whereNull('foto_penerimaan')
                    ->orWhere('foto_penerimaan', '');
            })
            ->whereNotNull('nama_lengkap')
            ->where('nama_lengkap', '!=', '')
            ->distinct()
            ->orderBy('nama_lengkap')
            ->pluck('nama_lengkap', 'nama_lengkap')
            ->toArray();
    }

    protected static function getKeperluanOptions(): array
    {
        return BukuTamu::query()
            ->where(function (Builder $query): void {
                $query->whereNull('foto_penerimaan')
                    ->orWhere('foto_penerimaan', '');
            })
            ->whereNotNull('keperluan')
            ->where('keperluan', '!=', '')
            ->distinct()
            ->orderBy('keperluan')
            ->pluck('keperluan', 'keperluan')
            ->toArray();
    }

    protected static function getStaffDitujuOptions(): array
    {
        return BukuTamu::query()
            ->where(function (Builder $query): void {
                $query->whereNull('foto_penerimaan')
                    ->orWhere('foto_penerimaan', '');
            })
            ->whereNotNull('staff_dituju')
            ->where('staff_dituju', '!=', '')
            ->distinct()
            ->orderBy('staff_dituju')
            ->pluck('staff_dituju', 'staff_dituju')
            ->toArray();
    }

    protected static function canChangeStatus(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) ($user && $user->role_user && $user->role_user->hasPermission('can_change_status'));
    }

    protected static function canPrint(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) ($user && $user->role_user && $user->role_user->hasPermission('can_print'));
    }

    protected static function isSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) ($user?->hasRole('Super Admin'));
    }

    protected static function deletePasswordVerificationSessionKey(): string
    {
        return 'buku_tamu.delete_password_verified_user_id';
    }

    protected static function hasDeletePasswordVerification(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return (int) session(static::deletePasswordVerificationSessionKey()) === (int) $user->id;
    }

    protected static function verifyDeletePasswordForSession(array $data): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            throw ValidationException::withMessages([
                'password' => 'Sesi login tidak valid. Silakan login ulang.',
            ]);
        }

        if (static::hasDeletePasswordVerification()) {
            return;
        }

        if (! Hash::check((string) ($data['password'] ?? ''), (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password tidak sesuai dengan akun yang sedang login.',
            ]);
        }

        session([
            static::deletePasswordVerificationSessionKey() => (int) $user->id,
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBukuTamus::route('/'),
            'view' => Pages\ViewBukuTamu::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
