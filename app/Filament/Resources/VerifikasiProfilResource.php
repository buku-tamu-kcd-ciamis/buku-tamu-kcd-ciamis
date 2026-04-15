<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VerifikasiProfilResource\Pages;
use App\Models\ProfileChangeRequest;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;

class VerifikasiProfilResource extends Resource
{
  protected static ?string $model = ProfileChangeRequest::class;

  protected static ?string $slug = 'verifikasi-profil';
  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
  protected static ?string $navigationLabel = 'Verifikasi Profil';
  protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
  protected static ?string $modelLabel = 'Pengajuan Profil';
  protected static ?string $pluralModelLabel = 'Verifikasi Profil';
  protected static ?int $navigationSort = 14;

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Filament::auth()->user();
    return $user && $user->hasRole('Super Admin');
  }

  public static function canViewAny(): bool
  {
    return static::shouldRegisterNavigation();
  }

  public static function canCreate(): bool
  {
    return false;
  }

  public static function getNavigationBadge(): ?string
  {
    $count = ProfileChangeRequest::where('status', ProfileChangeRequest::STATUS_PENDING)->count();
    return $count > 0 ? (string) $count : null;
  }

  public static function getNavigationBadgeColor(): string|array|null
  {
    return 'warning';
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('row_number')
          ->label('#')
          ->state(fn($rowLoop) => $rowLoop->iteration)
          ->width('50px'),
        Tables\Columns\TextColumn::make('user.name')
          ->label('Pengaju')
          ->searchable()
          ->icon('heroicon-o-user')
          ->weight('bold'),
        Tables\Columns\TextColumn::make('status')
          ->label('Status')
          ->badge()
          ->formatStateUsing(fn($state) => ProfileChangeRequest::STATUS_LABELS[$state] ?? ucfirst($state))
          ->color(fn($state) => ProfileChangeRequest::STATUS_COLORS[$state] ?? 'gray')
          ->icon(fn($state) => ProfileChangeRequest::STATUS_ICONS[$state] ?? null),
        Tables\Columns\TextColumn::make('changed_fields')
          ->label('Perubahan')
          ->state(function ($record) {
            $changed = $record->getChangedFields();
            $labels = ProfileChangeRequest::getFieldLabels();
            return collect($changed)->keys()->map(fn($k) => $labels[$k] ?? $k)->implode(', ');
          })
          ->wrap(),
        Tables\Columns\TextColumn::make('reviewer.name')
          ->label('Diproses Oleh')
          ->placeholder('-')
          ->icon('heroicon-o-shield-check'),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Tanggal Pengajuan')
          ->date('d/m/Y')
          ->tooltip(fn(ProfileChangeRequest $record): string => $record->created_at?->format('H:i') ?? '-')
          ->sortable(),
        Tables\Columns\TextColumn::make('reviewed_at')
          ->label('Tanggal Proses')
          ->date('d/m/Y')
          ->tooltip(fn(ProfileChangeRequest $record): string => $record->reviewed_at?->format('H:i') ?? '-')
          ->placeholder('-')
          ->sortable(),
      ])
      ->defaultSort('created_at', 'desc')
      ->recordUrl(fn(ProfileChangeRequest $record): string => static::getUrl('view', ['record' => $record->getKey()]))
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->label('Status')
          ->options(ProfileChangeRequest::STATUS_LABELS),
      ])
      ->recordActions([
        ViewAction::make()
          ->label('Detail')
          ->icon('heroicon-o-eye'),
      ])
      ->toolbarActions([])
      ->emptyStateHeading('Belum Ada Pengajuan')
      ->emptyStateDescription('Pengajuan perubahan profil dari Kepala Cabang Dinas akan muncul di sini.')
      ->emptyStateIcon('heroicon-o-clipboard-document-check');
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListVerifikasiProfil::route('/'),
      'view' => Pages\ViewVerifikasiProfil::route('/{record}'),
    ];
  }
}
