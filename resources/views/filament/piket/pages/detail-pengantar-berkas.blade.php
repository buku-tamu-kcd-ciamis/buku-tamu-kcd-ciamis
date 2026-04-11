<div class="vrt-wrapper" style="padding: 0;">
    {{-- Header: Foto + Info Utama --}}
    <div class="vrt-card" style="display: flex; gap: 1.5rem; align-items: flex-start; flex-wrap: wrap;">
        @if($record->foto_selfie_url)
            <div style="flex-shrink: 0; width: 8rem; height: 8rem; border-radius: 0.75rem; overflow: hidden; border: 2px solid var(--vrt-border); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <img src="{{ $record->foto_selfie_url }}" alt="Foto Selfie" style="width: 100%; height: 100%; object-fit: cover;" />
            </div>
        @endif
        <div style="flex: 1; min-width: 250px;">
            <p class="vrt-overline" style="margin-bottom: 0.25rem;">Pengantar Berkas</p>
            <h2 class="vrt-title" style="font-size: 1.5rem; margin-bottom: 1rem;">{{ $record->nama_lengkap }}</h2>
            
            <div class="vrt-profile-grid">
                <div class="vrt-info-item">
                    <p class="vrt-label" style="display: flex; align-items: center; gap: 0.25rem;">
                        <svg style="width: 1rem; height: 1rem; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg>
                        Jenis ID
                    </p>
                    <p class="vrt-value">{{ $record->jenis_id ?? '-' }}</p>
                </div>
                <div class="vrt-info-item">
                    <p class="vrt-label">Nomor ID</p>
                    <p class="vrt-value">{{ $record->nik }}</p>
                </div>
                <div class="vrt-info-item">
                    <p class="vrt-label" style="display: flex; align-items: center; gap: 0.25rem;">
                        <svg style="width: 1rem; height: 1rem; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Instansi
                    </p>
                    <p class="vrt-value">{{ $record->instansi ?? '-' }}</p>
                </div>
                <div class="vrt-info-item">
                    <p class="vrt-label">Jabatan</p>
                    <p class="vrt-value">{{ $record->jabatan ?? '-' }}</p>
                </div>
                <div class="vrt-info-item">
                    <p class="vrt-label" style="display: flex; align-items: center; gap: 0.25rem;">
                        <svg style="width: 1rem; height: 1rem; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        No. HP
                    </p>
                    <p class="vrt-value">{{ $record->nomor_hp ?? '-' }}</p>
                </div>
                <div class="vrt-info-item">
                    <p class="vrt-label">Email</p>
                    <p class="vrt-value">{{ $record->email ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Informasi Kunjungan --}}
    <div class="vrt-card" style="margin-top: 1.5rem;">
        <h4 class="vrt-title" style="font-size: 1.1rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg style="width: 1.25rem; height: 1.25rem; color: var(--primary-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Status & Keperluan
        </h4>
        <div class="vrt-profile-grid">
            <div class="vrt-info-item">
                <p class="vrt-label">Status Kunjungan</p>
                <div style="margin-top: 0.25rem;">
                    @php
                        $statusConfig = [
                            'menunggu'   => ['badge' => 'vrt-badge-yellow', 'dot' => 'vrt-dot-yellow'],
                            'diproses'   => ['badge' => 'vrt-badge-blue', 'dot' => 'vrt-dot-blue'],
                            'selesai'    => ['badge' => 'vrt-badge-green', 'dot' => 'vrt-dot-green'],
                            'ditolak'    => ['badge' => 'vrt-badge-red', 'dot' => 'vrt-dot-red'],
                            'dibatalkan' => ['badge' => 'vrt-badge-red', 'dot' => 'vrt-dot-red'],
                        ];
                        $cfg = $statusConfig[$record->status] ?? $statusConfig['dibatalkan'];
                        $labels = \App\Models\BukuTamu::STATUS_LABELS;
                    @endphp
                    <span class="vrt-badge {{ $cfg['badge'] }}" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">
                        <span class="vrt-dot {{ $cfg['dot'] }}"></span>
                        {{ $labels[$record->status] ?? ucfirst($record->status) }}
                    </span>
                </div>
            </div>
            <div class="vrt-info-item">
                <p class="vrt-label">Tanggal Masuk</p>
                <p class="vrt-value" style="font-weight: 600;">{{ $record->created_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="vrt-info-item">
                <p class="vrt-label" style="display: flex; align-items: center; gap: 0.25rem;">
                    <svg style="width: 1rem; height: 1rem; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Staff Yang Dituju
                </p>
                <p class="vrt-value" style="color: var(--primary-600); font-weight: 700;">{{ $record->staff_dituju ?? '-' }}</p>
            </div>
            <div class="vrt-info-item">
                <p class="vrt-label">Kabupaten / Kota</p>
                <p class="vrt-value">{{ $record->kabupaten_kota ?? '-' }}</p>
            </div>
            <div class="vrt-info-item vrt-col-span-2">
                <p class="vrt-label">Keperluan</p>
                <p class="vrt-value" style="background: rgba(var(--primary-500), 0.05); padding: 0.75rem; border-radius: 0.5rem; border: 1px solid rgba(var(--primary-500), 0.1); margin-top: 0.25rem; font-weight: 500;">{{ $record->keperluan }}</p>
            </div>
            <div class="vrt-info-item">
                <p class="vrt-label">Nama Penerima Berkas</p>
                <p class="vrt-value">
                    @if($record->nama_penerima)
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 0.375rem; border: 1px solid #e5e7eb; font-size: 0.85rem;">
                            <svg style="width: 0.875rem; height: 0.875rem; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ $record->nama_penerima }}
                        </span>
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="vrt-info-item">
                <p class="vrt-label">Catatan</p>
                <p class="vrt-value">{{ $record->catatan ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Dokumen --}}
    @if($record->foto_penerimaan_url || $record->tanda_tangan_url)
    <div class="vrt-card" style="margin-top: 1.5rem; background: linear-gradient(to right, #f8fafc, #ffffff);">
        <h4 class="vrt-title" style="font-size: 1.1rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg style="width: 1.25rem; height: 1.25rem; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Dokumen & Bukti
        </h4>
        <div class="vrt-profile-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            @if($record->foto_penerimaan_url)
            <div class="vrt-info-item">
                <p class="vrt-label" style="text-align: center; margin-bottom: 0.75rem;">Foto Penerimaan Berkas</p>
                <div style="width: 100%; aspect-ratio: 4/3; border-radius: 0.75rem; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1); background: white;">
                    <img src="{{ $record->foto_penerimaan_url }}" alt="Foto Penerimaan" style="width: 100%; height: 100%; object-fit: contain; padding: 0.5rem;" />
                </div>
            </div>
            @endif
            @if($record->tanda_tangan_url)
            <div class="vrt-info-item">
                <p class="vrt-label" style="text-align: center; margin-bottom: 0.75rem;">Tanda Tangan</p>
                <div style="width: 100%; aspect-ratio: 4/3; border-radius: 0.75rem; overflow: hidden; border: 1px dashed #cbd5e1; background: white; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ $record->tanda_tangan_url }}" alt="Tanda Tangan" style="max-width: 90%; max-height: 90%; object-fit: contain;" />
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

</div>