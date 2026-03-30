<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="border-t border-gray-200 pt-6 dark:border-gray-700 flex justify-end" style="margin-top: 2rem;">
            <x-filament::button type="submit" icon="heroicon-o-check-circle" class="pengaturan-akses-submit-btn">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
