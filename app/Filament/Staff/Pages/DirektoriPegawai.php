<?php

namespace App\Filament\Staff\Pages;

use App\Models\Pegawai;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class DirektoriPegawai extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Direktori Pegawai';
    protected static string|\UnitEnum|null $navigationGroup = 'Informasi';
    protected static ?string $title = 'Direktori Pegawai';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.staff.pages.direktori-pegawai';

    public function getTableRecordKey($record): string
    {
        return (string) $record->id;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Pegawai::query()->where('is_active', true)->orderBy('nama')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->limit(35)
                    ->tooltip(fn($record) => $record->jabatan),
                Tables\Columns\TextColumn::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->searchable()
                    ->toggleable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->unit_kerja),
                Tables\Columns\TextColumn::make('nomor_hp')
                    ->label('No. HP')
                    ->formatStateUsing(function ($state) {
                        if (!$state)
                            return '-';
                        return '+62' . $state;
                    })
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('availability_status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => Pegawai::AVAILABILITY_LABELS[$state] ?? 'Tersedia')
                    ->colors([
                        'success' => 'available',
                        'warning' => 'busy',
                        'danger' => 'out_of_office',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'available',
                        'heroicon-o-clock' => 'busy',
                        'heroicon-o-x-circle' => 'out_of_office',
                    ])
                    ->sortable(),
            ])
            ->defaultSort('nama')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25])
            ->filters([
                Tables\Filters\SelectFilter::make('availability_status')
                    ->label('Status Ketersediaan')
                    ->options(Pegawai::AVAILABILITY_LABELS),
                Tables\Filters\SelectFilter::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->options(fn() => Pegawai::active()->pluck('unit_kerja', 'unit_kerja')->filter()->unique()->toArray()),
            ]);
    }
}
