<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-8 border-t border-gray-200 pt-5 dark:border-gray-700 flex justify-end">
            <x-filament::button type="submit" icon="heroicon-o-check-circle">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
