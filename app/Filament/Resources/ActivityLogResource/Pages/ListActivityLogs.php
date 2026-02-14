<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->form([
                    Select::make('user_id')
                        ->label('Filter User')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Semua User'),

                    Select::make('log_name')
                        ->label('Modul Aktivitas')
                        ->options(ActivityLogResource::getLogNameLabels())
                        ->placeholder('Semua Modul'),

                    Select::make('event')
                        ->label('Tipe Event')
                        ->options([
                            'created' => 'Created',
                            'updated' => 'Updated',
                            'deleted' => 'Deleted',
                            'login' => 'Login',
                            'logout' => 'Logout',
                            'print' => 'Print',
                        ])
                        ->placeholder('Semua Event'),

                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    Select::make('limit')
                        ->label('Jumlah Data')
                        ->options([
                            10 => '10 Log Terakhir',
                            25 => '25 Log Terakhir',
                            50 => '50 Log Terakhir',
                            100 => '100 Log Terakhir',
                            250 => '250 Log Terakhir',
                            500 => '500 Log Terakhir',
                        ])
                        ->default(50)
                        ->required(),
                ])
                ->url(function (array $data) {
                    $queryParams = array_filter([
                        'user_id' => $data['user_id'] ?? null,
                        'log_name' => $data['log_name'] ?? null,
                        'event' => $data['event'] ?? null,
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'limit' => $data['limit'] ?? 50,
                    ]);

                    return route('activity-logs.print', $queryParams);
                })
                ->openUrlInNewTab()
                ->modalHeading('Print Log Aktivitas')
                ->modalSubmitActionLabel('Print')
                ->modalWidth('lg'),
        ];
    }
}
