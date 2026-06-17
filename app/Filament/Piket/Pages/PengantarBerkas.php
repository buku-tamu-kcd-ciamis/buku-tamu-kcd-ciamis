<?php

namespace App\Filament\Piket\Pages;

use App\Filament\Piket\Pages\ChatBooking;
use App\Models\BukuTamu;
use App\Models\DropdownOption;
use App\Services\BookingChatManager;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use App\Models\User;

class PengantarBerkas extends Page implements HasTable
{
  use InteractsWithTable;

  public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
  {
    return null;
  }

  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel = 'Pengantar Berkas';
  protected static string|\UnitEnum|null $navigationGroup = 'Layanan Tamu';
  protected static ?string $title = 'Daftar Pengantar Berkas';
  protected static ?int $navigationSort = 2;
  protected string $view = 'filament.piket.pages.pengantar-berkas';

  public static function shouldRegisterNavigation(): bool
  {
    return true;
  }

  public static function canAccess(): bool
  {
    return true;
  }

  public ?string $activeTab = 'hari_ini';

  protected function hasCustomDateFilters(): bool
  {
    return !empty($this->tableFilters['tanggal']['dari'] ?? null) || !empty($this->tableFilters['tanggal']['sampai'] ?? null);
  }

  public function getTabBadge(string $tab): int
  {
    $query = BukuTamu::query()
      ->whereNotNull('foto_penerimaan')
      ->where('foto_penerimaan', '!=', '');

    return match ($tab) {
      'hari_ini' => $query->whereDate('created_at', now()->toDateString())->count(),
      'kemarin' => $query->whereDate('created_at', now()->subDay()->toDateString())->count(),
      'minggu_ini' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
      'bulan_ini' => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
      'semua' => $query->count(),
      default => 0,
    };
  }

  public function table(Table $table): Table
  {
    return $table
      ->query(
        BukuTamu::query()
          ->whereNotNull('foto_penerimaan')
          ->where('foto_penerimaan', '!=', '')
          ->when($this->activeTab === 'hari_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereDate('created_at', now()->toDateString()))
          ->when($this->activeTab === 'kemarin' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereDate('created_at', now()->subDay()->toDateString()))
          ->when($this->activeTab === 'minggu_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
          ->when($this->activeTab === 'bulan_ini' && !$this->hasCustomDateFilters(), fn ($query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]))
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
          ->tooltip(fn($record) => $record->created_at->format('d/m/Y H:i'))
          ->sortable(),
      ])
      ->defaultSort('created_at', 'desc')
      ->defaultPaginationPageOption(10)
      ->paginationPageOptions([10])
      ->recordActionsColumnLabel('')
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->options(BukuTamu::STATUS_LABELS),
        Tables\Filters\SelectFilter::make('keperluan')
          ->label('Keperluan')
          ->options(fn(): array => BukuTamu::query()
            ->whereNotNull('foto_penerimaan')
            ->where('foto_penerimaan', '!=', '')
            ->select('keperluan')
            ->whereNotNull('keperluan')
            ->where('keperluan', '!=', '')
            ->orderBy('keperluan')
            ->distinct()
            ->pluck('keperluan', 'keperluan')
            ->all())
          ->searchable(),
        Tables\Filters\SelectFilter::make('staff_dituju')
          ->label('Staff Yang Dituju')
          ->options(fn(): array => BukuTamu::query()
            ->whereNotNull('foto_penerimaan')
            ->where('foto_penerimaan', '!=', '')
            ->select('staff_dituju')
            ->whereNotNull('staff_dituju')
            ->where('staff_dituju', '!=', '')
            ->orderBy('staff_dituju')
            ->distinct()
            ->pluck('staff_dituju', 'staff_dituju')
            ->all())
          ->searchable(),
        Tables\Filters\Filter::make('tanggal')
          ->schema([
            Forms\Components\DatePicker::make('dari')
              ->label('Dari Tanggal')
              ->native(false)
              ->displayFormat('d/m/Y')
              ->closeOnDateSelection(),
            Forms\Components\DatePicker::make('sampai')
              ->label('Sampai Tanggal')
              ->native(false)
              ->displayFormat('d/m/Y')
              ->closeOnDateSelection(),
          ])
          ->query(function ($query, array $data) {
            return $query
              ->when($data['dari'], fn($query, $date) => $query->whereDate('created_at', '>=', $date))
              ->when($data['sampai'], fn($query, $date) => $query->whereDate('created_at', '<=', $date));
          })
          ->indicateUsing(function (array $data): array {
            $indicators = [];
            if ($data['dari'] ?? null) {
              $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['dari'])->translatedFormat('d M Y');
            }
            if ($data['sampai'] ?? null) {
              $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai'])->translatedFormat('d M Y');
            }
            return $indicators;
          })
          ->columns(2)
          ->columnSpan(2),
      ])
      ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
      ->filtersFormColumns(5)
      ->recordActions([
        ActionGroup::make([
          Action::make('status_menunggu')
            ->label('Tandai Menunggu')
            ->icon('heroicon-o-clock')
            ->color('warning')
            ->visible(fn(BukuTamu $record): bool => $record->status !== BukuTamu::STATUS_SELESAI && $record->status !== BukuTamu::STATUS_MENUNGGU)
            ->action(fn(BukuTamu $record) => $this->updateStatus($record, BukuTamu::STATUS_MENUNGGU)),
          Action::make('status_selesai')
            ->label('Tandai Selesai')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn(BukuTamu $record): bool => $record->status !== BukuTamu::STATUS_SELESAI)
            ->action(fn(BukuTamu $record) => $this->updateStatus($record, BukuTamu::STATUS_SELESAI)),
          Action::make('status_ditolak')
            ->label('Tandai Ditolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn(BukuTamu $record): bool => $record->status !== BukuTamu::STATUS_SELESAI && $record->status !== BukuTamu::STATUS_DITOLAK)
            ->action(fn(BukuTamu $record) => $this->updateStatus($record, BukuTamu::STATUS_DITOLAK)),
          Action::make('status_dibatalkan')
            ->label('Tandai Dibatalkan')
            ->icon('heroicon-o-no-symbol')
            ->color('gray')
            ->visible(fn(BukuTamu $record): bool => $record->status !== BukuTamu::STATUS_SELESAI && $record->status !== BukuTamu::STATUS_DIBATALKAN)
            ->action(fn(BukuTamu $record) => $this->updateStatus($record, BukuTamu::STATUS_DIBATALKAN)),
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
                $chat = app(BookingChatManager::class)->bootstrapForBooking($record, Filament::auth()->user())->first();
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
            ->modalContent(fn(BukuTamu $record) => view('filament.piket.pages.detail-pengantar-berkas', ['record' => $record]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup'),
        ])
          ->label(false)
          ->icon('heroicon-m-ellipsis-vertical')
          ->color('gray'),
      ])
      ->headerActions([])
      ->toolbarActions([
        BulkActionGroup::make([
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

  private function updateStatus(BukuTamu $record, string $status): void
  {
    if (!array_key_exists($status, BukuTamu::STATUS_LABELS)) {
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

    $record->update(['status' => $status]);

    Notification::make()
      ->title('Status diperbarui')
      ->body('Status tamu diubah menjadi ' . (BukuTamu::STATUS_LABELS[$status] ?? $status) . '.')
      ->success()
      ->send();
  }
}
