<?php

namespace App\Filament\Piket\Pages;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
  protected string | Width | null $maxWidth = '4xl';

  protected static ?string $title = 'Profil';

  protected static ?string $navigationLabel = 'Profil';

  public function form(Schema $schema): Schema
  {
    return $schema
      ->components([
        Grid::make([
          'default' => 1,
          'md' => 2,
        ])
          ->schema([
            Section::make('Informasi Akun')
              ->description('Perbarui data akun')
              ->icon('heroicon-o-user')
              ->schema([
                $this->getNameFormComponent()
                  ->label('Nama Lengkap'),
                $this->getEmailFormComponent()
                  ->label('Email'),
                Forms\Components\FileUpload::make('profile_photo_path')
                  ->label('Foto Profil')
                  ->image()
                  ->default('profile-photos/default-donut.svg')
                  ->directory('profile-photos')
                  ->disk('public')
                  ->visibility('public')
                  ->maxSize(2048)
                  ->panelLayout('compact')
                  ->imagePreviewHeight('110')
                  ->placeholder('<span class="filepond--label-action">Upload Foto Baru</span>')
                  ->extraAlpineAttributes([
                    'x-effect' => '$nextTick(() => { const input = $el.querySelector("input[type=file]"); if (input && input.hasAttribute("disabled")) { input.removeAttribute("disabled") } })',
                  ])
                  ->openable()
                  ->imageEditor()
                  ->imageEditorAspectRatioOptions([null, '1:1'])
                  ->imageEditorMode(2)
                  ->imageEditorEmptyFillColor('#000000')
                  ->automaticallyCropImagesToAspectRatio(false)
                  ->automaticallyResizeImagesMode('contain')
                  ->automaticallyResizeImagesToWidth('512')
                  ->automaticallyResizeImagesToHeight('512')
                  ->helperText('Panduan singkat: 1) Klik Upload Foto Baru. 2) Pilih file JPG/PNG (maks. 2 MB). 3) Klik Simpan Perubahan.')
                  ->columnSpanFull(),
              ])
              ->columnSpan(1),

            Section::make('Keamanan Akun')
              ->description('Masukkan password lama jika ingin ganti password')
              ->icon('heroicon-o-lock-closed')
              ->schema([
                Forms\Components\TextInput::make('current_password')
                  ->label('Password Saat Ini')
                  ->password()
                  ->revealable()
                  ->dehydrated(false)
                  ->required(fn(callable $get) => filled($get('password')))
                  ->rules([
                    fn() => function (string $attribute, $value, $fail) {
                      $user = Auth::user();

                      if ($user && !Hash::check((string) $value, $user->password)) {
                        $fail('Password saat ini tidak sesuai.');
                      }
                    },
                  ]),
                Forms\Components\TextInput::make('password')
                  ->label('Password Baru')
                  ->password()
                  ->revealable()
                  ->rule(Password::default())
                  ->same('passwordConfirmation')
                  ->dehydrateStateUsing(fn(?string $state) => filled($state) ? Hash::make($state) : null),
                Forms\Components\TextInput::make('passwordConfirmation')
                  ->label('Konfirmasi Password Baru')
                  ->password()
                  ->revealable()
                  ->dehydrated(false)
                  ->requiredWith('password')
              ])
              ->columnSpan(1),
          ]),
      ]);
  }

  /**
   * @param array<string, mixed> $data
   * @return array<string, mixed>
   */
  protected function mutateFormDataBeforeSave(array $data): array
  {
    if (blank($data['password'] ?? null)) {
      unset($data['password']);
    }

    unset($data['current_password'], $data['passwordConfirmation']);

    return $data;
  }

  protected function getSavedNotification(): ?Notification
  {
    return null;
  }

  protected function afterSave(): void
  {
    // Inject toast langsung via JS — paling reliable di Livewire 3
    $this->js(<<<'JS'
      (function() {
        let toast = document.getElementById('filament-toast');
        if (!toast) {
          toast = document.createElement('div');
          toast.id = 'filament-toast';
          Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            left: '50%',
            transform: 'translateX(-50%) translateY(-100px)',
            background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
            color: 'white',
            padding: '16px 32px',
            borderRadius: '12px',
            fontFamily: 'Inter, Figtree, ui-sans-serif, system-ui, sans-serif',
            fontSize: '14px',
            fontWeight: '500',
            zIndex: '99999',
            boxShadow: '0 10px 40px rgba(0,0,0,0.3)',
            transition: 'all 0.4s cubic-bezier(0.175,0.885,0.32,1.275)',
            opacity: '0',
            pointerEvents: 'none',
            textAlign: 'center',
            maxWidth: '90vw',
            minWidth: '320px'
          });
          document.body.appendChild(toast);
        }
        toast.textContent = 'Profil berhasil diperbarui!';
        setTimeout(() => {
          toast.style.transform = 'translateX(-50%) translateY(0)';
          toast.style.opacity = '1';
        }, 50);
        clearTimeout(toast._t);
        toast._t = setTimeout(() => {
          toast.style.transform = 'translateX(-50%) translateY(-100px)';
          toast.style.opacity = '0';
        }, 5000);
      })();
    JS);
  }

  protected function getFormActions(): array
  {
    return [
      $this->getSaveFormAction(),
      $this->getCancelFormAction(),
    ];
  }
}
