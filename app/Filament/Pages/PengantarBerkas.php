<?php

namespace App\Filament\Pages;

use App\Models\BukuTamu;
use App\Models\DropdownOption;
use App\Models\User;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class PengantarBerkas extends Page implements HasTable
{
  use InteractsWithTable;

  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel = 'Pengantar Berkas';
  protected static ?string $navigationGroup = 'Layanan Tamu';
  protected static ?string $title = 'Daftar Pengantar Berkas';
  protected static ?int $navigationSort = 3;
  protected static string $view = 'filament.pages.pengantar-berkas';

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->hasRole('Super Admin');
  }

  public function table(Table $table): Table
  {
    return $table
      ->query(
        BukuTamu::query()
          ->where(function ($q) {
            $q->where('keperluan', 'like', '%berkas%')
              ->orWhere('keperluan', 'like', '%surat%')
              ->orWhere('keperluan', 'like', '%dokumen%')
              ->orWhere('keperluan', 'like', '%legalisir%');
          })
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
        Tables\Columns\TextColumn::make('bagian_dituju')
          ->label('Bagian Dituju'),
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
          ->tooltip(fn($record) => $record->created_at->format('d/m/Y H:i'))
          ->sortable(),
      ])
      ->defaultSort('created_at', 'desc')
      ->defaultPaginationPageOption(10)
      ->paginationPageOptions([10])
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->options(BukuTamu::STATUS_LABELS),
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
              return $record->status !== 'selesai' && $user && $user->hasRole('Super Admin');
            })
            ->form([
              Forms\Components\Placeholder::make('info_tamu')
                ->label('Detail Tamu')
                ->content(fn(BukuTamu $record) => new \Illuminate\Support\HtmlString(
                  '<div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-3 text-sm leading-relaxed">' .
                  '<div class="flex gap-4 mb-3">' .
                  '<div class="flex gap-3">' .
                  ($record->foto_selfie_url ? '<img src="' . e($record->foto_selfie_url) . '" class="w-20 h-20 rounded-lg object-cover border-2 border-gray-300 dark:border-gray-600" />' : '') .
                  ($record->tanda_tangan_url ? '<div><strong class="text-xs text-gray-600 dark:text-gray-300">Tanda Tangan:</strong><br><img src="' . e($record->tanda_tangan_url) . '" class="w-20 h-12 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700" /></div>' : '') .
                  '</div>' .
                  '<div class="flex-1">' .
                  '<strong class="text-base dark:text-white">' . e($record->nama_lengkap) . '</strong><br>' .
                  '<span class="text-gray-600 dark:text-gray-300">NIK: ' . e($record->nik) . '</span><br>' .
                  '<span class="text-gray-600 dark:text-gray-300">Instansi: ' . e($record->instansi ?? '-') . '</span>' .
                  '</div>' .
                  '</div>' .
                  ($record->foto_penerimaan_url ? '<div class="mb-3"><strong class="text-xs text-gray-600 dark:text-gray-300">Foto Penerimaan Berkas:</strong><br><img src="' . e($record->foto_penerimaan_url) . '" class="w-30 h-20 border border-gray-300 dark:border-gray-600 rounded object-cover" /></div>' : '') .
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
            ->modalHeading('Ubah Status Pengantar Berkas')
            ->modalSubmitActionLabel('Simpan'),
          Tables\Actions\ViewAction::make()
            ->label('Lihat Detail')
            ->icon('heroicon-s-eye')
            ->url(fn(BukuTamu $record) => \App\Filament\Resources\BukuTamuResource::getUrl('view', ['record' => $record])),
          Tables\Actions\Action::make('print')
            ->label('Cetak')
            ->icon('heroicon-s-printer')
            ->color('success')
            ->url(fn(BukuTamu $record) => route('buku-tamu.print', $record->id))
            ->openUrlInNewTab()
            ->visible(function (BukuTamu $record) {
              /** @var User $user */
              $user = Auth::user();
              return $record->status === 'selesai' && $user && $user->hasRole('Super Admin');
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
            ->modalHeading('Hapus Data Pengantar Berkas')
            ->modalDescription('Apakah Anda yakin ingin menghapus data ini? Data yang dihapus tidak dapat dikembalikan.')
            ->modalSubmitActionLabel('Hapus')
            ->successNotificationTitle('Data berhasil dihapus'),
        ])
          ->label(false)
          ->icon('heroicon-m-ellipsis-vertical')
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
            return $user && $user->hasRole('Super Admin');
          })
          ->form([
            Forms\Components\DatePicker::make('start_date')
              ->label('Tanggal Mulai'),
            Forms\Components\DatePicker::make('end_date')
              ->label('Tanggal Akhir'),
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
          ->action(function (array $data) {
            $query = http_build_query(array_filter([
              'start_date' => $data['start_date'] ?? null,
              'end_date' => $data['end_date'] ?? null,
              'kabupaten_kota' => $data['kabupaten_kota'] ?? null,
              'keperluan' => $data['keperluan'] ?? null,
              'type' => 'pengantar',
            ]));

            $url = route('buku-tamu.print-bulk') . ($query ? '?' . $query : '');
            return redirect($url);
          })
          ->modalHeading('Filter Laporan Pengantar Berkas')
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
}
