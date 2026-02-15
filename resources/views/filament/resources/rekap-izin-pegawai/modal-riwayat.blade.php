<div class="space-y-4">
    {{-- Info Pegawai --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Nama</span>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $record->nama_pegawai }}</p>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">NIP</span>
                <p class="font-mono text-gray-900 dark:text-white">{{ $record->nip }}</p>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Jabatan</span>
                <p class="text-gray-900 dark:text-white">{{ $record->jabatan ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Unit Kerja</span>
                <p class="text-gray-900 dark:text-white">{{ $record->unit_kerja ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Statistik Ringkas --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $record->total_izin }}</p>
            <p class="text-xs text-blue-500 dark:text-blue-300">Total Izin</p>
        </div>
        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $record->total_hari }}</p>
            <p class="text-xs text-orange-500 dark:text-orange-300">Total Hari</p>
        </div>
        @if($record->sedang_izin > 0)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">Ya</p>
                <p class="text-xs text-yellow-500 dark:text-yellow-300">Sedang Izin</p>
            </div>
        @else
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">Tidak</p>
                <p class="text-xs text-green-500 dark:text-green-300">Sedang Izin</p>
            </div>
        @endif
    </div>

    {{-- Tabel Riwayat --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">#</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Jenis Izin</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Tanggal Mulai</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Tanggal Selesai</th>
                    <th class="px-3 py-2 text-center font-medium text-gray-600 dark:text-gray-300">Durasi</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Keterangan</th>
                    <th class="px-3 py-2 text-center font-medium text-gray-600 dark:text-gray-300">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($riwayat as $index => $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-3 py-2 text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-3 py-2">
                            @if($item->jenis_izin === 'sakit')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                    {{ \App\Models\PegawaiIzin::JENIS_IZIN_LABELS[$item->jenis_izin] ?? ucfirst($item->jenis_izin) }}
                                </span>
                            @elseif($item->jenis_izin === 'cuti')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ \App\Models\PegawaiIzin::JENIS_IZIN_LABELS[$item->jenis_izin] ?? ucfirst($item->jenis_izin) }}
                                </span>
                            @elseif($item->jenis_izin === 'dinas_luar')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                                    {{ \App\Models\PegawaiIzin::JENIS_IZIN_LABELS[$item->jenis_izin] ?? ucfirst($item->jenis_izin) }}
                                </span>
                            @elseif($item->jenis_izin === 'izin_pribadi')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                    {{ \App\Models\PegawaiIzin::JENIS_IZIN_LABELS[$item->jenis_izin] ?? ucfirst($item->jenis_izin) }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400">
                                    {{ \App\Models\PegawaiIzin::JENIS_IZIN_LABELS[$item->jenis_izin] ?? ucfirst($item->jenis_izin) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $item->tanggal_mulai->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $item->tanggal_selesai->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-center text-gray-900 dark:text-gray-100">
                            {{ $item->tanggal_mulai->diffInDays($item->tanggal_selesai) + 1 }} hari
                        </td>
                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400 max-w-[200px] truncate" title="{{ $item->keterangan }}">
                            {{ $item->keterangan ?? '-' }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $item->status === 'aktif' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}
                            ">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-6 text-center text-gray-500">Tidak ada data riwayat izin.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
