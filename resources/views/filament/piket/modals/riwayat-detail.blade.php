<div class="space-y-4">
    {{-- Info Pengunjung --}}
    <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600">
        {{-- Header dengan nama --}}
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 dark:from-primary-500 dark:to-primary-600 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-bold text-lg">
                    {{ strtoupper(substr($tamu->nama_lengkap, 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">{{ $tamu->nama_lengkap }}</h3>
                    <p class="text-xs text-white/70">Pengunjung</p>
                </div>
            </div>
        </div>

        {{-- Detail info - stacked rows --}}
        <div class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/50">
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">NIK</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $tamu->nik }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Instansi</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $tamu->instansi ?? '-' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">No. HP</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        @php
                            $phone = $tamu->nomor_hp;
                            if ($phone) {
                                $cleaned = preg_replace('/[^0-9]/', '', $phone);
                                if (str_starts_with($cleaned, '0')) {
                                    $cleaned = substr($cleaned, 1);
                                }
                                echo '+62' . $cleaned;
                            } else {
                                echo '-';
                            }
                        @endphp
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Kunjungan --}}
    <div class="flex items-center gap-4 p-4 rounded-xl bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-500/10 dark:to-primary-400/10 border border-primary-200 dark:border-primary-500/30">
        <div class="w-11 h-11 rounded-xl bg-primary-500 dark:bg-primary-500 flex items-center justify-center flex-shrink-0 shadow-sm">
            <span class="text-lg font-black text-white">{{ $kunjungan->count() }}</span>
        </div>
        <div>
            <p class="text-sm font-bold text-primary-800 dark:text-primary-200">Total Kunjungan</p>
            <p class="text-xs text-primary-600 dark:text-primary-400">Seluruh riwayat tercatat</p>
        </div>
    </div>

    {{-- Riwayat Kunjungan --}}
    <div>
        <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Riwayat Kunjungan
        </h3>

        <div class="space-y-4 max-h-[400px] overflow-y-auto pr-1">
            @foreach($kunjungan as $index => $item)
            @php
                $statusConfig = [
                    'menunggu' => ['bg' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300', 'dot' => 'bg-amber-500', 'ring' => 'ring-amber-500/30'],
                    'diproses' => ['bg' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300', 'dot' => 'bg-blue-500', 'ring' => 'ring-blue-500/30'],
                    'selesai' => ['bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300', 'dot' => 'bg-emerald-500', 'ring' => 'ring-emerald-500/30'],
                    'ditolak' => ['bg' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300', 'dot' => 'bg-red-500', 'ring' => 'ring-red-500/30'],
                    'dibatalkan' => ['bg' => 'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-300', 'dot' => 'bg-gray-400', 'ring' => 'ring-gray-400/30'],
                ];
                $statusLabels = [
                    'menunggu' => 'Menunggu', 'diproses' => 'Diproses', 'selesai' => 'Selesai',
                    'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan',
                ];
                $cfg = $statusConfig[$item->status] ?? $statusConfig['dibatalkan'];
            @endphp
            <div class="relative flex gap-4">
                {{-- Timeline line --}}
                @if(!$loop->last)
                <div class="absolute left-[13px] top-8 -bottom-4 w-0.5 bg-gray-200 dark:bg-gray-600"></div>
                @endif

                {{-- Timeline dot --}}
                <div class="flex-shrink-0 z-10">
                    <div class="w-7 h-7 rounded-full {{ $cfg['dot'] }} ring-[3px] {{ $cfg['ring'] }} flex items-center justify-center shadow-sm">
                        <span class="text-[10px] font-bold text-white">#{{ $index + 1 }}</span>
                    </div>
                </div>

                {{-- Card --}}
                <div class="flex-1 -mt-0.5">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 p-4 shadow-sm hover:shadow-md transition-shadow">
                        {{-- Header: tanggal + status --}}
                        <div class="flex items-start justify-between gap-2 mb-2.5">
                            <span class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ $item->created_at->format('d M Y, H:i') }}
                                <span class="text-gray-300 dark:text-gray-600 mx-0.5">&middot;</span>
                                {{ $item->created_at->diffForHumans() }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold whitespace-nowrap {{ $cfg['bg'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $cfg['dot'] }}"></span>
                                {{ $statusLabels[$item->status] ?? ucfirst($item->status) }}
                            </span>
                        </div>

                        {{-- Keperluan --}}
                        <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ $item->keperluan }}</p>

                        {{-- Tags --}}
                        <div class="flex flex-wrap gap-1.5">
                            <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-500/20">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                {{ $item->bagian_dituju }}
                            </span>
                            @if($item->nama_penerima)
                            <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-lg bg-violet-50 dark:bg-violet-500/15 text-violet-700 dark:text-violet-300 border border-violet-100 dark:border-violet-500/20">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ $item->nama_penerima }}
                            </span>
                            @endif
                        </div>

                        {{-- Catatan --}}
                        @if($item->catatan)
                        <div class="mt-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
                            <p class="text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2 leading-relaxed">
                                <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                                <span>{{ $item->catatan }}</span>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($hasMore ?? false)
        <div class="mt-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 text-center">
            <p class="text-xs text-blue-700 dark:text-blue-300 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Menampilkan <strong>3 kunjungan terbaru</strong> dari total <strong>{{ $totalKunjungan ?? 0 }} kunjungan</strong></span>
            </p>
        </div>
        @endif
    </div>
</div>
