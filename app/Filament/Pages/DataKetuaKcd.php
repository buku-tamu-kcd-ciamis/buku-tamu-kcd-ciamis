<?php

namespace App\Filament\Pages;

use App\Models\PengaturanKcd;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class DataKetuaKcd extends Page implements HasForms
{
  use InteractsWithForms;

  protected static ?string $navigationIcon = 'heroicon-o-identification';
  protected static ?string $navigationLabel = 'Data Ketua KCD';
  protected static ?string $navigationGroup = 'Pengaturan';
  protected static ?string $title = 'Data Ketua KCD';
  protected static ?int $navigationSort = 13;
  protected static string $view = 'filament.pages.data-ketua-kcd';

  public ?array $data = [];

  public static function shouldRegisterNavigation(): bool
  {
    /** @var User $user */
    $user = Auth::user();
    return $user?->role_user?->name === 'Super Admin';
  }

  public function mount(): void
  {
    $settings = PengaturanKcd::getSettings();
    $this->form->fill([
      'nama_ketua' => $settings->nama_ketua,
      'nip_ketua' => $settings->nip_ketua,
      'jabatan' => $settings->jabatan,
    ]);
  }

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make('Informasi Kepala Cabang Dinas')
          ->description('Data ini akan ditampilkan pada bagian tanda tangan di seluruh halaman cetak/print.')
          ->icon('heroicon-o-identification')
          ->schema([
            Forms\Components\TextInput::make('nama_ketua')
              ->label('Nama Lengkap Ketua KCD')
              ->placeholder('Contoh: Drs. H. Ahmad Suryadi, M.Pd.')
              ->maxLength(255)
              ->helperText('Nama lengkap beserta gelar depan dan belakang.')
              ->required(),

            Forms\Components\TextInput::make('nip_ketua')
              ->label('NIP')
              ->placeholder('Contoh: 196712051992031005')
              ->mask('999999999999999999')
              ->length(18)
              ->helperText('NIP harus terdiri dari 18 digit.')
              ->required(),

            Forms\Components\TextInput::make('jabatan')
              ->label('Jabatan')
              ->default('Kepala Cabang Dinas Pendidikan Wilayah XIII')
              ->maxLength(255)
              ->helperText('Jabatan yang ditampilkan di halaman cetak.')
              ->required(),
          ])
          ->columns(1),

        Forms\Components\Section::make('Preview Tanda Tangan')
          ->description('Tampilan tanda tangan pada halaman cetak.')
          ->icon('heroicon-o-eye')
          ->schema([
            Forms\Components\Placeholder::make('preview')
              ->label('')
              ->content(function ($get) {
                $nama = $get('nama_ketua') ?: '(...............................................)';
                $nip = $get('nip_ketua') ? 'NIP. ' . $get('nip_ketua') : 'NIP. ..............................';
                $jabatan = $get('jabatan') ?: 'Kepala Cabang Dinas Pendidikan Wilayah XIII';

                return new \Illuminate\Support\HtmlString("
                                    <div class='rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 p-6 max-w-sm mx-auto text-center' style='font-family: Times New Roman, serif;'>
                                        <p class='text-sm text-gray-700 dark:text-gray-200 mb-1'>Ciamis, " . now()->translatedFormat('d F Y') . "</p>
                                        <p class='text-sm text-gray-700 dark:text-gray-200 mt-2'>{$jabatan},</p>
                                        <p class='text-sm font-bold text-gray-900 dark:text-white mt-16 pb-1 inline-block' style='border-bottom: 2px solid currentColor;'>{$nama}</p>
                                        <br><span class='text-xs text-gray-700 dark:text-gray-200 mt-1 inline-block'>{$nip}</span>
                                    </div>
                                ");
              }),
          ])
          ->collapsible(),
      ])
      ->statePath('data');
  }

  public function save(): void
  {
    $data = $this->form->getState();

    $settings = PengaturanKcd::getSettings();
    $settings->update($data);

    activity()
      ->causedBy(Auth::user())
      ->performedOn($settings)
      ->useLog('pengaturan')
      ->event('updated')
      ->withProperties(['attributes' => $data])
      ->log('Data Ketua KCD diperbarui: ' . ($data['nama_ketua'] ?? ''));

    Notification::make()
      ->title('Data Ketua KCD berhasil disimpan!')
      ->body('Perubahan akan langsung ditampilkan di seluruh halaman cetak.')
      ->success()
      ->send();
  }
}
