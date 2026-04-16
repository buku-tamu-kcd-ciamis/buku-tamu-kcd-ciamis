<?php

namespace App\Filament\Piket\Resources;

use App\Filament\Piket\Concerns\ChecksPiketPermission;
use App\Filament\Piket\Resources\KunjunganResource\Pages;
use App\Filament\Piket\Pages\ChatBooking;
use App\Models\BukuTamu;
use App\Services\BookingChatManager;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class KunjunganResource extends Resource
{
  use ChecksPiketPermission;

  protected static ?string $model = BukuTamu::class;

  protected static ?string $slug = 'buku-tamu';
  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
  protected static ?string $navigationLabel = 'Buku Tamu';
  protected static string|\UnitEnum|null $navigationGroup = 'Layanan Tamu';
  protected static ?string $modelLabel = 'Buku Tamu';
  protected static ?string $pluralModelLabel = 'Data Buku Tamu';
  protected static ?int $navigationSort = 1;

  public static function shouldRegisterNavigation(): bool
  {
    return static::hasPiketPermission('buku_tamu');
  }

  public static function canViewAny(): bool
  {
    return static::hasPiketPermission('buku_tamu');
  }

  public static function form(Schema $schema): Schema
  {
    return $schema->components([
      Forms\Components\Select::make('status')
        ->options(BukuTamu::STATUS_LABELS)
        ->required(),
      Forms\Components\Textarea::make('catatan')
        ->rows(3),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->query(
        BukuTamu::query()
          ->where(function ($q) {
            $q->where('keperluan', 'not like', '%berkas%')
              ->where('keperluan', 'not like', '%surat%')
              ->where('keperluan', 'not like', '%dokumen%')
              ->where('keperluan', 'not like', '%legalisir%');
          })
      )
      ->columns([
        Tables\Columns\ViewColumn::make('foto_selfie')
          ->label('Foto')
          ->view('filament.tables.columns.avatar-column'),
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
        Tables\Columns\TextColumn::make('staff_dituju')
          ->label('Staff Yang Dituju')
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
          ->color('gray')
          ->tooltip(fn($record) => $record->created_at->format('d/m/Y H:i'))
          ->sortable()
          ->verticallyAlignCenter(),
      ])
      ->defaultSort('created_at', 'desc')
      ->defaultPaginationPageOption(10)
      ->paginationPageOptions([5, 10, 20, 50, 100])
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->options(BukuTamu::STATUS_LABELS),
        Tables\Filters\Filter::make('tanggal')
          ->schema([
            Forms\Components\DatePicker::make('tanggal'),
          ])
          ->query(function ($query, array $data) {
            return $query->when($data['tanggal'], fn($q, $date) => $q->whereDate('created_at', $date));
          }),
      ])
      ->recordActionsAlignment('center')
      ->recordActionsColumnLabel('')
      ->recordActions([
        ActionGroup::make([
          Action::make('status_menunggu')
            ->label('Tandai Menunggu')
            ->icon('heroicon-o-clock')
            ->color('warning')
            ->visible(fn(BukuTamu $record): bool => $record->status !== BukuTamu::STATUS_SELESAI && $record->status !== BukuTamu::STATUS_MENUNGGU)
            ->action(fn(BukuTamu $record) => static::updateGuestStatus($record, BukuTamu::STATUS_MENUNGGU)),
          Action::make('status_selesai')
            ->label('Tandai Selesai')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn(BukuTamu $record): bool => $record->status !== BukuTamu::STATUS_SELESAI)
            ->action(fn(BukuTamu $record) => static::updateGuestStatus($record, BukuTamu::STATUS_SELESAI)),
          Action::make('status_ditolak')
            ->label('Tandai Ditolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn(BukuTamu $record): bool => $record->status !== BukuTamu::STATUS_SELESAI && $record->status !== BukuTamu::STATUS_DITOLAK)
            ->action(fn(BukuTamu $record) => static::updateGuestStatus($record, BukuTamu::STATUS_DITOLAK)),
          Action::make('status_dibatalkan')
            ->label('Tandai Dibatalkan')
            ->icon('heroicon-o-no-symbol')
            ->color('gray')
            ->visible(fn(BukuTamu $record): bool => $record->status !== BukuTamu::STATUS_SELESAI && $record->status !== BukuTamu::STATUS_DIBATALKAN)
            ->action(fn(BukuTamu $record) => static::updateGuestStatus($record, BukuTamu::STATUS_DIBATALKAN)),
          Action::make('print')
            ->label('Print')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->visible(fn(BukuTamu $record): bool => $record->status === BukuTamu::STATUS_SELESAI)
            ->url(fn(BukuTamu $record): string => route('buku-tamu.print', ['id' => $record->id]))
            ->openUrlInNewTab(true),
          Action::make('chat')
            ->label('Chat Staff')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('primary')
            ->visible(fn(BukuTamu $record): bool => $record->status !== BukuTamu::STATUS_SELESAI)
            ->url(function (BukuTamu $record): string {
              $chat = $record->bookingChats()->first();

              if (!$chat) {
                $chat = app(BookingChatManager::class)->bootstrapForBooking($record, \Filament\Facades\Filament::auth()->user())->first();
              }

              if (!$chat) {
                return ChatBooking::getUrl() . '?booking=' . $record->id;
              }

              return ChatBooking::getUrl() . '?chat=' . $chat->id;
            })
            ->openUrlInNewTab(false),
          Action::make('detail')
            ->label('Lihat Detail')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->visible(fn(BukuTamu $record): bool => $record->status !== BukuTamu::STATUS_SELESAI)
            ->url(fn(BukuTamu $record): string => Pages\ViewKunjungan::getUrl(['record' => $record]))
            ->openUrlInNewTab(false),
        ])
          ->label(false)
          ->icon('heroicon-m-ellipsis-vertical')
          ->color('gray'),
      ])
      ->headerActions([])
      ->toolbarActions([
        BulkActionGroup::make([
          BulkAction::make('bulk_set_selesai')
            ->label('Selesaikan Terpilih')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Selesaikan Kunjungan Terpilih')
            ->modalDescription('Hanya data berstatus Menunggu atau Diproses yang akan diselesaikan. Data yang sudah Selesai tidak akan diproses ulang.')
            ->action(function ($records): void {
              $eligibleStatuses = [BukuTamu::STATUS_MENUNGGU, BukuTamu::STATUS_DIPROSES];
              $completedBy = (string) (Auth::user()?->name ?? 'Petugas Piket');
              $completedAt = now();

              $updatedCount = 0;
              $skippedCount = 0;

              $records->each(function (BukuTamu $record) use (&$updatedCount, &$skippedCount, $eligibleStatuses, $completedBy, $completedAt): void {
                if (!in_array($record->status, $eligibleStatuses, true)) {
                  $skippedCount++;

                  return;
                }

                $payload = [
                  'status' => BukuTamu::STATUS_SELESAI,
                  'diselesaikan_oleh' => $completedBy,
                  'diselesaikan_pada' => $completedAt,
                ];

                if (blank($record->nama_penerima)) {
                  $payload['nama_penerima'] = $completedBy;
                }

                $record->update($payload);
                $updatedCount++;
              });

              if ($updatedCount > 0) {
                Notification::make()
                  ->title('Bulk penyelesaian berhasil')
                  ->body("{$updatedCount} data berhasil ditandai Selesai.")
                  ->success()
                  ->send();
              }

              if ($skippedCount > 0) {
                Notification::make()
                  ->title('Sebagian data dilewati')
                  ->body("{$skippedCount} data tidak diproses karena statusnya bukan Menunggu/Diproses atau sudah Selesai.")
                  ->warning()
                  ->send();
              }

              if ($updatedCount === 0 && $skippedCount === 0) {
                Notification::make()
                  ->title('Tidak ada data diproses')
                  ->warning()
                  ->send();
              }
            })
            ->deselectRecordsAfterCompletion(),
          BulkAction::make('bulk_print')
            ->label('Print')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->url(route('buku-tamu.print-bulk'))
            ->livewireClickHandlerEnabled(false)
            ->accessSelectedRecords(false)
            ->openUrlInNewTab(true)
            ->extraAttributes([
              'style' => 'padding: 10px 16px !important;',
              'x-bind:href' => "`\${window.location.origin}/print/buku-tamu-bulk?ids=\${[...selectedRecords].join(',')}`",
            ]),
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListKunjungan::route('/'),
      'view' => Pages\ViewKunjungan::route('/{record}'),
    ];
  }

  public static function canCreate(): bool
  {
    return false;
  }

  private static function updateGuestStatus(BukuTamu $record, string $status): void
  {
    if (!array_key_exists($status, BukuTamu::STATUS_LABELS)) {
      Notification::make()
        ->title('Status tidak valid')
        ->danger()
        ->send();

      return;
    }

    if ($record->status === BukuTamu::STATUS_SELESAI && $status !== BukuTamu::STATUS_SELESAI) {
      Notification::make()
        ->title('Status sudah final')
        ->body('Data berstatus Selesai tidak bisa dikembalikan ke status lain. Gunakan Print untuk arsip.')
        ->warning()
        ->send();

      return;
    }

    $payload = ['status' => $status];

    if ($status === BukuTamu::STATUS_SELESAI) {
      $completedBy = (string) (Auth::user()?->name ?? 'Petugas Piket');

      $payload['diselesaikan_oleh'] = $completedBy;
      $payload['diselesaikan_pada'] = now();

      if (blank($record->nama_penerima)) {
        $payload['nama_penerima'] = $completedBy;
      }
    } elseif ($record->status !== BukuTamu::STATUS_SELESAI) {
      $payload['diselesaikan_oleh'] = null;
      $payload['diselesaikan_pada'] = null;
    }

    $record->update($payload);

    Notification::make()
      ->title('Status diperbarui')
      ->body('Status tamu diubah menjadi ' . (BukuTamu::STATUS_LABELS[$status] ?? $status) . '.')
      ->success()
      ->send();
  }
}
