<?php

namespace App\Filament\Piket\Pages;

use App\Models\BukuTamu;
use App\Models\DropdownOption;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->role_user && $user->role_user->hasPermission('pengantar_berkas');
  }

  public function table(Table $table): Table
  {
    return $table
      ->query(
        BukuTamu::query()
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
          ->tooltip(fn($record) => $record->created_at->format('d/m/Y H:i'))
          ->sortable(),
      ])
      ->defaultSort('created_at', 'desc')
      ->defaultPaginationPageOption(10)
      ->paginationPageOptions([10])
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->options(BukuTamu::STATUS_LABELS),
      ])
      ->recordActions([])
      ->headerActions([])
      ->toolbarActions([]);
  }
}
