<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
  protected ?string $maxWidth = '5xl';

  protected static ?string $title = 'Profil';

  protected static ?string $navigationLabel = 'Profil';

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Grid::make(2)
          ->schema([
            Forms\Components\Section::make('Informasi Akun')
              ->description('Perbarui informasi akun Anda')
              ->icon('heroicon-o-user')
              ->schema([
                $this->getNameFormComponent()
                  ->label('Nama Lengkap'),
                $this->getEmailFormComponent()
                  ->label('Email'),
              ])
              ->columnSpan(1),

            Forms\Components\Section::make('Keamanan')
              ->description('Ubah password akun Anda')
              ->icon('heroicon-o-lock-closed')
              ->schema([
                Forms\Components\TextInput::make('current_password')
                  ->label('Password Saat Ini')
                  ->password()
                  ->revealable()
                  ->required(fn(callable $get) => filled($get('password')))
                  ->dehydrated(false)
                  ->rules([
                    fn() => function (string $attribute, $value, $fail) {
                      $user = Auth::user();
                      if ($user && ! Hash::check($value, $user->password)) {
                        $fail('Password saat ini tidak sesuai.');
                      }
                    },
                  ]),
                Forms\Components\TextInput::make('password')
                  ->label('Password Baru')
                  ->password()
                  ->revealable()
                  ->rule(Password::default())
                  ->dehydrateStateUsing(fn($state) => Hash::make($state))
                  ->dehydrated(fn($state) => filled($state))
                  ->live(debounce: 500)
                  ->same('passwordConfirmation'),
                Forms\Components\TextInput::make('passwordConfirmation')
                  ->label('Konfirmasi Password Baru')
                  ->password()
                  ->revealable()
                  ->requiredWith('password')
                  ->dehydrated(false),
              ])
              ->columnSpan(1),
          ]),
      ]);
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
        toast.textContent = 'Profil berhasil diperbarui! Perubahan pada akun Anda telah tersimpan.';
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
      $this->getSaveFormAction()
        ->label('Simpan Perubahan'),
      $this->getCancelFormAction()
        ->label('Batal'),
    ];
  }
}
