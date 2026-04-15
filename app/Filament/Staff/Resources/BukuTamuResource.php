<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Concerns\ChecksStaffPermission;
use App\Filament\Staff\Pages\ChatBooking;
use App\Models\BukuTamu;
use App\Services\BookingChatManager;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BukuTamuResource extends Resource
{
  use ChecksStaffPermission;

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
    return static::hasStaffPermission('buku_tamu');
  }

  public static function canViewAny(): bool
  {
    return static::hasStaffPermission('buku_tamu');
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

  public static function infolist(Schema $schema): Schema
  {
    return $schema->components([
      Section::make()
        ->columnSpanFull()
        ->components([
          Grid::make(3)
            ->components([
              Group::make([
                Infolists\Components\ImageEntry::make('foto_selfie')
                  ->label('Foto selfie')
                  ->disk('public'),
              ])->columnSpan(1),
              Group::make([
                Infolists\Components\TextEntry::make('nama_lengkap')
                  ->size('lg'),
                Grid::make(2)
                  ->components([
                    Infolists\Components\TextEntry::make('jenis_id')
                      ->icon('heroicon-o-identification'),
                    Infolists\Components\TextEntry::make('nik')
                      ->icon('heroicon-o-finger-print')
                      ->copyable(),
                    Infolists\Components\TextEntry::make('instansi')
                      ->icon('heroicon-o-building-office-2')
                      ->placeholder('-'),
                    Infolists\Components\TextEntry::make('jabatan')
                      ->icon('heroicon-o-briefcase')
                      ->placeholder('-'),
                    Infolists\Components\TextEntry::make('nomor_hp')
                      ->icon('heroicon-o-phone')
                      ->formatStateUsing(function ($state) {
                        if (!$state)
                          return '-';
                        $cleaned = preg_replace('/[^0-9]/', '', $state);
                        if (str_starts_with($cleaned, '0')) {
                          $cleaned = substr($cleaned, 1);
                        }
                        return '+62' . $cleaned;
                      })
                      ->copyable(),
                    Infolists\Components\TextEntry::make('email')
                      ->icon('heroicon-o-envelope')
                      ->copyable()
                      ->placeholder('-'),
                  ]),
              ])->columnSpan(2),
            ]),
        ]),
      Grid::make(['default' => 1, 'lg' => 2])
        ->columnSpanFull()
        ->components([
          Section::make('Status Kunjungan')
            ->icon('heroicon-o-signal')
            ->columns(2)
            ->components([
              Infolists\Components\TextEntry::make('status')
                ->badge()
                ->formatStateUsing(fn(string $state) => BukuTamu::STATUS_LABELS[$state] ?? ucfirst($state))
                ->color(fn(string $state) => match ($state) {
                  'menunggu' => 'warning',
                  'diproses' => 'info',
                  'selesai' => 'success',
                  'ditolak' => 'danger',
                  'dibatalkan' => 'gray',
                  default => 'secondary',
                }),
              Infolists\Components\TextEntry::make('nama_penerima')
                ->icon('heroicon-o-user')
                ->placeholder('Belum ada penerima'),
              Infolists\Components\TextEntry::make('catatan')
                ->placeholder('Tidak ada catatan'),
            ]),
          Section::make('Informasi Kunjungan')
            ->icon('heroicon-o-clipboard-document-list')
            ->columns(2)
            ->components([
              Infolists\Components\TextEntry::make('kabupaten_kota')
                ->icon('heroicon-o-map-pin'),
              Infolists\Components\TextEntry::make('staff_dituju')
                ->icon('heroicon-o-building-office'),
              Infolists\Components\TextEntry::make('keperluan')
                ->icon('heroicon-o-document-text'),
              Infolists\Components\TextEntry::make('created_at')
                ->icon('heroicon-o-clock')
                ->dateTime('d F Y, H:i:s'),
            ]),
        ]),
      Section::make('Dokumen')
        ->icon('heroicon-o-camera')
        ->columnSpanFull()
        ->columns(2)
        ->components([
          Infolists\Components\ImageEntry::make('foto_penerimaan')
            ->label('Foto penerimaan')
            ->disk('public'),
          Infolists\Components\ImageEntry::make('tanda_tangan')
            ->label('Tanda tangan')
            ->disk('public'),
        ]),
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
      ->recordActionsColumnLabel('')
      ->recordActions([
        ActionGroup::make([
          ViewAction::make()
            ->label('Lihat Detail')
            ->icon('heroicon-o-eye')
            ->color('gray'),
          Action::make('chat')
            ->label('Chat Piket')
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
          ->label(false)
          ->icon('heroicon-m-ellipsis-vertical')
          ->color('gray'),
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
      'index' => \App\Filament\Staff\Resources\BukuTamuResource\Pages\ListBukuTamu::route('/'),
      'view' => \App\Filament\Staff\Resources\BukuTamuResource\Pages\ViewBukuTamu::route('/{record}'),
    ];
  }

  public static function canCreate(): bool
  {
    return false;
  }
}
