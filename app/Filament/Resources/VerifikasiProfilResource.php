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
  protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
  protected static ?string $navigationLabel = 'Verifikasi Profil';
  protected static ?string $navigationGroup = 'Pengaturan';
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
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\Action::make('approve')
            ->label('Setujui')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn($record) => $record->isPending())
            ->requiresConfirmation()
            ->modalHeading('Setujui Perubahan Profil')
            ->modalDescription(function ($record) {
              $changed = $record->getChangedFields();
              $labels = ProfileChangeRequest::getFieldLabels();
              $lines = [];
              foreach ($changed as $field => $changes) {
                $label = $labels[$field] ?? $field;
                $lines[] = "<b>{$label}</b>: <span class='line-through text-gray-400'>" .
                  ($changes['old'] ?: '(kosong)') . "</span> → <b>" .
                  $changes['new'] . "</b>";
              }
              return new \Illuminate\Support\HtmlString(
                'Apakah Anda yakin ingin menyetujui perubahan berikut?<br><br>' .
                  implode('<br>', $lines)
              );
            })
            ->modalSubmitActionLabel('Ya, Setujui')
            ->action(function ($record) {
              /** @var User $user */
              $user = Auth::user();

              // Terapkan perubahan ke pengaturan KCD
              $settings = \App\Models\PengaturanKcd::getSettings();
              $settings->update($record->new_data);

              // Update status
              $record->update([
                'status' => ProfileChangeRequest::STATUS_APPROVED,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
              ]);

              activity()
                ->causedBy($user)
                ->performedOn($record)
                ->useLog('profil_change')
                ->event('approved')
                ->withProperties([
                  'old_data' => $record->old_data,
                  'new_data' => $record->new_data,
                  'pengaju' => $record->user?->name,
                ])
                ->log('Perubahan profil Kepala Cabang Dinas disetujui untuk ' . ($record->user?->name ?? ''));

              \Filament\Notifications\Notification::make()
                ->success()
                ->title('Perubahan disetujui!')
                ->body('Data Kepala Cabang Dinas telah diperbarui sesuai pengajuan.')
                ->send();
            }),

          Tables\Actions\Action::make('reject')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn($record) => $record->isPending())
            ->form([
              \Filament\Forms\Components\Textarea::make('alasan_reject')
                ->label('Alasan Penolakan')
                ->placeholder('Jelaskan alasan mengapa pengajuan ini ditolak...')
                ->required()
                ->rows(3),
            ])
            ->modalHeading('Tolak Perubahan Profil')
            ->modalSubmitActionLabel('Ya, Tolak')
            ->action(function ($record, array $data) {
              /** @var User $user */
              $user = Auth::user();

              $record->update([
                'status' => ProfileChangeRequest::STATUS_REJECTED,
                'alasan_reject' => $data['alasan_reject'],
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
              ]);

              activity()
                ->causedBy($user)
                ->performedOn($record)
                ->useLog('profil_change')
                ->event('rejected')
                ->withProperties([
                  'alasan' => $data['alasan_reject'],
                  'pengaju' => $record->user?->name,
                ])
                ->log('Perubahan profil Kepala Cabang Dinas ditolak untuk ' . ($record->user?->name ?? ''));

              \Filament\Notifications\Notification::make()
                ->success()
                ->title('Pengajuan ditolak')
                ->body('Pengajuan perubahan profil telah ditolak.')
                ->send();
            }),

          Tables\Actions\Action::make('view_detail')
            ->label('Detail')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->modalHeading(fn($record) => 'Detail Pengajuan — ' . ($record->user?->name ?? ''))
            ->modalContent(function ($record) {
              return view('filament.resources.verifikasi-profil.detail', [
                'record' => $record,
              ]);
            })
            ->modalWidth('2xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup'),
        ])->tooltip('Aksi'),
      ])
      ->bulkActions([])
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
