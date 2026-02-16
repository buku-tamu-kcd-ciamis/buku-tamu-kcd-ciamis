<x-filament-panels::page>
    @php
        $paginatedRiwayat = $this->getRiwayatPaginated();
        $totalRiwayat = $this->allRiwayat->count();

        $jenisIzinConfig = [
            'sakit' => ['badge' => 'bg-red-500 text-white', 'num_bg' => 'bg-red-500 text-white shadow-lg', 'num_idle' => 'bg-red-100 text-red-700 dark:bg-red-500/30 dark:text-red-300', 'border_active' => 'border-red-400 dark:border-red-500'],
            'cuti' => ['badge' => 'bg-blue-500 text-white', 'num_bg' => 'bg-blue-500 text-white shadow-lg', 'num_idle' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/30 dark:text-blue-300', 'border_active' => 'border-blue-400 dark:border-blue-500'],
            'dinas_luar' => ['badge' => 'bg-yellow-500 text-white', 'num_bg' => 'bg-yellow-500 text-white shadow-lg', 'num_idle' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/30 dark:text-yellow-300', 'border_active' => 'border-yellow-400 dark:border-yellow-500'],
            'izin_pribadi' => ['badge' => 'bg-purple-500 text-white', 'num_bg' => 'bg-purple-500 text-white shadow-lg', 'num_idle' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/30 dark:text-purple-300', 'border_active' => 'border-purple-400 dark:border-purple-500'],
            'lainnya' => ['badge' => 'bg-gray-500 text-white', 'num_bg' => 'bg-gray-500 text-white shadow-lg', 'num_idle' => 'bg-gray-100 text-gray-700 dark:bg-gray-500/30 dark:text-gray-300', 'border_active' => 'border-gray-400 dark:border-gray-500'],
        ];
        $jenisIzinLabels = \App\Models\PegawaiIzin::JENIS_IZIN_LABELS;
    @endphp

    {{ $this->rekapInfolist }}

    {{-- ===== RIWAYAT IZIN (ACCORDION STYLE) ===== --}}
    <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm mt-6">
        <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-500/10 dark:bg-primary-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Riwayat Izin</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $totalRiwayat }} data izin keseluruhan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($paginatedRiwayat->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">Belum ada data riwayat izin</p>
                </div>
            @else
                <div x-data="{ openItem: 0 }" class="space-y-4">
                    @foreach($paginatedRiwayat as $index => $item)
                    @php
                        $globalIndex = ($paginatedRiwayat->currentPage() - 1) * $paginatedRiwayat->perPage() + $index;
                        $config = $jenisIzinConfig[$item->jenis_izin] ?? $jenisIzinConfig['lainnya'];
                        $durasi = $item->tanggal_mulai->diffInDays($item->tanggal_selesai) + 1;
                    @endphp
                    <div x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0">
                        <div class="relative rounded-2xl bg-white dark:bg-gray-800 transition-all duration-300 overflow-hidden"
                             :class="openItem === {{ $index }} ? 'shadow-xl' : 'shadow-md hover:shadow-lg'">

                            {{-- Accordion Header --}}
                            <button @click="openItem = (openItem === {{ $index }} ? null : {{ $index }})"
                                    class="relative z-10 w-full flex items-center justify-between px-6 py-5 text-left hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all duration-200">
                                <div class="flex items-center gap-4 flex-1 min-w-0">
                                    {{-- Numbered circle --}}
                                    <div class="flex-shrink-0">
                                        <div class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-base transition-all duration-300"
                                             :class="openItem === {{ $index }} ? '{{ $config['num_bg'] }}' : '{{ $config['num_idle'] }}'">
                                            {{ $globalIndex + 1 }}
                                        </div>
                                    </div>
                                    {{-- Title: Jenis Izin + Tanggal --}}
                                    <div class="flex-1 min-w-0">
                                        <span class="font-semibold text-gray-900 dark:text-white text-base leading-relaxed block truncate">
                                            {{ $jenisIzinLabels[$item->jenis_izin] ?? ucfirst($item->jenis_izin) }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 block">
                                            {{ $item->tanggal_mulai->translatedFormat('d F Y') }} - {{ $item->tanggal_selesai->translatedFormat('d F Y') }}
                                        </span>
                                    </div>
                                    {{-- Status Badge --}}
                                    <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap {{ $config['badge'] }} shadow-sm">
                                        {{ $durasi }} hari
                                    </span>
                                </div>
                                {{-- Chevron --}}
                                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500 transition-transform duration-300 flex-shrink-0 ml-2"
                                     :class="openItem === {{ $index }} ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            {{-- Accordion Content --}}
                            <div x-show="openItem === {{ $index }}"
                                 x-collapse>
                                <div class="p-6 bg-gray-50 dark:bg-gray-900">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        {{-- Left Column --}}
                                        <div class="space-y-4">
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal Mulai</p>
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->tanggal_mulai->translatedFormat('d F Y') }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal Selesai</p>
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->tanggal_selesai->translatedFormat('d F Y') }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</p>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $item->status === 'aktif' ? 'bg-green-500 text-white' : 'bg-gray-500 text-white' }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Right Column --}}
                                        <div class="space-y-4">
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Keterangan</p>
                                                <div class="flex items-start gap-2">
                                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span class="text-sm text-gray-900 dark:text-white">{{ $item->keterangan ?? '-' }}</span>
                                                </div>
                                            </div>
                                            @if($item->nama_piket)
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Petugas Piket</p>
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->nama_piket }}</span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($paginatedRiwayat->hasPages())
                <div class="mt-6 pt-6">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        {{-- Info Text --}}
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Menampilkan <span class="font-semibold text-gray-900 dark:text-white">{{ $paginatedRiwayat->firstItem() }}</span>
                            - <span class="font-semibold text-gray-900 dark:text-white">{{ $paginatedRiwayat->lastItem() }}</span>
                            dari <span class="font-semibold text-gray-900 dark:text-white">{{ $paginatedRiwayat->total() }}</span> data izin
                        </div>

                        {{-- Pagination Buttons --}}
                        <div class="flex items-center gap-2">
                            {{-- Previous Button --}}
                            <button wire:click="previousPage" 
                                    @if($paginatedRiwayat->onFirstPage()) disabled @endif
                                    class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ $paginatedRiwayat->onFirstPage() ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>

                            {{-- Page Numbers --}}
                            @foreach(range(1, $paginatedRiwayat->lastPage()) as $pageNum)
                                <button wire:click="gotoPage({{ $pageNum }})"
                                        class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors {{ $paginatedRiwayat->currentPage() == $pageNum ? 'bg-primary-600 text-white dark:bg-primary-500' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600' }}">
                                    {{ $pageNum }}
                                </button>
                            @endforeach

                            {{-- Next Button --}}
                            <button wire:click="nextPage"
                                    @if(!$paginatedRiwayat->hasMorePages()) disabled @endif
                                    class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ !$paginatedRiwayat->hasMorePages() ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>
</x-filament-panels::page>
