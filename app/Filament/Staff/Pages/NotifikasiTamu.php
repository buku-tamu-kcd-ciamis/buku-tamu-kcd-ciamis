<?php

namespace App\Filament\Staff\Pages;

use App\Filament\Staff\Concerns\ChecksStaffPermission;
use App\Models\StaffNotification;
use App\Services\BookingChatManager;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class NotifikasiTamu extends Page implements HasTable
{
    use InteractsWithTable;
    use ChecksStaffPermission;

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Notifikasi Tamu';
    protected static ?string $title = 'Notifikasi Tamu';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.staff.pages.notifikasi-tamu';

    public static function shouldRegisterNavigation(): bool
    {
        return static::hasStaffPermission('buku_tamu');
    }

    public static function canAccess(): bool
    {
        return static::hasStaffPermission('buku_tamu');
    }

    // Polling interval: auto-refresh every 5 seconds
    protected static string $pollingInterval = '5s';

    public function getTableRecordKey($record): string
    {
        return (string) $record->id;
    }

    /**
     * Get unread notification count for badge
     */
    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        if (!$user)
            return null;

        $count = StaffNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StaffNotification::query()
                    ->where('user_id', Auth::id())
                    ->with('bukuTamu')
                    ->latest()
            )
            ->poll('5s')
            ->columns([
                Tables\Columns\IconColumn::make('is_read')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('primary')
                    ->width('40px'),
                Tables\Columns\TextColumn::make('bukuTamu.nama_lengkap')
                    ->label('Nama Tamu')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bukuTamu.instansi')
                    ->label('Instansi')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bukuTamu.keperluan')
                    ->label('Keperluan')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->bukuTamu?->keperluan),
                Tables\Columns\TextColumn::make('bukuTamu.nomor_hp')
                    ->label('No. HP')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Pesan')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('response')
                    ->label('Status')
                    ->colors([
                        'warning' => fn($state) => $state === null,
                        'success' => 'diterima',
                        'danger' => 'ditolak',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'diterima' => 'Diterima',
                        'ditolak' => 'Ditolak',
                        default => 'Menunggu',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since()
                    ->tooltip(fn($record) => $record->created_at?->format('d/m/Y H:i:s'))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->actions([
                ActionGroup::make([
                    Action::make('terima')
                        ->label('Terima Tamu')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Terima Tamu')
                        ->modalDescription(fn($record) => "Apakah Anda yakin ingin menerima tamu '{$record->bukuTamu?->nama_lengkap}'?")
                        ->action(function ($record) {
                            $record->respondAndSyncVisitStatus(StaffNotification::RESPONSE_DITERIMA);

                            Notification::make()
                                ->title('Tamu diterima')
                                ->body("Anda telah menerima tamu {$record->bukuTamu?->nama_lengkap}")
                                ->success()
                                ->send();
                        })
                        ->visible(fn($record) => $record->response === null),
                    Action::make('tolak')
                        ->label('Tolak Tamu')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Tamu')
                        ->modalDescription(fn($record) => "Apakah Anda yakin ingin menolak tamu '{$record->bukuTamu?->nama_lengkap}'?")
                        ->action(function ($record) {
                            $record->respondAndSyncVisitStatus(StaffNotification::RESPONSE_DITOLAK);

                            Notification::make()
                                ->title('Tamu ditolak')
                                ->body("Anda telah menolak tamu {$record->bukuTamu?->nama_lengkap}")
                                ->warning()
                                ->send();
                        })
                        ->visible(fn($record) => $record->response === null),
                    Action::make('tandai_dibaca')
                        ->label('Tandai Dibaca')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->action(function ($record) {
                            $record->markAsRead();
                        })
                        ->visible(fn($record) => !$record->is_read),
                    Action::make('chat')
                        ->label('Buka Chat')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('primary')
                        ->url(function ($record): string {
                            $booking = $record->bukuTamu;

                            if (!$booking) {
                                return ChatBooking::getUrl();
                            }

                            $chat = app(BookingChatManager::class)
                                ->getOrCreateForBookingAndStaff($booking, Auth::user(), Auth::user());

                            return ChatBooking::getUrl() . '?chat=' . $chat->id;
                        })
                        ->openUrlInNewTab(false),
                ])
                    ->label(false)
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->bulkActions([
                BulkAction::make('tandai_semua_dibaca')
                    ->label('Tandai Semua Dibaca')
                    ->icon('heroicon-o-check')
                    ->action(function ($records) {
                        $records->each(fn($record) => $record->markAsRead());
                        Notification::make()
                            ->title('Semua notifikasi ditandai dibaca')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}
