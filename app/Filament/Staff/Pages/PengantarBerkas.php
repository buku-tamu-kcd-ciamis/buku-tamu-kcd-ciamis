<?php

namespace App\Filament\Staff\Pages;

use App\Filament\Staff\Concerns\ChecksStaffPermission;
use App\Models\BukuTamu;
use App\Models\User;
use App\Services\BookingChatManager;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Page;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PengantarBerkas extends Page implements HasTable
{
    use InteractsWithTable;
    use ChecksStaffPermission;

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Pengantar Berkas';
    protected static string|\UnitEnum|null $navigationGroup = 'Layanan Tamu';
    protected static ?string $title = 'Daftar Pengantar Berkas';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.staff.pages.pengantar-berkas';

    public static function shouldRegisterNavigation(): bool
    {
        return static::hasStaffPermission('pengantar_berkas');
    }

    public static function canAccess(): bool
    {
        return static::hasStaffPermission('pengantar_berkas');
    }

    public function table(Table $table): Table
    {
        $staffNama = $this->getStaffNama();

        return $table
            ->query(
                BukuTamu::query()
                    ->where('staff_dituju', $staffNama)
                    ->whereNotNull('foto_penerimaan')
                    ->where('foto_penerimaan', '!=', '')
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
                Tables\Columns\TextColumn::make('staff_dituju')
                    ->label('Staff Yang Dituju'),
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
                    ->tooltip(fn(BukuTamu $record) => $record->created_at->format('d/m/Y H:i'))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10])
            ->recordActionsColumnLabel('')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(BukuTamu::STATUS_LABELS),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('chat')
                        ->label('Chat Piket')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('primary')
                        ->url(function (BukuTamu $record): string {
                            $chat = $record->bookingChats()->first();

                            if (! $chat) {
                                $chat = app(BookingChatManager::class)->bootstrapForBooking($record, Auth::user())->first();
                            }

                            if (! $chat) {
                                return ChatBooking::getUrl() . '?booking=' . $record->id;
                            }

                            return ChatBooking::getUrl() . '?chat=' . $chat->id;
                        })
                        ->openUrlInNewTab(false),
                    Action::make('detail')
                        ->label('Lihat Detail')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->modalContent(fn(BukuTamu $record) => view('filament.piket.pages.detail-pengantar-berkas', ['record' => $record]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup'),
                ])
                    ->label(false)
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->headerActions([])
            ->toolbarActions([]);
    }

    private function getStaffNama(): string
    {
        /** @var User|null $user */
        $user = Auth::user();

        $nama = trim((string) ($user?->pegawai?->nama ?? ''));

        return $nama;
    }
}
