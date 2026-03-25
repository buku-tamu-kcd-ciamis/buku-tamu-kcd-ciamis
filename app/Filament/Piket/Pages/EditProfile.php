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
  protected string | Width | null $maxWidth = '5xl';

  protected static ?string $title = 'Profil';

  protected static ?string $navigationLabel = 'Profil';

  public function form(Schema $schema): Schema
  {
    return $schema
      ->components([
        Grid::make(2)
          ->schema([
            Section::make('Informasi Akun')
              ->description('Perbarui informasi akun Anda')
              ->icon('heroicon-o-user')
              ->schema([
                $this->getNameFormComponent()
                  ->label('Nama Lengkap'),
                $this->getEmailFormComponent()
                  ->label('Email'),
              ])
              ->columnSpan(1),

            Section::make('Keamanan')
              ->description('Ubah password akun Anda')
              ->icon('heroicon-o-lock-closed')
              ->schema([
                Forms\Components\TextInput::make('current_password')
                  ->password()
                  ->revealable()
                  ->required(fn(callable $get) => filled($get('password')))
                  ->rules([
                    fn() => function (string $attribute, $value, $fail) {
                      if (! Hash::check($value, Auth::user()?->password ?? '')) {
                        $fail('Password saat ini tidak sesuai.');
                      }
                    },
                  ]),
                Forms\Components\TextInput::make('password')
                  ->password()
                  ->revealable()
                  ->rule(Password::default())
                  ->same('passwordConfirmation'),
                Forms\Components\TextInput::make('passwordConfirmation')
                  ->password()
                  ->revealable()
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
    if (filled($data['password'] ?? null)) {
      $data['password'] = Hash::make((string) $data['password']);
    } else {
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
      $this->getSaveFormAction(),
      $this->getCancelFormAction(),
    ];
  }
}
