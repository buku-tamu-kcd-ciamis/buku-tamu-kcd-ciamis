<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages;

use App\Exports\PegawaiTemplateExport;
use App\Filament\Resources\DataPegawaiResource;
use App\Imports\PegawaiImport;
use App\Models\RoleUser;
use App\Models\User;
use App\Services\StaffDitujuSyncService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Throwable;

class ListDataPegawai extends ListRecords
{
  protected static string $resource = DataPegawaiResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('importPegawaiExcel')
        ->label('Import')
        ->icon('heroicon-o-arrow-up-tray')
        ->color('gray')
        ->modalHeading('Import Data Pegawai dari Excel')
        ->modalDescription('Unggah file Excel dengan format utama: Nama Pegawai, NIP, Pangkat/Golongan, Jabatan, Unit Kerja.')
        ->schema([
          FileUpload::make('file')
            ->label('File Excel')
            ->disk('local')
            ->directory('imports/pegawai')
            ->preserveFilenames()
            ->acceptedFileTypes([
              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
              'application/vnd.ms-excel',
            ])
            ->maxSize(5120)
            ->helperText('Template terbaru sudah disesuaikan dengan bahasa kolom baru. Kolom Role User/Password Login bersifat opsional; jika password kosong sistem akan generate default otomatis sesuai role.')
            ->required(),
        ])
        ->action(function (array $data): void {
          $uploadedState = $data['file'] ?? null;
          $relativePath = is_array($uploadedState)
            ? (string) ($uploadedState[0] ?? '')
            : (string) $uploadedState;

          if ($relativePath === '' || !Storage::disk('local')->exists($relativePath)) {
            Notification::make()
              ->danger()
              ->title('File tidak ditemukan')
              ->body('Silakan unggah ulang file Excel untuk proses import.')
              ->send();

            return;
          }

          try {
            $importer = (new PegawaiImport())->import(Storage::disk('local')->path($relativePath));
            $syncResult = app(StaffDitujuSyncService::class)->syncNow();
            $syncSummary = $syncResult['has_staff']
              ? ($syncResult['saved'] . ' opsi staff aktif tersinkron otomatis')
              : ('tidak ada pegawai aktif, ' . $syncResult['removed'] . ' opsi staff lama dibersihkan');

            if ($importer->hasErrors()) {
              $errors = array_slice($importer->getErrors(), 0, 3);

              Notification::make()
                ->warning()
                ->title('Import selesai dengan catatan')
                ->body($importer->getSummary() . '. Sinkron staff otomatis: ' . $syncSummary . '. Contoh error: ' . implode(' | ', $errors))
                ->send();
            } else {
              Notification::make()
                ->success()
                ->title('Import berhasil')
                ->body($importer->getSummary() . '. Sinkron staff otomatis: ' . $syncSummary)
                ->send();
            }
          } catch (Throwable $exception) {
            Log::error('Import Data Pegawai gagal.', [
              'exception' => $exception,
              'message' => $exception->getMessage(),
            ]);

            Notification::make()
              ->danger()
              ->title('Import gagal')
              ->body('Gagal import. Periksa format file dan gunakan template yang disediakan.')
              ->send();
          } finally {
            Storage::disk('local')->delete($relativePath);
          }
        }),
      Actions\Action::make('downloadTemplatePegawai')
        ->label('Template')
        ->icon('heroicon-o-document-arrow-down')
        ->color('gray')
        ->action(fn() => (new PegawaiTemplateExport())->download()),
      Actions\Action::make('resetDefaultStaffPassword')
        ->label('Reset Password Staff')
        ->icon('heroicon-o-key')
        ->color('warning')
        ->requiresConfirmation()
        ->modalHeading('Reset Password Default Staff')
        ->modalDescription('Semua akun dengan role Staff akan direset ke password default staff123. Setelah login, pengguna bisa mengganti password dari halaman profil.')
        ->modalSubmitActionLabel('Reset Sekarang')
        ->action(function (): void {
          $result = $this->resetStaffPasswordsToDefault();

          if ($result['error'] !== null) {
            Notification::make()
              ->warning()
              ->title('Reset password dibatalkan')
              ->body($result['error'])
              ->send();

            return;
          }

          Notification::make()
            ->success()
            ->title('Reset password Staff selesai')
            ->body($result['updated'] . ' akun Staff berhasil direset ke password default staff123.')
            ->send();
        }),
      Actions\Action::make('resetDefaultKepalaCabdinPassword')
        ->label('Reset Password Kepala Cabang')
        ->icon('heroicon-o-shield-check')
        ->color('warning')
        ->requiresConfirmation()
        ->modalHeading('Reset Password Default Kepala Cabang Dinas')
        ->modalDescription('Semua akun dengan role Kepala Cabang Dinas akan direset ke password default kepalakcd123. Setelah login, pengguna bisa mengganti password dari halaman profil.')
        ->modalSubmitActionLabel('Reset Sekarang')
        ->action(function (): void {
          $result = $this->resetKepalaCabdinPasswordsToDefault();

          if ($result['error'] !== null) {
            Notification::make()
              ->warning()
              ->title('Reset password dibatalkan')
              ->body($result['error'])
              ->send();

            return;
          }

          Notification::make()
            ->success()
            ->title('Reset password Kepala Cabang selesai')
            ->body($result['updated'] . ' akun Kepala Cabang Dinas berhasil direset ke password default kepalakcd123.')
            ->send();
        }),
      Actions\Action::make('exportPegawaiPdf')
        ->label('Export')
        ->icon('heroicon-o-document-arrow-down')
        ->color('gray')
        ->url(route('data-pegawai.print'))
        ->openUrlInNewTab(true),
      Actions\CreateAction::make()
        ->label('Buat')
        ->color('success'),
    ];
  }

  protected function resetStaffPasswordsToDefault(): array
  {
    $staffRoleId = RoleUser::query()
      ->where('name', 'Staff')
      ->value('id');

    if (! $staffRoleId) {
      return [
        'updated' => 0,
        'error' => 'Role Staff tidak ditemukan.',
      ];
    }

    $users = User::query()
      ->where('role_user_id', $staffRoleId)
      ->get(['id']);

    if ($users->isEmpty()) {
      return [
        'updated' => 0,
        'error' => 'Tidak ada akun Staff yang dapat direset.',
      ];
    }

    $updated = 0;

    foreach ($users as $user) {
      $user->update([
        'password' => Hash::make('staff123'),
      ]);
      $updated++;
    }

    return [
      'updated' => $updated,
      'error' => null,
    ];
  }

  protected function resetKepalaCabdinPasswordsToDefault(): array
  {
    $kepalaCabdinRoleId = RoleUser::query()
      ->where('name', 'Kepala Cabang Dinas')
      ->value('id');

    if (! $kepalaCabdinRoleId) {
      return [
        'updated' => 0,
        'error' => 'Role Kepala Cabang Dinas tidak ditemukan.',
      ];
    }

    $users = User::query()
      ->where('role_user_id', $kepalaCabdinRoleId)
      ->get(['id']);

    if ($users->isEmpty()) {
      return [
        'updated' => 0,
        'error' => 'Tidak ada akun Kepala Cabang Dinas yang dapat direset.',
      ];
    }

    $updated = 0;

    foreach ($users as $user) {
      $user->update([
        'password' => Hash::make('kepalakcd123'),
      ]);
      $updated++;
    }

    return [
      'updated' => $updated,
      'error' => null,
    ];
  }
}
