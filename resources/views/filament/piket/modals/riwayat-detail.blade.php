<div class="space-y-6">
    <!-- Info Pengunjung -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Lengkap</label>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $tamu->nama_lengkap }}</p>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NIK</label>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $tamu->nik }}</p>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Instansi</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $tamu->instansi ?? '-' }}</p>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. HP</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">
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

    <!-- Summary -->
    <div class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-700">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium text-primary-900 dark:text-primary-100">Total Kunjungan</span>
        </div>
        <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $kunjungan->count() }}x</span>
    </div>

    <!-- Riwayat Kunjungan -->
    <div>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Riwayat Kunjungan
        </h3>
        
        <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
            @foreach($kunjungan as $item)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                {{ $item->created_at->format('d/m/Y H:i') }}
                            </span>
                            <span class="text-xs text-gray-400">•</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $item->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                            {{ $item->keperluan }}
                        </p>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600 dark:text-gray-300">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                {{ $item->bagian_dituju }}
                            </span>
                            @if($item->nama_penerima)
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Diterima: {{ $item->nama_penerima }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="ml-3">
                        @php
                            $statusColors = [
                                'menunggu' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                'diproses' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                'selesai' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                'ditolak' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                'dibatalkan' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            ];
                            $statusLabels = [
                                'menunggu' => 'Menunggu',
                                'diproses' => 'Diproses',
                                'selesai' => 'Selesai',
                                'ditolak' => 'Ditolak',
                                'dibatalkan' => 'Dibatalkan',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$item->status] ?? ucfirst($item->status) }}
                        </span>
                    </div>
                </div>
                
                @if($item->catatan)
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400 flex items-start gap-2">
                        <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <span>{{ $item->catatan }}</span>
                    </p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
