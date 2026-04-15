<x-filament-panels::page>
    @php
        $statuses = [
            'available' => [
                'label' => 'Tersedia',
                'description' => 'Siap menerima tamu',
                'icon' => 'heroicon-o-check-circle',
                'tone' => 'sky',
            ],
            'busy' => [
                'label' => 'Sibuk',
                'description' => 'Sedang fokus menyelesaikan pekerjaan',
                'icon' => 'heroicon-o-clock',
                'tone' => 'amber',
            ],
            'out_of_office' => [
                'label' => 'Tidak di Kantor',
                'description' => 'Sementara tidak berada di lokasi kantor',
                'icon' => 'heroicon-o-x-circle',
                'tone' => 'rose',
            ],
        ];

        $current = $statuses[$availability_status] ?? $statuses['available'];
    @endphp

    <div class="staff-availability-page mx-auto max-w-6xl space-y-6">
        <section class="staff-availability-hero">
            <div class="staff-availability-current is-{{ $current['tone'] }}">
                <div class="staff-availability-current-head">
                    <span class="staff-availability-current-chip">Status Aktif</span>
                    <span class="staff-availability-current-dot"></span>
                </div>

                <div class="staff-availability-current-body">
                    <div class="staff-availability-current-icon">
                        <x-dynamic-component :component="$current['icon']" class="h-9 w-9" />
                    </div>
                    <div>
                        <h2 class="staff-availability-current-title">{{ $current['label'] }}</h2>
                        <p class="staff-availability-current-description">{{ $current['description'] }}</p>
                    </div>
                </div>
            </div>

            <aside class="staff-availability-note">
                <x-heroicon-o-information-circle class="h-5 w-5" />
                <div>
                    <p class="staff-availability-note-title">Tentang Status Ketersediaan</p>
                    <p class="staff-availability-note-text">
                        Status ini terlihat di <strong>Direktori Pegawai</strong> agar rekan kerja dan petugas piket
                        lebih mudah mengarahkan tamu kepada Anda.
                    </p>
                </div>
            </aside>
        </section>

        <section class="staff-availability-options-grid" aria-label="Pilihan status ketersediaan">
            @foreach($statuses as $key => $status)
                @php($isActive = $availability_status === $key)

                <button
                    type="button"
                    wire:click="updateStatus('{{ $key }}')"
                    wire:loading.attr="disabled"
                    class="staff-availability-option is-{{ $status['tone'] }} {{ $isActive ? 'is-active' : '' }}"
                >
                    <div class="staff-availability-option-icon">
                        <x-dynamic-component :component="$status['icon']" class="h-7 w-7" />
                    </div>

                    <div class="staff-availability-option-content">
                        <h3 class="staff-availability-option-title">{{ $status['label'] }}</h3>
                        <p class="staff-availability-option-description">{{ $status['description'] }}</p>
                    </div>

                    <span class="staff-availability-option-state">
                        {{ $isActive ? 'Sedang Dipakai' : 'Pilih' }}
                    </span>
                </button>
            @endforeach
        </section>
    </div>
</x-filament-panels::page>
