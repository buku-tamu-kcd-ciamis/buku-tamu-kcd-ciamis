<?php

namespace App\Filament\Staff\Pages;

use Filament\Forms;
use Filament\Schemas\Schema;
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
                Section::make('Informasi Akun')
                    ->description('Perbarui informasi akun Anda')
                    ->icon('heroicon-o-user')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ]),
            ]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return null;
    }

    protected function afterSave(): void
    {
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
            background: 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
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
