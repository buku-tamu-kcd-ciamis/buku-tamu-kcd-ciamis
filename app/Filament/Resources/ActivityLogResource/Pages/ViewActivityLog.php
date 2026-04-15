<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;
    protected string $view = 'filament.resources.activity-log-resource.pages.view-activity-log';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
            Section::make('Informasi Aktivitas')
                ->icon('heroicon-o-information-circle')
                ->columnSpanFull()
                ->columns(3)
                ->components([
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Waktu')
                        ->dateTime('d F Y, H:i:s')
                        ->icon('heroicon-o-clock'),
                    Infolists\Components\TextEntry::make('causer.name')
                        ->label('Dilakukan Oleh')
                        ->default('System')
                        ->icon('heroicon-o-user'),
                    Infolists\Components\TextEntry::make('causer.email')
                        ->label('Email User')
                        ->default('-')
                        ->icon('heroicon-o-envelope'),
                    Infolists\Components\TextEntry::make('log_name')
                        ->label('Modul')
                        ->badge()
                        ->formatStateUsing(fn($state) => ActivityLogResource::getLogNameLabel($state))
                        ->color(fn($state) => ActivityLogResource::getLogNameColor($state)),
                    Infolists\Components\TextEntry::make('event')
                        ->label('Jenis Aksi')
                        ->badge()
                        ->formatStateUsing(fn(?string $state) => match ($state) {
                            'created' => 'Dibuat',
                            'updated' => 'Diubah',
                            'deleted' => 'Dihapus',
                            default => $state ? ucfirst($state) : '-',
                        })
                        ->icon(fn(?string $state): ?string => $state ? ActivityLogResource::getEventIcon($state) : null)
                        ->color(fn(?string $state): string => match ($state) {
                            'created' => 'success',
                            'updated' => 'warning',
                            'deleted' => 'danger',
                            default => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('subject_type')
                        ->label('Tipe Data')
                        ->formatStateUsing(fn(?string $state) => $state ? class_basename($state) : '-'),
                    Infolists\Components\TextEntry::make('description')
                        ->label('Deskripsi Aktivitas')
                        ->columnSpanFull()
                        ->icon('heroicon-o-document-text'),
                ]),

            // Section: Data Changes (before/after diff)
            Section::make('Perubahan Data')
                ->icon('heroicon-o-arrows-right-left')
                ->columnSpanFull()
                ->visible(fn($record) => $record->properties->has('attributes') || $record->properties->has('old'))
                ->components([
                    Infolists\Components\ViewEntry::make('properties')
                        ->label('')
                        ->view('filament.infolists.components.activity-properties'),
                ])
                ->collapsible(),

            // Section: Additional Properties (for print/auth logs that have custom properties)
            Section::make('Detail Tambahan')
                ->icon('heroicon-o-clipboard-document-list')
                ->columnSpanFull()
                ->visible(fn($record) => $record->properties->isNotEmpty()
                    && !$record->properties->has('attributes')
                    && !$record->properties->has('old'))
                ->components([
                    Infolists\Components\ViewEntry::make('properties')
                        ->label('')
                        ->view('filament.infolists.components.activity-detail'),
                ])
                ->collapsible(),
        ]);
    }

    public function getTitle(): string
    {
        return 'Detail Log Aktivitas';
    }
}
