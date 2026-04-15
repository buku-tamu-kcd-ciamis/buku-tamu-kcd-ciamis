<?php

namespace App\Filament\Pages;

use App\Models\RoleUser;
use App\Models\User;
use App\Support\PrintPaperSize;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;

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
  public ?array $staff = [];
  public ?string $paperSize = 'a4';

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Filament::auth()->user();
    return $user && $user->hasRole('Super Admin');
  }

  public static function canAccess(): bool
  {
    /** @var User $user */
    $user = Filament::auth()->user();

    return $user && $user->hasRole('Super Admin');
  }

  public function mount(): void
  {
    /** @var User $user */
    $user = Filament::auth()->user();
    if (!$user || !$user->hasRole('Super Admin')) {
      abort(403);
    }

    $kepalaCabdinRole = RoleUser::where('name', 'Kepala Cabang Dinas')->first();
    $piketRole = RoleUser::where('name', 'Piket')->first();
    $staffRole = RoleUser::where('name', 'Staff')->first();

    $kepalaCabdinPermissions = array_merge(RoleUser::getDefaultPermissions(), $kepalaCabdinRole?->permissions ?? []);
    $piketPermissions = array_merge(RoleUser::getDefaultPermissions(), $piketRole?->permissions ?? []);
    $staffPermissions = array_merge(RoleUser::getDefaultPermissions(), $staffRole?->permissions ?? []);
    $settings = \App\Models\PengaturanKcd::getSettings();

    $this->form->fill([
      'kepalaCabdin' => $this->getSelectedPermissions($kepalaCabdinPermissions),
      'piket' => $this->getSelectedPermissions($piketPermissions),
      'staff' => $this->getSelectedPermissions($staffPermissions),
      'paperSize' => $settings->paper_size ?? 'a4',
    ]);
  }

  public function form(Schema $form): Schema
  {
    $resourcePermissionLabels = RoleUser::getResourcePermissionLabels();
    $actionPermissionLabels = [
      'can_print' => 'Bisa Cetak',
      'can_change_status' => 'Bisa Ubah Status',
    ];

    return $form->schema([
      Section::make('Akses Kepala Cabang Dinas')
        ->description('Atur menu dan aksi yang dapat diakses oleh role Kepala Cabang Dinas.')
        ->schema([
          Forms\Components\CheckboxList::make('kepalaCabdin')
            ->hiddenLabel()
            ->options($resourcePermissionLabels + $actionPermissionLabels)
            ->columns(2)
            ->bulkToggleable()
            ->descriptions([
              'can_print' => 'Mengizinkan akses tombol cetak pada halaman terkait.',
              'can_change_status' => 'Mengizinkan perubahan status data yang memerlukan otorisasi.',
            ]),
        ])
        ->collapsible(),

      Section::make('Akses Piket')
        ->description('Atur menu dan aksi yang dapat diakses oleh role Piket.')
        ->schema([
          Forms\Components\CheckboxList::make('piket')
            ->hiddenLabel()
            ->options($resourcePermissionLabels + $actionPermissionLabels)
            ->columns(2)
            ->bulkToggleable()
            ->descriptions([
              'can_print' => 'Mengizinkan akses tombol cetak pada halaman terkait.',
              'can_change_status' => 'Mengizinkan perubahan status data yang memerlukan otorisasi.',
            ]),
        ])
        ->collapsible(),

      Section::make('Akses Staff')
        ->description('Atur menu dan aksi yang dapat diakses oleh role Staff.')
        ->schema([
          Forms\Components\CheckboxList::make('staff')
            ->hiddenLabel()
            ->options($resourcePermissionLabels + $actionPermissionLabels)
            ->columns(2)
            ->bulkToggleable()
            ->descriptions([
              'can_print' => 'Mengizinkan akses tombol cetak pada halaman terkait.',
              'can_change_status' => 'Mengizinkan perubahan status data yang memerlukan otorisasi.',
            ]),
        ])
        ->collapsible(),

      Section::make('Pengaturan Cetak')
        ->description('Atur ukuran kertas default untuk fitur cetak surat dan laporan.')
        ->schema([
          Forms\Components\Select::make('paperSize')
            ->label('Paper size')
            ->options(PrintPaperSize::options())
            ->searchable()
            ->required(),
        ]),
    ]);
  }

  public function save(): void
  {
    $data = $this->form->getState();
    $kepalaCabdinPermissions = $this->toPermissionMap($data['kepalaCabdin'] ?? []);
    $piketPermissions = $this->toPermissionMap($data['piket'] ?? []);
    $staffPermissions = $this->toPermissionMap($data['staff'] ?? []);

    $kepalaCabdinRole = RoleUser::where('name', 'Kepala Cabang Dinas')->first();
    $piketRole = RoleUser::where('name', 'Piket')->first();
    $staffRole = RoleUser::where('name', 'Staff')->first();

    if ($kepalaCabdinRole) {
      $kepalaCabdinRole->update(['permissions' => $kepalaCabdinPermissions]);
    }

    if ($piketRole) {
      $piketRole->update(['permissions' => $piketPermissions]);
    }

    if ($staffRole) {
      $staffRole->update(['permissions' => $staffPermissions]);
    }

    \App\Models\PengaturanKcd::getSettings()->update([
      'paper_size' => $data['paperSize']
    ]);

    activity()
      ->causedBy(Filament::auth()->user())
      ->useLog('pengaturan_akses')
      ->event('updated')
      ->log('Pengaturan akses role diperbarui');

    Notification::make()
      ->success()
      ->title('Pengaturan akses berhasil disimpan!')
      ->body('Visibilitas menu dan pengaturan aksi telah diperbarui untuk semua role.')
      ->send();
  }

  private function getSelectedPermissions(array $permissions): array
  {
    return collect($permissions)
      ->filter(fn($isAllowed) => (bool) $isAllowed)
      ->keys()
      ->values()
      ->all();
  }

  private function toPermissionMap(array $selectedPermissions): array
  {
    $selected = array_flip($selectedPermissions);

    return collect(RoleUser::getDefaultPermissions())
      ->mapWithKeys(fn($value, $key) => [$key => isset($selected[$key])])
      ->all();
  }
}
