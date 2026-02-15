<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BukuTamuResource\Pages;
use App\Models\BukuTamu;
use App\Models\DropdownOption;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class BukuTamuResource extends Resource
{
    protected static ?string $model = BukuTamu::class;

    protected static ?string $slug = 'buku-tamu';
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Buku Tamu';
    protected static ?string $navigationGroup = 'Layanan Tamu';
    protected static ?string $modelLabel = 'Buku Tamu';
    protected static ?string $pluralModelLabel = 'Data Buku Tamu';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->options(BukuTamu::STATUS_LABELS)
                ->required(),
            Forms\Components\Textarea::make('catatan')
                ->label('Catatan')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto_selfie')
                    ->label('Foto')
                    ->circular()
                    ->size(40)
                    ->verticallyAlignCenter()
                    ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=G&background=0F9455&color=fff'),
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
                Tables\Columns\TextColumn::make('bagian_dituju')
                    ->label('Bagian Dituju')
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
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['tanggal'], fn($q, $date) => $q->whereDate('created_at', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('ubah_status')
                        ->label('Ubah Status')
                        ->icon('heroicon-s-pencil-square')
                        ->color('warning')
                        ->visible(function (BukuTamu $record) {
                            /** @var User $user */
                            $user = Auth::user();
                            return $record->status !== 'selesai' && $user && !$user->hasRole('Ketua KCD');
                        })
                        ->form([
                            Forms\Components\Placeholder::make('info_tamu')
                                ->label('Detail Tamu')
                                ->content(fn(BukuTamu $record) => new HtmlString(
                                    '<div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-3 text-sm leading-relaxed">' .
                                        '<div class="flex gap-4 mb-3">' .
                                        '<div class="flex gap-3">' .
                                        ($record->foto_selfie ? '<img src="' . e($record->foto_selfie) . '" class="w-20 h-20 rounded-lg object-cover border-2 border-gray-300 dark:border-gray-600" />' : '') .
                                        ($record->tanda_tangan ? '<div><strong class="text-xs text-gray-600 dark:text-gray-300">Tanda Tangan:</strong><br><img src="' . e($record->tanda_tangan) . '" class="w-20 h-12 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700" /></div>' : '') .
                                        '</div>' .
                                        '<div class="flex-1">' .
                                        '<strong class="text-base dark:text-white">' . e($record->nama_lengkap) . '</strong><br>' .
                                        '<span class="text-gray-600 dark:text-gray-300">NIK: ' . e($record->nik) . '</span><br>' .
                                        '<span class="text-gray-600 dark:text-gray-300">Instansi: ' . e($record->instansi ?? '-') . '</span>' .
                                        '</div>' .
                                        '</div>' .
                                        ($record->foto_penerimaan ? '<div class="mb-3"><strong class="text-xs text-gray-600 dark:text-gray-300">Foto Penerimaan Berkas:</strong><br><img src="' . e($record->foto_penerimaan) . '" class="w-30 h-20 border border-gray-300 dark:border-gray-600 rounded object-cover" /></div>' : '') .
                                        '<div class="border-t border-gray-300 dark:border-gray-600 pt-2 mt-2 dark:text-gray-200">' .
                                        '<strong>Keperluan:</strong> ' . e($record->keperluan) . '<br>' .
                                        '<strong>Bagian Dituju:</strong> ' . e($record->bagian_dituju) . '<br>' .
                                        '<strong>Waktu:</strong> ' . $record->created_at->format('d/m/Y H:i') .
                                        '</div>' .
                                        '</div>'
                                )),
                            Forms\Components\Select::make('nama_penerima')
                                ->label('Nama Penerima')
                                ->options(DropdownOption::getOptions(DropdownOption::CATEGORY_PEGAWAI_PIKET))
                                ->searchable()
                                ->allowHtml(false)
                                ->placeholder('Pilih nama penerima'),
                            Forms\Components\Select::make('status')
                                ->options([
                                    'menunggu' => 'Menunggu',
                                    'diproses' => 'Diproses',
                                    'selesai' => 'Selesai',
                                    'dibatalkan' => 'Dibatalkan',
                                ])
                                ->required(),
                            Forms\Components\Textarea::make('catatan')
                                ->label('Catatan')
                                ->rows(3),
                        ])
                        ->fillForm(fn(BukuTamu $record) => [
                            'status' => $record->status,
                            'catatan' => $record->catatan,
                        ])
                        ->action(function (BukuTamu $record, array $data) {
                            $record->update($data);
                        })
                        ->modalHeading('Ubah Status Buku Tamu')
                        ->modalSubmitActionLabel('Simpan'),
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail')
                        ->icon('heroicon-s-eye'),
                    Tables\Actions\Action::make('print')
                        ->label('Cetak')
                        ->icon('heroicon-s-printer')
                        ->color('success')
                        ->url(fn(BukuTamu $record) => route('buku-tamu.print', $record->id))
                        ->openUrlInNewTab()
                        ->visible(function (BukuTamu $record) {
                            /** @var User $user */
                            $user = Auth::user();
                            return $record->status === 'selesai' && $user && !$user->hasRole('Ketua KCD');
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-s-trash')
                        ->visible(function () {
                            /** @var User $user */
                            $user = Auth::user();
                            return $user && $user->hasRole('Super Admin');
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Buku Tamu')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data ini? Data yang dihapus tidak dapat dikembalikan.')
                        ->modalSubmitActionLabel('Hapus')
                        ->successNotificationTitle('Data berhasil dihapus'),
                ])
                    ->label(false)
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button()
                    ->color('gray'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('print_bulk')
                    ->label('Cetak Laporan')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(function () {
                        /** @var User $user */
                        $user = Auth::user();
                        return $user && !$user->hasRole('Ketua KCD');
                    })
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Akhir'),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama')
                            ->placeholder('Cari berdasarkan nama'),
                        Forms\Components\Select::make('kabupaten_kota')
                            ->label('Kabupaten/Kota')
                            ->searchable()
                            ->options(DropdownOption::getOptions(DropdownOption::CATEGORY_KABUPATEN_KOTA))
                            ->placeholder('Pilih kabupaten/kota'),
                        Forms\Components\Select::make('keperluan')
                            ->label('Keperluan')
                            ->searchable()
                            ->options(DropdownOption::getOptions(DropdownOption::CATEGORY_KEPERLUAN))
                            ->placeholder('Pilih keperluan'),
                    ])
                    ->action(function (array $data, $livewire) {
                        $query = http_build_query(array_filter([
                            'start_date' => $data['start_date'] ?? null,
                            'end_date' => $data['end_date'] ?? null,
                            'nama' => $data['nama'] ?? null,
                            'kabupaten_kota' => $data['kabupaten_kota'] ?? null,
                            'keperluan' => $data['keperluan'] ?? null,
                        ]));

                        $url = route('buku-tamu.print-bulk') . ($query ? '?' . $query : '');

                        // Dispatch browser event to open in new tab
                        $livewire->dispatch('open-url-in-new-tab', url: $url);
                    })
                    ->modalHeading('Filter Laporan Buku Tamu')
                    ->modalSubmitActionLabel('Cetak'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(function () {
                        /** @var User $user */
                        $user = Auth::user();
                        return $user && $user->hasRole('Super Admin');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Terpilih')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data yang dipilih? Data yang dihapus tidak dapat dikembalikan.')
                    ->modalSubmitActionLabel('Hapus')
                    ->successNotificationTitle('Data berhasil dihapus'),
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
