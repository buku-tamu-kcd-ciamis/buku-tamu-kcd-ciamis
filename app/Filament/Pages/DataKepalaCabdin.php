<?php

namespace App\Filament\Pages;

use App\Models\PengaturanKcd;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();
        return $user?->role_user?->name === 'Super Admin';
    }

    public function mount(): void
    {
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
                            ->placeholder('Contoh: Drs. H. Ahmad Suryadi, M.Pd.')
                            ->maxLength(255)
                            ->helperText('Nama lengkap beserta gelar depan dan belakang.')
                            ->required(),

                        TextInput::make('nip_kepala')
                            ->placeholder('Contoh: 196712051992031005')
                            ->mask('999999999999999999')
                            ->length(18)
                            ->helperText('NIP harus terdiri dari 18 digit.')
                            ->required(),

                        TextInput::make('jabatan')
                            ->maxLength(255)
                            ->helperText('Jabatan yang ditampilkan di halaman cetak.')
                            ->required(),
                    ])
                    ->columns(1),

                Section::make('Preview Tanda Tangan')
                    ->description('Tampilan tanda tangan pada halaman cetak.')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Placeholder::make('preview')
                            ->content(function ($get) {
                                $nama = $get('nama_kepala') ?: '(...............................................)';
                                $nip = $get('nip_kepala') ? 'NIP. ' . $get('nip_kepala') : 'NIP. ..............................';
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
            ->log('Data Kepala Cabang Dinas diperbarui: ' . ($data['nama_kepala'] ?? ''));

        Notification::make()
            ->title('Data Kepala Cabang Dinas berhasil disimpan!')
            ->body('Perubahan akan langsung ditampilkan di seluruh halaman cetak.')
            ->success()
            ->send();
    }
}
