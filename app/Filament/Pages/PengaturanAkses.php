<?php

namespace App\Filament\Pages;

use App\Models\RoleUser;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PengaturanAkses extends Page implements HasForms
{
  use InteractsWithForms;

  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
  protected static ?string $navigationLabel = 'Pengaturan Akses';
  protected static string|\UnitEnum|null $navigationGroup = 'Pengguna';
  protected static ?int $navigationSort = 3;
  protected static ?string $title = 'Pengaturan Akses & Visibilitas';
  protected string $view = 'filament.pages.pengaturan-akses';

  public ?array $kepalaCabdin = [];
  public ?array $piket = [];
  public ?string $paperSize = 'a4';

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user && $user->hasRole('Super Admin');
  }

  public function mount(): void
  {
    /** @var User $user */
    $user = Auth::user();
    if (!$user || !$user->hasRole('Super Admin')) {
      abort(403);
    }

    $kepalaCabdinRole = RoleUser::where('name', 'Kepala Cabang Dinas')->first();
    $piketRole = RoleUser::where('name', 'Piket')->first();

    $kepalaCabdinPermissions = array_merge(RoleUser::getDefaultPermissions(), $kepalaCabdinRole?->permissions ?? []);
    $piketPermissions = array_merge(RoleUser::getDefaultPermissions(), $piketRole?->permissions ?? []);
    $settings = \App\Models\PengaturanKcd::getSettings();

    $this->form->fill([
      'kepalaCabdin' => $kepalaCabdinPermissions,
      'piket' => $piketPermissions,
      'paperSize' => $settings->paper_size ?? 'a4',
    ]);
  }

  public function form(Schema $form): Schema
  {
    return $form->schema([
      Forms\Components\Select::make('paperSize')
        ->options([
          'a4' => 'A4 (210 x 297 mm)',
          'f4' => 'F4 (215 x 330 mm)',
        ])
        ->required(),
    ]);
  }

  public function save(): void
  {
    $data = $this->form->getState();

    $kepalaCabdinRole = RoleUser::where('name', 'Kepala Cabang Dinas')->first();
    $piketRole = RoleUser::where('name', 'Piket')->first();

    if ($kepalaCabdinRole) {
      $kepalaCabdinRole->update(['permissions' => $data['kepalaCabdin']]);
    }

    if ($piketRole) {
      $piketRole->update(['permissions' => $data['piket']]);
    }

    \App\Models\PengaturanKcd::getSettings()->update([
      'paper_size' => $data['paperSize']
    ]);

    activity()
      ->causedBy(Auth::user())
      ->useLog('pengaturan_akses')
      ->event('updated')
      ->log('Pengaturan akses role diperbarui');

    Notification::make()
      ->success()
      ->title('Pengaturan akses berhasil disimpan!')
      ->body('Visibilitas menu dan pengaturan aksi telah diperbarui untuk semua role.')
      ->send();
  }
}
