<div class="space-y-6">
    {{-- Header: Foto + Info Utama --}}
    <div class="flex gap-6">
        @if($record->foto_selfie)
            <div class="shrink-0">
                <img src="{{ $record->foto_selfie }}" alt="Foto Selfie"
                     class="w-32 h-32 rounded-lg object-cover border-2 border-gray-200" />
            </div>
        @endif
        <div class="flex-1 space-y-2">
            <div>
                <span class="text-xs text-gray-500 font-medium">Nama Lengkap</span>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $record->nama_lengkap }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-xs text-gray-500 font-medium">Jenis ID</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <x-heroicon-o-identification class="w-4 h-4 text-gray-400" />
                        {{ $record->jenis_id ?? '-' }}
                    </p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 font-medium">Nomor ID</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <x-heroicon-o-finger-print class="w-4 h-4 text-gray-400" />
                        {{ $record->nik }}
                    </p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 font-medium">Instansi</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <x-heroicon-o-building-office-2 class="w-4 h-4 text-gray-400" />
                        {{ $record->instansi ?? '-' }}
                    </p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 font-medium">Jabatan</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <x-heroicon-o-briefcase class="w-4 h-4 text-gray-400" />
                        {{ $record->jabatan ?? '-' }}
                    </p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 font-medium">No. HP</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <x-heroicon-o-phone class="w-4 h-4 text-gray-400" />
                        {{ $record->nomor_hp ?? '-' }}
                    </p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 font-medium">Email</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <x-heroicon-o-envelope class="w-4 h-4 text-gray-400" />
                        {{ $record->email ?? '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Informasi Kunjungan --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
            <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
            Informasi Pengantar Berkas
        </h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-xs text-gray-500 font-medium">Kabupaten / Kota</span>
                <p class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                    <x-heroicon-o-map-pin class="w-4 h-4 text-gray-400" />
                    {{ $record->kabupaten_kota ?? '-' }}
                </p>
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium">Bagian Yang Dituju</span>
                <p class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                    <x-heroicon-o-building-office class="w-4 h-4 text-gray-400" />
                    {{ $record->bagian_dituju ?? '-' }}
                </p>
            </div>
            <div class="col-span-2">
                <span class="text-xs text-gray-500 font-medium">Keperluan</span>
                <p class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                    <x-heroicon-o-document-text class="w-4 h-4 text-gray-400" />
                    {{ $record->keperluan }}
                </p>
            </div>
            <div class="col-span-2">
                <span class="text-xs text-gray-500 font-medium">Waktu Kunjungan</span>
                <p class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                    <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                    {{ $record->created_at->format('d F Y, H:i:s') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Status --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
            <x-heroicon-o-signal class="w-4 h-4" />
            Status Kunjungan
        </h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-xs text-gray-500 font-medium">Status</span>
                <p class="mt-1">
                    @php
                        $statusColors = [
                            'menunggu' => 'bg-yellow-100 text-yellow-800',
                            'diproses' => 'bg-blue-100 text-blue-800',
                            'selesai' => 'bg-green-100 text-green-800',
                            'ditolak' => 'bg-red-100 text-red-800',
                            'dibatalkan' => 'bg-gray-100 text-gray-800',
                        ];
                        $colorClass = $statusColors[$record->status] ?? 'bg-gray-100 text-gray-800';
                        $labels = \App\Models\BukuTamu::STATUS_LABELS;
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                        {{ $labels[$record->status] ?? ucfirst($record->status) }}
                    </span>
                </p>
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium">Nama Penerima</span>
                <p class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1">
                    <x-heroicon-o-user class="w-4 h-4 text-gray-400" />
                    {{ $record->nama_penerima ?? 'Belum ada penerima' }}
                </p>
            </div>
            <div class="col-span-2">
                <span class="text-xs text-gray-500 font-medium">Catatan</span>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $record->catatan ?? 'Tidak ada catatan' }}</p>
            </div>
        </div>
    </div>

    {{-- Dokumen --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
            <x-heroicon-o-camera class="w-4 h-4" />
            Dokumen
        </h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-xs text-gray-500 font-medium">Foto Penerimaan Berkas</span>
                @if($record->foto_penerimaan)
                    <img src="{{ $record->foto_penerimaan }}" alt="Foto Penerimaan"
                         class="mt-1 w-full max-w-[200px] rounded-lg border border-gray-200 object-cover" />
                @else
                    <p class="text-sm text-gray-400 mt-1">Tidak ada foto</p>
                @endif
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium">Tanda Tangan</span>
                @if($record->tanda_tangan)
                    <img src="{{ $record->tanda_tangan }}" alt="Tanda Tangan"
                         class="mt-1 w-full max-w-[200px] rounded-lg border border-gray-200 bg-white" />
                @else
                    <p class="text-sm text-gray-400 mt-1">Tidak ada tanda tangan</p>
                @endif
            </div>
        </div>
    </div>
</div>
