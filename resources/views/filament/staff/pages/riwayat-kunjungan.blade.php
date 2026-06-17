<x-filament-panels::page>
    <x-filament::tabs>
        <x-filament::tabs.item
            :active="$activeTab === 'hari_ini'"
            wire:click="$set('activeTab', 'hari_ini')"
            :badge="$this->getTabBadge('hari_ini')"
            badge-color="success"
        >
            Hari Ini
        </x-filament::tabs.item>
        <x-filament::tabs.item
            :active="$activeTab === 'kemarin'"
            wire:click="$set('activeTab', 'kemarin')"
            :badge="$this->getTabBadge('kemarin')"
            badge-color="warning"
        >
            Kemarin
        </x-filament::tabs.item>
        <x-filament::tabs.item
            :active="$activeTab === 'minggu_ini'"
            wire:click="$set('activeTab', 'minggu_ini')"
            :badge="$this->getTabBadge('minggu_ini')"
            badge-color="primary"
        >
            Minggu Ini
        </x-filament::tabs.item>
        <x-filament::tabs.item
            :active="$activeTab === 'bulan_ini'"
            wire:click="$set('activeTab', 'bulan_ini')"
            :badge="$this->getTabBadge('bulan_ini')"
            badge-color="info"
        >
            Bulan Ini
        </x-filament::tabs.item>
        <x-filament::tabs.item
            :active="$activeTab === 'semua'"
            wire:click="$set('activeTab', 'semua')"
            :badge="$this->getTabBadge('semua')"
            badge-color="gray"
        >
            Semua Data
        </x-filament::tabs.item>
    </x-filament::tabs>

    {{ $this->table }}
</x-filament-panels::page>