<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Spatie\Activitylog\Models\Activity;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Detail Log Aktivitas')
                ->schema([
                    Infolists\Components\Grid::make(2)
                        ->schema([
                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Waktu')
                                ->dateTime('d F Y, H:i:s')
                                ->icon('heroicon-o-clock'),
                            Infolists\Components\TextEntry::make('causer.name')
                                ->label('Dilakukan Oleh')
                                ->default('System')
                                ->icon('heroicon-o-user'),
                            Infolists\Components\TextEntry::make('log_name')
                                ->label('Kategori')
                                ->badge()
                                ->color('success'),
                            Infolists\Components\TextEntry::make('description')
                                ->label('Deskripsi Aktivitas'),
                            Infolists\Components\TextEntry::make('event')
                                ->label('Jenis Event')
                                ->badge()
                                ->formatStateUsing(fn($state) => match ($state) {
                                    'created' => 'Dibuat',
                                    'updated' => 'Diubah',
                                    'deleted' => 'Dihapus',
                                    default => ucfirst($state),
                                })
                                ->color(fn(string $state): string => match ($state) {
                                    'created' => 'success',
                                    'updated' => 'warning',
                                    'deleted' => 'danger',
                                    default => 'gray',
                                }),
                            Infolists\Components\TextEntry::make('subject_type')
                                ->label('Tipe Model')
                                ->formatStateUsing(fn($state) => class_basename($state)),
                            Infolists\Components\TextEntry::make('subject_id')
                                ->label('ID Record'),
                        ]),
                ]),

            Infolists\Components\Section::make('Perubahan Data')
                ->visible(fn($record) => $record->properties->has('attributes') || $record->properties->has('old'))
                ->schema([
                    Infolists\Components\ViewEntry::make('properties')
                        ->label('')
                        ->view('filament.infolists.components.activity-properties'),
                ]),
        ]);
    }
}
