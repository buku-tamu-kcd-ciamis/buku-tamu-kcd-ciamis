<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BukuTamuResource\Pages;
use App\Models\BukuTamu;
use App\Models\DropdownOption;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class BukuTamuResource extends Resource
{
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
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->role_user && $user->role_user->hasPermission('buku_tamu');
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
            ->modifyQueryUsing(fn(Builder $query): Builder => $query
                ->where(function (Builder $q): void {
                    $q->whereNull('foto_penerimaan')
                        ->orWhere('foto_penerimaan', '');
                }))
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
                Tables\Filters\SelectFilter::make('keperluan')
                    ->label('Keperluan')
                    ->options(fn(): array => BukuTamu::query()
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
                        Forms\Components\DatePicker::make('tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['tanggal'], fn($q, $date) => $q->whereDate('created_at', $date));
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat Detail')
                        ->icon('heroicon-o-eye')
                        ->color('primary'),
                    Action::make('print')
                        ->label('Print')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->url(fn(BukuTamu $record): string => route('buku-tamu.print', ['id' => $record->id]))
                        ->openUrlInNewTab(true)
                        ->visible(fn(): bool => static::canPrint()),
                    Action::make('delete')
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->extraAttributes([
                            'style' => 'padding: 10px 16px !important;',
                        ])
                        ->modalHeading('Hapus Data Buku Tamu')
                        ->modalDescription(fn(BukuTamu $record): string => static::hasDeletePasswordVerification()
                            ? "Data '{$record->nama_lengkap}' akan dihapus permanen."
                            : "Data '{$record->nama_lengkap}' akan dihapus permanen. Verifikasi password hanya diminta sekali per sesi login.")
                        ->form(fn(): array => static::hasDeletePasswordVerification()
                            ? []
                            : [
                                Forms\Components\TextInput::make('password')
                                    ->label('Password Super Admin')
                                    ->password()
                                    ->required()
                                    ->autocomplete('current-password')
                                    ->helperText('Ketik password akun Super Admin. Verifikasi ini hanya diminta sekali per sesi login.'),
                            ])
                        ->action(function (array $data, BukuTamu $record): void {
                            static::verifyDeletePasswordForSession($data);

                            $nama = $record->nama_lengkap;
                            $record->delete();

                            Notification::make()
                                ->title('Data berhasil dihapus')
                                ->body("Data tamu '{$nama}' telah dihapus.")
                                ->success()
                                ->send();
                        })
                        ->visible(fn(): bool => static::isSuperAdmin()),
                    Action::make('set_diproses')
                        ->label('Tandai Diproses')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn(BukuTamu $record): bool => static::canChangeStatus() && !static::isSuperAdmin() && $record->status === BukuTamu::STATUS_MENUNGGU)
                        ->action(function (BukuTamu $record): void {
                            $record->update(['status' => BukuTamu::STATUS_DIPROSES]);

                            Notification::make()
                                ->title('Status diperbarui')
                                ->body('Data tamu ditandai sebagai Diproses.')
                                ->success()
                                ->send();
                        }),
                    Action::make('set_selesai')
                        ->label('Tandai Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(BukuTamu $record): bool => static::canChangeStatus() && !static::isSuperAdmin() && $record->status !== BukuTamu::STATUS_SELESAI)
                        ->action(function (BukuTamu $record): void {
                            $record->update(['status' => BukuTamu::STATUS_SELESAI]);

                            Notification::make()
                                ->title('Status diperbarui')
                                ->body('Data tamu ditandai sebagai Selesai.')
                                ->success()
                                ->send();
                        }),
                    Action::make('set_ditolak')
                        ->label('Tandai Ditolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn(BukuTamu $record): bool => static::canChangeStatus() && !static::isSuperAdmin() && $record->status !== BukuTamu::STATUS_DITOLAK)
                        ->action(function (BukuTamu $record): void {
                            $record->update(['status' => BukuTamu::STATUS_DITOLAK]);

                            Notification::make()
                                ->title('Status diperbarui')
                                ->body('Data tamu ditandai sebagai Ditolak.')
                                ->warning()
                                ->send();
                        }),
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
                        ->visible(fn(): bool => static::isSuperAdmin() && static::canPrint())
                        ->url(route('buku-tamu.print-bulk'))
                        ->livewireClickHandlerEnabled(false)
                        ->accessSelectedRecords(false)
                        ->openUrlInNewTab(true)
                        ->extraAttributes([
                            'style' => 'padding: 10px 16px !important;',
                            'x-bind:href' => "`\${window.location.origin}/print/buku-tamu-bulk?ids=\${[...selectedRecords].join(',')}`",
                        ]),
                    BulkAction::make('bulk_delete')
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->extraAttributes([
                            'style' => 'padding: 10px 16px !important;',
                        ])
                        ->modalHeading('Hapus Data Buku Tamu Terpilih')
                        ->modalDescription(fn(): string => static::hasDeletePasswordVerification()
                            ? 'Semua data buku tamu yang dipilih akan dihapus permanen.'
                            : 'Semua data buku tamu yang dipilih akan dihapus permanen. Verifikasi password hanya diminta sekali per sesi login.')
                        ->form(fn(): array => static::hasDeletePasswordVerification()
                            ? []
                            : [
                                Forms\Components\TextInput::make('password')
                                    ->label('Password Super Admin')
                                    ->password()
                                    ->required()
                                    ->autocomplete('current-password')
                                    ->helperText('Ketik password akun Super Admin. Verifikasi ini hanya diminta sekali per sesi login.'),
                            ])
                        ->action(function (array $data, $records): void {
                            static::verifyDeletePasswordForSession($data);

                            $count = $records->count();
                            $records->each(fn(BukuTamu $record): bool => $record->delete());

                            Notification::make()
                                ->title('Data berhasil dihapus')
                                ->body("{$count} data tamu berhasil dihapus.")
                                ->success()
                                ->send();
                        })
                        ->visible(fn(): bool => static::isSuperAdmin())
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('bulk_set_diproses')
                        ->label('Tandai Diproses')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn(): bool => static::canChangeStatus() && !static::isSuperAdmin())
                        ->action(function ($records): void {
                            $count = 0;

                            $records->each(function (BukuTamu $record) use (&$count): void {
                                if ($record->status !== BukuTamu::STATUS_DIPROSES) {
                                    $record->update(['status' => BukuTamu::STATUS_DIPROSES]);
                                    $count++;
                                }
                            });

                            Notification::make()
                                ->title('Bulk update selesai')
                                ->body("{$count} data tamu berhasil ditandai Diproses.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('bulk_set_selesai')
                        ->label('Tandai Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(): bool => static::canChangeStatus() && !static::isSuperAdmin())
                        ->action(function ($records): void {
                            $count = 0;

                            $records->each(function (BukuTamu $record) use (&$count): void {
                                if ($record->status !== BukuTamu::STATUS_SELESAI) {
                                    $record->update(['status' => BukuTamu::STATUS_SELESAI]);
                                    $count++;
                                }
                            });

                            Notification::make()
                                ->title('Bulk update selesai')
                                ->body("{$count} data tamu berhasil ditandai Selesai.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('bulk_set_ditolak')
                        ->label('Tandai Ditolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn(): bool => static::canChangeStatus() && !static::isSuperAdmin())
                        ->action(function ($records): void {
                            $count = 0;

                            $records->each(function (BukuTamu $record) use (&$count): void {
                                if ($record->status !== BukuTamu::STATUS_DITOLAK) {
                                    $record->update(['status' => BukuTamu::STATUS_DITOLAK]);
                                    $count++;
                                }
                            });

                            Notification::make()
                                ->title('Bulk update selesai')
                                ->body("{$count} data tamu berhasil ditandai Ditolak.")
                                ->warning()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    protected static function canChangeStatus(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) ($user && $user->role_user && $user->role_user->hasPermission('can_change_status'));
    }

    protected static function canPrint(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) ($user && $user->role_user && $user->role_user->hasPermission('can_print'));
    }

    protected static function isSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) ($user?->hasRole('Super Admin'));
    }

    protected static function deletePasswordVerificationSessionKey(): string
    {
        return 'buku_tamu.delete_password_verified_user_id';
    }

    protected static function hasDeletePasswordVerification(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return (int) session(static::deletePasswordVerificationSessionKey()) === (int) $user->id;
    }

    protected static function verifyDeletePasswordForSession(array $data): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            throw ValidationException::withMessages([
                'password' => 'Sesi login tidak valid. Silakan login ulang.',
            ]);
        }

        if (static::hasDeletePasswordVerification()) {
            return;
        }

        if (! Hash::check((string) ($data['password'] ?? ''), (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password tidak sesuai dengan akun yang sedang login.',
            ]);
        }

        session([
            static::deletePasswordVerificationSessionKey() => (int) $user->id,
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBukuTamus::route('/'),
            'view' => Pages\ViewBukuTamu::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
