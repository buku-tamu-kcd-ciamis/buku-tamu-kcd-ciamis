<?php

namespace App\Filament\Pages;

use App\Models\PengaturanKcd;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;

class DataKepalaCabdin extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Data Kepala Cabang Dinas';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?string $title = 'Data Kepala Cabang Dinas';
    protected static ?int $navigationSort = 13;
    protected string $view = 'filament.pages.data-kepala-cabdin';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User $user */
        $user = Filament::auth()->user();

        return $user?->role_user?->name === 'Super Admin';
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::auth()->user();

        return $user?->role_user?->name === 'Super Admin';
    }

    public function mount(): void
    {
        /** @var User $user */
        $user = Filament::auth()->user();

        if (!$user || $user->role_user?->name !== 'Super Admin') {
            abort(403);
        }

        $settings = PengaturanKcd::getSettings();

        $this->form->fill([
            'nama_kepala' => $settings->nama_kepala,
            'nip_kepala' => $settings->nip_kepala,
            'jabatan' => $settings->jabatan,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Kepala Cabang Dinas')
                    ->description('Data ini akan ditampilkan pada bagian tanda tangan di seluruh halaman cetak/print.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        TextInput::make('nama_kepala')
                            ->label('Nama Kepala Cabang Dinas')
                            ->placeholder('Contoh: Drs. H. Ahmad Suryadi, M.Pd.')
                            ->maxLength(255)
                            ->helperText('Nama lengkap beserta gelar depan dan belakang.')
                            ->required()
                            ->live(),
                        TextInput::make('nip_kepala')
                            ->label('NIP Kepala Cabang Dinas')
                            ->placeholder('Contoh: 196712051992031005')
                            ->mask('999999999999999999')
                            ->length(18)
                            ->helperText('NIP harus terdiri dari 18 digit.')
                            ->required()
                            ->live(),
                        TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->maxLength(255)
                            ->helperText('Jabatan yang ditampilkan di halaman cetak.')
                            ->required()
                            ->live(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = PengaturanKcd::getSettings();
        $settings->update($data);

        activity()
            ->causedBy(Filament::auth()->user())
            ->performedOn($settings)
            ->useLog('pengaturan')
            ->event('updated')
            ->withProperties(['attributes' => $data])
            ->log('Data Kepala Cabang Dinas diperbarui: ' . ($data['nama_kepala'] ?? ''));

        Notification::make()
            ->title('Data Kepala Cabang Dinas berhasil disimpan!')
            ->body('Perubahan akan langsung ditampilkan di seluruh halaman cetak.')
            ->success()
            ->send();
    }
}
