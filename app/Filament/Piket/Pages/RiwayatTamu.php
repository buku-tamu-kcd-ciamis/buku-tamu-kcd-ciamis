<?php

namespace App\Filament\Piket\Pages;

use App\Models\BukuTamu;
use App\Models\DropdownOption;
use App\Services\BookingChatManager;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Models\User;

class RiwayatTamu extends Page implements HasTable
{
  use InteractsWithTable;

  public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
  {
    return null;
  }

  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';
  protected static ?string $navigationLabel = 'Riwayat Pengunjung';
  protected static string|\UnitEnum|null $navigationGroup = 'Layanan Tamu';
  protected static ?string $title = 'Riwayat Pengunjung';
  protected static ?int $navigationSort = 3;
  protected string $view = 'filament.piket.pages.riwayat-tamu';

  public static function shouldRegisterNavigation(): bool
  {
    return true;
  }

  public static function canAccess(): bool
  {
    return true;
  }

  public function getTableRecordKey($record): string
  {
    return (string) $record->id;
  }

  public function table(Table $table): Table
  {
    return $table
      ->query(
        BukuTamu::query()
          ->select([
            'buku_tamu.*',
            DB::raw('(SELECT COUNT(*) FROM buku_tamu AS bt WHERE bt.nik = buku_tamu.nik) as total_kunjungan'),
            DB::raw('(SELECT MAX(created_at) FROM buku_tamu AS bt WHERE bt.nik = buku_tamu.nik) as kunjungan_terakhir')
          ])
          ->whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
              ->from('buku_tamu')
              ->groupBy('nik');
          })
      )
      ->columns([
        Tables\Columns\ViewColumn::make('foto_selfie')
          ->label('Foto')
          ->view('filament.tables.columns.avatar-column'),
        Tables\Columns\TextColumn::make('nama_lengkap')
          ->label('Nama')
          ->searchable()
          ->weight('bold'),
        Tables\Columns\TextColumn::make('nik')
          ->label('NIK')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('instansi')
          ->searchable()
          ->toggleable(),
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
        Tables\Columns\TextColumn::make('total_kunjungan')
          ->label('Total Kunjungan')
          ->badge()
          ->color('success')
          ->alignCenter()
          ->sortable(),
        Tables\Columns\TextColumn::make('kunjungan_terakhir')
          ->label('Terakhir Berkunjung')
          ->since()
          ->color('gray')
          ->tooltip(fn($record) => \Carbon\Carbon::parse($record->kunjungan_terakhir)->format('d/m/Y H:i'))
          ->sortable(),
      ])
      ->defaultSort('total_kunjungan', 'desc')
      ->defaultPaginationPageOption(10)
      ->paginationPageOptions([10])
      ->recordActionsColumnLabel('')
      ->recordActions([
        ActionGroup::make([
          Action::make('chat')
            ->label('Chat Staff')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('primary')
            ->url(function (BukuTamu $record): string {
              $chat = $record->bookingChats()->first();

              if (!$chat) {
                $chat = app(BookingChatManager::class)->bootstrapForBooking($record, Auth::user())->first();
              }

              if (!$chat) {
                return ChatBooking::getUrl() . '?booking=' . $record->id;
              }

              return ChatBooking::getUrl() . '?chat=' . $chat->id;
            })
            ->openUrlInNewTab(false),
          Action::make('view')
            ->label('Lihat Detail')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->url(fn(BukuTamu $record) => ViewRiwayatTamu::getUrl(['nik' => $record->nik])),
        ])
          ->label(false)
          ->icon('heroicon-m-ellipsis-vertical')
          ->color('gray'),
      ])
      ->headerActions([]);
  }

  public function getFooter(): ?View
  {
    return view('filament.piket.pages.riwayat-tamu-footer');
  }
}
