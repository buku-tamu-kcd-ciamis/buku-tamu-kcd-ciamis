<?php

namespace App\Filament\Staff\Pages;

use App\Models\BukuTamu;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RiwayatKunjungan extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Riwayat Kunjungan';
    protected static string|\UnitEnum|null $navigationGroup = 'Informasi';
    protected static ?string $title = 'Riwayat Kunjungan Saya';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.staff.pages.riwayat-kunjungan';

    public function getTableRecordKey($record): string
    {
        return (string) $record->id;
    }

    public function table(Table $table): Table
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $staffNama = $pegawai?->nama ?? $user->name;

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
            ]);
    }
}
