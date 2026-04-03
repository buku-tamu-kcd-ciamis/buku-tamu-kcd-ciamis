<?php

namespace App\Filament\Piket\Resources;

use App\Filament\Piket\Concerns\ChecksPiketPermission;
use App\Filament\Piket\Resources\KunjunganResource\Pages;
use App\Filament\Piket\Pages\ChatBooking;
use App\Models\BukuTamu;
use App\Services\BookingChatManager;
use Filament\Actions\Action;
use Filament\Forms;
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
      ->paginationPageOptions([10])
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
      ->recordActionsColumnLabel('Aksi')
      ->recordActions([
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
      ])
      ->headerActions([])
      ->toolbarActions([]);
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
}
