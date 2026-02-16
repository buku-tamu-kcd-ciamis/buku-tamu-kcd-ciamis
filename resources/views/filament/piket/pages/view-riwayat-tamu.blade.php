<x-filament-panels::page>
    @php
        $tamu = $this->getTamu();
        $allKunjungan = $this->getAllKunjungan();
        $totalKunjungan = $allKunjungan->count();

        $phone = $tamu->nomor_hp;
        $formattedPhone = '-';
        if ($phone) {
            $cleaned = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($cleaned, '0')) {
                $cleaned = substr($cleaned, 1);
            }
            $formattedPhone = '+62 ' . $cleaned;
        }

        $statusCounts = $allKunjungan->groupBy('status')->map->count();

        $statusConfig = [
            'menunggu'   => ['badge' => 'bg-yellow-500 text-white', 'num_bg' => 'bg-yellow-500 text-white shadow-lg', 'num_idle' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/30 dark:text-yellow-300', 'border_active' => 'border-yellow-400 dark:border-yellow-500', 'accent' => 'border-yellow-500'],
            'diproses'   => ['badge' => 'bg-blue-500 text-white', 'num_bg' => 'bg-blue-500 text-white shadow-lg', 'num_idle' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/30 dark:text-blue-300', 'border_active' => 'border-blue-400 dark:border-blue-500', 'accent' => 'border-blue-500'],
            'selesai'    => ['badge' => 'bg-green-500 text-white', 'num_bg' => 'bg-green-500 text-white shadow-lg', 'num_idle' => 'bg-green-100 text-green-700 dark:bg-green-500/30 dark:text-green-300', 'border_active' => 'border-green-400 dark:border-green-500', 'accent' => 'border-green-500'],
            'ditolak'    => ['badge' => 'bg-red-500 text-white', 'num_bg' => 'bg-red-500 text-white shadow-lg', 'num_idle' => 'bg-red-100 text-red-700 dark:bg-red-500/30 dark:text-red-300', 'border_active' => 'border-red-400 dark:border-red-500', 'accent' => 'border-red-500'],
            'dibatalkan' => ['badge' => 'bg-red-500 text-white', 'num_bg' => 'bg-red-500 text-white shadow-lg', 'num_idle' => 'bg-red-100 text-red-700 dark:bg-red-500/30 dark:text-red-300', 'border_active' => 'border-red-400 dark:border-red-500', 'accent' => 'border-red-500'],
        ];
        $statusLabels = \App\Models\BukuTamu::STATUS_LABELS;
    @endphp

    <div class="space-y-6">

        {{-- ===== PROFIL PENGUNJUNG ===== --}}
        <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
            {{-- Header --}}
            <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-500/10 dark:bg-primary-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Informasi Pengunjung</h3>
                </div>
            </div>

            <div class="p-6 space-y-6">
                {{-- Nama Pengunjung --}}
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-2">Nama Pengunjung</p>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $tamu->nama_lengkap }}</h2>
                    </div>
                </div>

                {{-- Data Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Left Column --}}
                    <div class="space-y-4">
                        {{-- Jenis ID & Nomor --}}
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-2">{{ $tamu->jenis_id ?? 'KTP' }}</p>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg>
                                <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $tamu->nik }}</span>
                            </div>
                        </div>

                        {{-- Jabatan --}}
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-2">Jabatan</p>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $tamu->jabatan ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Instansi --}}
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-2">Instansi</p>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $tamu->instansi ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="space-y-4">
                        {{-- No. HP --}}
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-2">No. HP</p>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $formattedPhone }}</span>
                            </div>
                        </div>

                        {{-- Kabupaten/Kota --}}
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-2">Kabupaten/Kota</p>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $tamu->kabupaten_kota ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Email --}}
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-2">Email</p>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="text-base font-semibold text-gray-900 dark:text-white break-all">{{ $tamu->email ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== STATISTIK RINGKAS ===== --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- Total --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-500/10 dark:bg-primary-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $totalKunjungan }}</p>
                        <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</p>
                    </div>
                </div>
            </div>
            {{-- Menunggu --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $statusCounts->get('menunggu', 0) }}</p>
                        <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Menunggu</p>
                    </div>
                </div>
            </div>
            {{-- Diproses --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $statusCounts->get('diproses', 0) }}</p>
                        <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diproses</p>
                    </div>
                </div>
            </div>
            {{-- Selesai --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $statusCounts->get('selesai', 0) }}</p>
                        <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Selesai</p>
                    </div>
                </div>
            </div>
            {{-- Ditolak --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-500/10 dark:bg-red-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $statusCounts->get('ditolak', 0) + $statusCounts->get('dibatalkan', 0) }}</p>
                        <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ditolak</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== RIWAYAT KUNJUNGAN (Accordion Style) ===== --}}
        @php
            $paginatedKunjungan = $this->getKunjunganPaginated();
        @endphp
        <div x-data="{ openItem: 0 }" class="space-y-4">
            @foreach($paginatedKunjungan as $index => $item)
            @php
                $globalIndex = ($paginatedKunjungan->currentPage() - 1) * $paginatedKunjungan->perPage() + $index;
                $sts = $statusConfig[$item->status] ?? $statusConfig['dibatalkan'];
            @endphp
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
            >
                <div class="relative rounded-2xl border-2 bg-white dark:bg-gray-800 transition-all duration-300 overflow-hidden"
                     :class="openItem === {{ $index }} ? '{{ $sts['border_active'] }} shadow-xl' : 'border-gray-200 dark:border-gray-700 shadow-md hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-600'">

                    {{-- Accordion Header (Button) --}}
                    <button
                        @click="openItem = (openItem === {{ $index }} ? null : {{ $index }})"
                        class="relative z-10 w-full flex items-center justify-between px-6 py-5 text-left hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all duration-200"
                    >
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            {{-- Numbered circle --}}
                            <div class="flex-shrink-0">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-base transition-all duration-300"
                                     :class="openItem === {{ $index }} ? '{{ $sts['num_bg'] }}' : '{{ $sts['num_idle'] }}'">
                                    {{ $globalIndex + 1 }}
                                </div>
                            </div>
                            {{-- Title: Keperluan + Date --}}
                            <div class="flex-1 min-w-0">
                                <span class="font-semibold text-gray-900 dark:text-white text-base leading-relaxed block truncate">
                                    {{ $item->keperluan }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 block">
                                    {{ $item->created_at->translatedFormat('d F Y, H:i') }}
                                    &middot; {{ $item->created_at->diffForHumans() }}
                                </span>
                            </div>
                            {{-- Status Badge --}}
                            <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap {{ $sts['badge'] }} shadow-sm">
                                {{ $statusLabels[$item->status] ?? ucfirst($item->status) }}
                            </span>
                        </div>
                        {{-- Chevron --}}
                        <div class="flex-shrink-0 ml-4">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-300"
                                 :class="openItem === {{ $index }} ? 'bg-primary-100 dark:bg-primary-900/40 ring-2 ring-primary-200 dark:ring-primary-800' : 'bg-gray-100 dark:bg-gray-700'">
                                <svg class="w-5 h-5 transform transition-all duration-300"
                                     :class="openItem === {{ $index }} ? 'rotate-180 text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                    </button>

                    {{-- Accordion Content --}}
                    <div x-show="openItem === {{ $index }}"
                         x-collapse
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <div class="relative px-6 pb-6 pt-2">
                            <div class="pl-14 pr-4">
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-6 border-l-4 {{ $sts['accent'] }}">

                                    {{-- Mobile status badge --}}
                                    <div class="sm:hidden mb-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $sts['badge'] }} shadow-sm">
                                            {{ $statusLabels[$item->status] ?? ucfirst($item->status) }}
                                        </span>
                                    </div>

                                    {{-- Detail Grid --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Bagian Dituju</p>
                                            <div class="flex items-center gap-2 text-sm font-medium text-gray-900 dark:text-white">
                                                <svg class="w-4 h-4 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                {{ $item->bagian_dituju }}
                                            </div>
                                        </div>
                                        @if($item->kabupaten_kota)
                                        <div>
                                            <p class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Kabupaten / Kota</p>
                                            <div class="flex items-center gap-2 text-sm font-medium text-gray-900 dark:text-white">
                                                <svg class="w-4 h-4 text-rose-500 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                {{ $item->kabupaten_kota }}
                                            </div>
                                        </div>
                                        @endif
                                        @if($item->nama_penerima)
                                        <div>
                                            <p class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Penerima</p>
                                            <div class="flex items-center gap-2 text-sm font-medium text-gray-900 dark:text-white">
                                                <svg class="w-4 h-4 text-violet-500 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                {{ $item->nama_penerima }}
                                            </div>
                                        </div>
                                        @endif
                                        <div>
                                            <p class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Status</p>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $sts['badge'] }}">
                                                {{ $statusLabels[$item->status] ?? ucfirst($item->status) }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Catatan --}}
                                    @if($item->catatan)
                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                            <div>
                                                <p class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Catatan</p>
                                                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $item->catatan }}</p>
                                            </div>
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

            {{-- Pagination --}}
            @if($paginatedKunjungan->hasPages())
            <div class="pt-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Menampilkan <span class="font-semibold text-gray-900 dark:text-white">{{ $paginatedKunjungan->firstItem() }}</span>
                        - <span class="font-semibold text-gray-900 dark:text-white">{{ $paginatedKunjungan->lastItem() }}</span>
                        dari <span class="font-semibold text-gray-900 dark:text-white">{{ $paginatedKunjungan->total() }}</span> kunjungan
                    </p>
                    <div class="flex items-center gap-2">
                        {{-- Previous --}}
                        @if($paginatedKunjungan->onFirstPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                        @else
                        <button wire:click="previousPage" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        @endif

                        {{-- Page numbers --}}
                        @foreach($paginatedKunjungan->getUrlRange(1, $paginatedKunjungan->lastPage()) as $pageNum => $url)
                        <button wire:click="gotoPage({{ $pageNum }})" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm font-bold transition-colors shadow-sm {{ $pageNum == $paginatedKunjungan->currentPage() ? 'bg-primary-500 text-white' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            {{ $pageNum }}
                        </button>
                        @endforeach

                        {{-- Next --}}
                        @if($paginatedKunjungan->hasMorePages())
                        <button wire:click="nextPage" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                        @else
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>
</x-filament-panels::page>
