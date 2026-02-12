<?php

namespace App\Filament\Piket\Pages;

use App\Models\BukuTamu;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class PengantarBerkas extends Page implements HasTable
{
  use InteractsWithTable;

  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel = 'Pengantar Berkas';
  protected static ?string $navigationGroup = 'Layanan Tamu';
  protected static ?string $title = 'Daftar Pengantar Berkas';
  protected static ?int $navigationSort = 2;
  protected static string $view = 'filament.piket.pages.pengantar-berkas';

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
        Tables\Columns\ImageColumn::make('foto_selfie')
          ->label('Foto')
          ->circular()
          ->size(40)
          ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=G&background=0F9455&color=fff'),
        Tables\Columns\TextColumn::make('nama_lengkap')
          ->label('Nama')
          ->searchable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('instansi')
          ->searchable(),
        Tables\Columns\TextColumn::make('keperluan'),
        Tables\Columns\TextColumn::make('bagian_dituju')
          ->label('Bagian Dituju'),
        Tables\Columns\BadgeColumn::make('status')
          ->colors([
            'warning' => 'menunggu',
            'info' => 'diproses',
            'success' => 'selesai',
            'danger' => 'ditolak',
            'gray' => 'dibatalkan',
          ])
          ->formatStateUsing(fn(string $state) => BukuTamu::STATUS_LABELS[$state] ?? ucfirst($state)),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Waktu')
          ->since()
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
            ->form([
              Forms\Components\Placeholder::make('info_tamu')
                ->label('Detail Tamu')
                ->content(fn(BukuTamu $record) => new \Illuminate\Support\HtmlString(
                  '<div style="background:#f9fafb;border-radius:8px;padding:12px;font-size:13px;line-height:1.8">' .
                    '<div style="display:flex;gap:16px;margin-bottom:12px;">' .
                    '<div style="display:flex;gap:12px;">' .
                    ($record->foto_selfie ? '<img src="' . e($record->foto_selfie) . '" style="width:80px;height:80px;border-radius:8px;object-fit:cover;border:2px solid #e5e7eb;" />' : '') .
                    ($record->tanda_tangan ? '<div><strong style="font-size:11px;color:#6b7280;">Tanda Tangan:</strong><br><img src="' . e($record->tanda_tangan) . '" style="width:80px;height:50px;border:1px solid #e5e7eb;border-radius:4px;background:#fff;" /></div>' : '') .
                    '</div>' .
                    '<div style="flex:1;">' .
                    '<strong style="font-size:15px;">' . e($record->nama_lengkap) . '</strong><br>' .
                    '<span style="color:#6b7280;">NIK: ' . e($record->nik) . '</span><br>' .
                    '<span style="color:#6b7280;">Instansi: ' . e($record->instansi ?? '-') . '</span>' .
                    '</div>' .
                    '</div>' .
                    ($record->foto_penerimaan ? '<div style="margin-bottom:12px;"><strong style="font-size:11px;color:#6b7280;">Foto Penerimaan Berkas:</strong><br><img src="' . e($record->foto_penerimaan) . '" style="width:120px;height:80px;border:1px solid #e5e7eb;border-radius:4px;object-fit:cover;" /></div>' : '') .
                    '<div style="border-top:1px solid #e5e7eb;padding-top:8px;margin-top:8px;">' .
                    '<strong>Keperluan:</strong> ' . e($record->keperluan) . '<br>' .
                    '<strong>Bagian Dituju:</strong> ' . e($record->bagian_dituju) . '<br>' .
                    '<strong>Waktu:</strong> ' . $record->created_at->format('d/m/Y H:i') .
                    '</div>' .
                    '</div>'
                )),
              Forms\Components\TextInput::make('nama_penerima')
                ->label('Nama Penerima')
                ->placeholder('Masukkan nama penerima'),
              Forms\Components\Select::make('status')
                ->options(BukuTamu::STATUS_LABELS)
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
            ->modalButton('Simpan'),
          Tables\Actions\ViewAction::make()
            ->label('Lihat Detail')
            ->icon('heroicon-s-eye')
            ->url(fn(BukuTamu $record) => \App\Filament\Piket\Resources\KunjunganResource::getUrl('view', ['record' => $record])),
          Tables\Actions\Action::make('print')
            ->label('Cetak')
            ->icon('heroicon-s-printer')
            ->color('success')
            ->url(fn(BukuTamu $record) => route('buku-tamu.print', $record->id))
            ->openUrlInNewTab()
            ->visible(fn(BukuTamu $record) => $record->status === 'selesai'),
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
          ->form([
            Forms\Components\DatePicker::make('start_date')
              ->label('Tanggal Mulai'),
            Forms\Components\DatePicker::make('end_date')
              ->label('Tanggal Akhir'),
            Forms\Components\Select::make('kabupaten_kota')
              ->label('Kabupaten/Kota')
              ->searchable()
              ->options([
                'Kab. Bandung' => 'Kab. Bandung',
                'Kab. Bandung Barat' => 'Kab. Bandung Barat',
                'Kab. Bekasi' => 'Kab. Bekasi',
                'Kab. Bogor' => 'Kab. Bogor',
                'Kab. Ciamis' => 'Kab. Ciamis',
                'Kab. Cianjur' => 'Kab. Cianjur',
                'Kab. Cirebon' => 'Kab. Cirebon',
                'Kab. Garut' => 'Kab. Garut',
                'Kab. Indramayu' => 'Kab. Indramayu',
                'Kab. Karawang' => 'Kab. Karawang',
                'Kab. Kuningan' => 'Kab. Kuningan',
                'Kab. Majalengka' => 'Kab. Majalengka',
                'Kab. Pangandaran' => 'Kab. Pangandaran',
                'Kab. Purwakarta' => 'Kab. Purwakarta',
                'Kab. Subang' => 'Kab. Subang',
                'Kab. Sukabumi' => 'Kab. Sukabumi',
                'Kab. Sumedang' => 'Kab. Sumedang',
                'Kab. Tasikmalaya' => 'Kab. Tasikmalaya',
                'Kota Bandung' => 'Kota Bandung',
                'Kota Banjar' => 'Kota Banjar',
                'Kota Bekasi' => 'Kota Bekasi',
                'Kota Bogor' => 'Kota Bogor',
                'Kota Cimahi' => 'Kota Cimahi',
                'Kota Cirebon' => 'Kota Cirebon',
                'Kota Depok' => 'Kota Depok',
                'Kota Sukabumi' => 'Kota Sukabumi',
                'Kota Tasikmalaya' => 'Kota Tasikmalaya',
              ])
              ->placeholder('Pilih kabupaten/kota'),
            Forms\Components\Select::make('keperluan')
              ->label('Keperluan')
              ->searchable()
              ->options([
                'Menyerahkan Surat/Berkas' => 'Menyerahkan Surat/Berkas',
                'Legalisir' => 'Legalisir',
                'Pengambilan Dokumen' => 'Pengambilan Dokumen',
                'Tanda Tangan Dokumen' => 'Tanda Tangan Dokumen',
              ])
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
          ->modalButton('Cetak')
          ->modalSubmitActionLabel('Cetak'),
      ])
      ->bulkActions([]);
  }
}
