<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VerifikasiProfilResource\Pages;
use App\Models\ProfileChangeRequest;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

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
    $user = Auth::user();
    return $user && $user->hasRole('Super Admin');
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
          ->dateTime('d/m/Y H:i')
          ->sortable(),
        Tables\Columns\TextColumn::make('reviewed_at')
          ->label('Tanggal Proses')
          ->dateTime('d/m/Y H:i')
          ->placeholder('-')
          ->sortable(),
      ])
      ->defaultSort('created_at', 'desc')
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->label('Status')
          ->options(ProfileChangeRequest::STATUS_LABELS),
      ])
      ->recordActions([])
      ->toolbarActions([])
      ->emptyStateHeading('Belum Ada Pengajuan')
      ->emptyStateDescription('Pengajuan perubahan profil dari Kepala Cabang Dinas akan muncul di sini.')
      ->emptyStateIcon('heroicon-o-clipboard-document-check');
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListVerifikasiProfil::route('/'),
    ];
  }
}
