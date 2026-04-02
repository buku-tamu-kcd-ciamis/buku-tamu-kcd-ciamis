<x-filament-panels::page>
    @php
        $rekap = $this->rekap;
        $paginatedRiwayat = $this->getRiwayatPaginated();
        $totalRiwayat = $this->allRiwayat->count();

        $formatPhone = static function (?string $phone): string {
            if (! filled($phone)) {
                return '-';
            }

            $cleaned = preg_replace('/[^0-9]/', '', $phone);

            if (! filled($cleaned)) {
                return '-';
            }

            if (str_starts_with($cleaned, '62')) {
                return '+62' . substr($cleaned, 2);
            }

            if (str_starts_with($cleaned, '0')) {
                return '+62' . substr($cleaned, 1);
            }

            return '+62' . ltrim($cleaned, '0');
        };

        $statusLabels = \App\Models\PegawaiIzin::STATUS_LABELS;
        $jenisIzinLabels = \App\Models\PegawaiIzin::JENIS_IZIN_LABELS;

        $statusClassMap = [
            'menunggu' => 'rk-badge-neutral',
            'disetujui' => 'rk-badge-neutral',
            'aktif' => 'rk-badge-neutral-strong',
            'selesai' => 'rk-badge-neutral',
            'ditolak' => 'rk-badge-neutral',
            'dibatalkan' => 'rk-badge-neutral',
        ];
    @endphp

    <style>
        .rk-page {
            display: grid;
            gap: 1rem;
        }

        .rk-hero {
            border-radius: 1rem;
            border: 1px solid #bfe8da;
            background:
                radial-gradient(circle at top right, rgba(15, 148, 85, 0.16), transparent 45%),
                linear-gradient(135deg, #f0fdf4 0%, #f8fffc 100%);
            padding: 1rem 1.1rem;
            display: grid;
            gap: 0.7rem;
        }

        .rk-title {
            margin: 0;
            font-size: 1.12rem;
            line-height: 1.25;
            font-weight: 800;
            color: #065f46;
        }

        .rk-subtitle {
            margin: 0;
            color: #166534;
            font-size: 0.86rem;
        }

        .rk-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .rk-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            border: 1px solid #a7f3d0;
            background: #ffffff;
            color: #065f46;
            padding: 0.25rem 0.65rem;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .rk-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            align-items: start;
        }

        .rk-card {
            background: #ffffff;
            border: 1px solid #dcebe5;
            border-radius: 0.9rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .rk-card-head {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid #e7eeeb;
            background: linear-gradient(180deg, #f7fffb 0%, #eef8f4 100%);
        }

        .rk-card-kicker {
            margin: 0;
            font-size: 0.68rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 700;
            color: #0f766e;
        }

        .rk-card-title {
            margin: 0.25rem 0 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .rk-card-head-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .rk-page-size {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            color: #64748b;
        }

        .rk-page-size-btn {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #334155;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.22rem 0.52rem;
            line-height: 1;
            transition: all 0.15s ease;
        }

        .rk-page-size-btn:hover {
            border-color: #9ca3af;
            background: #f8fafc;
        }

        .rk-page-size-btn.is-active {
            background: #111827;
            border-color: #111827;
            color: #ffffff;
        }

        .rk-card-body {
            padding: 1rem;
        }

        .rk-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.7rem;
        }

        .rk-field {
            border: 1px solid #e3ece8;
            border-radius: 0.7rem;
            padding: 0.72rem 0.8rem;
            background: #fbfffd;
        }

        .rk-field-label {
            margin: 0;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
            color: #64748b;
        }

        .rk-field-value {
            margin: 0.24rem 0 0;
            color: #111827;
            font-size: 0.93rem;
            font-weight: 600;
            word-break: break-word;
        }

        .rk-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.65rem;
        }

        .rk-stat {
            border: 1px solid #dcebe5;
            border-radius: 0.8rem;
            background: #ffffff;
            padding: 0.8rem;
            min-height: 84px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .rk-stat:nth-child(1) { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); }
        .rk-stat:nth-child(2) { background: linear-gradient(180deg, #ffffff 0%, #eff6ff 100%); }
        .rk-stat:nth-child(3) { background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%); }
        .rk-stat:nth-child(4) { background: linear-gradient(180deg, #ffffff 0%, #fdf4ff 100%); }
        .rk-stat:nth-child(5) { background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%); }

        .rk-stat-label {
            margin: 0;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rk-stat-value {
            margin: 0.25rem 0 0;
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1;
            color: #0f172a;
        }

        .rk-list {
            display: grid;
            gap: 0.75rem;
        }

        .rk-item {
            border: 1px solid #dbe8e2;
            border-radius: 0.75rem;
            overflow: hidden;
            background: #ffffff;
        }

        .rk-item > summary {
            list-style: none;
            cursor: pointer;
            padding: 0.8rem 0.9rem;
            display: flex;
            gap: 0.7rem;
            justify-content: space-between;
            align-items: flex-start;
        }

        .rk-item > summary::-webkit-details-marker {
            display: none;
        }

        .rk-item-main {
            min-width: 0;
        }

        .rk-item-title {
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.3;
            font-weight: 700;
            color: #0f172a;
        }

        .rk-item-subtitle {
            margin: 0.22rem 0 0;
            font-size: 0.8rem;
            color: #6b7280;
        }

        .rk-time-soft {
            color: #6b7280 !important;
            font-weight: 500;
        }

        .rk-item-meta {
            display: flex;
            gap: 0.45rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .rk-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.2rem 0.55rem;
            font-size: 0.72rem;
            font-weight: 700;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .rk-badge-warn { color: #854d0e; background: #fffbeb; border-color: #fde68a; }
        .rk-badge-info { color: #1e40af; background: #eff6ff; border-color: #bfdbfe; }
        .rk-badge-active { color: #14532d; background: #dcfce7; border-color: #86efac; }
        .rk-badge-success { color: #166534; background: #ecfdf5; border-color: #86efac; }
        .rk-badge-danger { color: #991b1b; background: #fef2f2; border-color: #fecaca; }
        .rk-badge-muted { color: #374151; background: #f3f4f6; border-color: #d1d5db; }

        .rk-item-body {
            border-top: 1px solid #e7eeeb;
            background: #fbfffd;
            padding: 0.82rem 0.9rem 0.95rem;
            display: grid;
            gap: 0.75rem;
        }

        .rk-item-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.62rem;
        }

        .rk-note {
            margin: 0;
            font-size: 0.87rem;
            color: #334155;
            background: #ffffff;
            border: 1px solid #e6edf2;
            border-radius: 0.55rem;
            padding: 0.65rem 0.75rem;
            line-height: 1.45;
        }

        .rk-pagination {
            margin-top: 0.8rem;
            padding-top: 0.8rem;
            border-top: 1px solid #e7eeeb;
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            justify-content: space-between;
            align-items: center;
        }

        .rk-page-btns {
            display: flex;
            align-items: center;
            gap: 0.38rem;
        }

        .rk-page-btn {
            border-radius: 0.55rem;
            border: 1px solid #d2e5dc;
            background: #ffffff;
            color: #244737;
            font-size: 0.8rem;
            font-weight: 600;
            min-width: 2rem;
            height: 2rem;
            padding: 0 0.5rem;
            transition: all 0.18s ease;
        }

        .rk-page-btn:hover {
            background: #eef8f2;
            border-color: #b7ddc8;
        }

        .rk-page-btn.is-active {
            background: linear-gradient(135deg, #0f9455 0%, #0b7a46 100%);
            border-color: #0b7a46;
            color: #ffffff;
        }

        .rk-page-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .rk-pagination-meta {
            margin: 0;
            font-size: 0.84rem;
            font-weight: 500;
            color: #475569;
        }

        /* Dark mode adjustments for rekap detail page */
        .dark .rk-hero {
            border-color: #2f2f35;
            background: linear-gradient(135deg, #0b0b0d 0%, #111113 100%);
        }

        .dark .rk-title {
            color: #f3f4f6;
        }

        .dark .rk-subtitle {
            color: #a1a1aa;
        }

        .dark .rk-pill {
            border-color: #3f3f46;
            background: #18181b;
            color: #f3f4f6;
        }

        .dark .rk-card {
            background: #111113;
            border-color: #3f3f46;
            box-shadow: none;
        }

        .dark .rk-card-head {
            border-bottom-color: #3f3f46;
            background: linear-gradient(180deg, #141417 0%, #101013 100%);
        }

        .dark .rk-card-kicker {
            color: #a1a1aa;
        }

        .dark .rk-card-title {
            color: #f3f4f6;
        }

        .dark .rk-page-size {
            color: #a1a1aa;
        }

        .dark .rk-page-size-btn {
            border-color: #52525b;
            background: #18181b;
            color: #e4e4e7;
        }

        .dark .rk-page-size-btn:hover {
            border-color: #71717a;
            background: #27272a;
        }

        .dark .rk-page-size-btn.is-active {
            background: #f3f4f6;
            border-color: #e4e4e7;
            color: #111113;
        }

        .dark .rk-field {
            border-color: #3f3f46;
            background: #0f0f11;
        }

        .dark .rk-field-label,
        .dark .rk-stat-label {
            color: #a1a1aa;
        }

        .dark .rk-field-value,
        .dark .rk-stat-value,
        .dark .rk-item-title {
            color: #f3f4f6;
        }

        .dark .rk-time-soft,
        .dark .rk-item-subtitle,
        .dark .rk-pagination-meta {
            color: #a1a1aa !important;
        }

        .dark .rk-stat {
            border-color: #3f3f46;
            background: #111113;
        }

        .dark .rk-stat:nth-child(1),
        .dark .rk-stat:nth-child(2),
        .dark .rk-stat:nth-child(3),
        .dark .rk-stat:nth-child(4),
        .dark .rk-stat:nth-child(5) {
            background: #111113;
        }

        .dark .rk-item {
            border-color: #3f3f46;
            background: #111113;
        }

        .dark .rk-item-body {
            border-top-color: #3f3f46;
            background: #0f0f11;
        }

        .dark .rk-note {
            color: #e4e4e7;
            background: #111113;
            border-color: #3f3f46;
        }

        .dark .rk-page-btn {
            border-color: #52525b;
            background: #18181b;
            color: #e4e4e7;
        }

        .dark .rk-page-btn:hover {
            background: #27272a;
            border-color: #71717a;
        }

        .dark .rk-page-btn.is-active {
            background: #f3f4f6;
            border-color: #d4d4d8;
            color: #111113;
        }

        .rk-badge-neutral {
            color: #111827;
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .rk-badge-neutral-strong {
            color: #111827;
            background: #e5e7eb;
            border-color: #9ca3af;
        }

        .dark .rk-badge-neutral {
            color: #f4f4f5;
            background: #18181b;
            border-color: #52525b;
        }

        .dark .rk-badge-neutral-strong {
            color: #ffffff;
            background: #27272a;
            border-color: #71717a;
        }

        .dark .rk-badge-warn,
        .dark .rk-badge-info,
        .dark .rk-badge-active,
        .dark .rk-badge-success,
        .dark .rk-badge-danger,
        .dark .rk-badge-muted {
            color: #f4f4f5;
            background: #18181b;
            border-color: #52525b;
        }

        .dark .rk-pagination {
            border-top-color: #3f3f46;
        }

        .rk-span-8 { grid-column: span 8 / span 8; }
        .rk-span-4 { grid-column: span 4 / span 4; }
        .rk-span-12 { grid-column: span 12 / span 12; }

        @media (max-width: 1140px) {
            .rk-span-8,
            .rk-span-4,
            .rk-span-12 {
                grid-column: span 12 / span 12;
            }

            .rk-stats {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .rk-fields,
            .rk-item-grid,
            .rk-stats {
                grid-template-columns: 1fr;
            }

            .rk-item > summary {
                flex-direction: column;
            }

            .rk-item-meta {
                justify-content: flex-start;
            }
        }
    </style>

    <div class="rk-page">
        <section class="rk-hero">
            <h2 class="rk-title">Ringkasan Izin Pegawai</h2>
            <p class="rk-subtitle">Detail rekap izin dengan tampilan terstruktur sesuai tema utama aplikasi.</p>
            <div class="rk-meta">
                <span class="rk-pill">{{ $rekap->nama_pegawai }}</span>
                <span class="rk-pill">NIP: {{ $rekap->nip }}</span>
                <span class="rk-pill">Total Izin: {{ $rekap->total_izin }} kali</span>
                <span class="rk-pill">Total Hari: {{ $rekap->total_hari }} hari</span>
            </div>
        </section>

        <div class="rk-grid">
            <section class="rk-card rk-span-8">
                <div class="rk-card-head">
                    <p class="rk-card-kicker">Profil</p>
                    <h3 class="rk-card-title">Informasi Pegawai</h3>
                </div>
                <div class="rk-card-body">
                    <div class="rk-fields">
                        <div class="rk-field">
                            <p class="rk-field-label">Nama Pegawai</p>
                            <p class="rk-field-value">{{ $rekap->nama_pegawai }}</p>
                        </div>
                        <div class="rk-field">
                            <p class="rk-field-label">NIP</p>
                            <p class="rk-field-value">{{ $rekap->nip }}</p>
                        </div>
                        <div class="rk-field">
                            <p class="rk-field-label">Jabatan</p>
                            <p class="rk-field-value">{{ $rekap->jabatan ?: '-' }}</p>
                        </div>
                        <div class="rk-field">
                            <p class="rk-field-label">Unit Kerja</p>
                            <p class="rk-field-value">{{ $rekap->unit_kerja ?: '-' }}</p>
                        </div>
                        <div class="rk-field">
                            <p class="rk-field-label">Nomor HP</p>
                            <p class="rk-field-value">{{ $formatPhone($rekap->nomor_hp) }}</p>
                        </div>
                        <div class="rk-field">
                            <p class="rk-field-label">Izin Terakhir</p>
                            <p class="rk-field-value rk-time-soft">{{ $rekap->terakhir_izin ? \Carbon\Carbon::parse($rekap->terakhir_izin)->translatedFormat('d F Y') : '-' }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rk-card rk-span-4">
                <div class="rk-card-head">
                    <p class="rk-card-kicker">Statistik</p>
                    <h3 class="rk-card-title">Jenis Izin</h3>
                </div>
                <div class="rk-card-body">
                    <div class="rk-stats">
                        <article class="rk-stat">
                            <p class="rk-stat-label">Sakit</p>
                            <p class="rk-stat-value">{{ (int) $rekap->total_sakit }}</p>
                        </article>
                        <article class="rk-stat">
                            <p class="rk-stat-label">Cuti</p>
                            <p class="rk-stat-value">{{ (int) $rekap->total_cuti }}</p>
                        </article>
                        <article class="rk-stat">
                            <p class="rk-stat-label">Dinas Luar</p>
                            <p class="rk-stat-value">{{ (int) $rekap->total_dinas_luar }}</p>
                        </article>
                        <article class="rk-stat">
                            <p class="rk-stat-label">Izin Pribadi</p>
                            <p class="rk-stat-value">{{ (int) $rekap->total_izin_pribadi }}</p>
                        </article>
                        <article class="rk-stat">
                            <p class="rk-stat-label">Lainnya</p>
                            <p class="rk-stat-value">{{ (int) $rekap->total_lainnya }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="rk-card rk-span-12">
                <div class="rk-card-head">
                    <div class="rk-card-head-row">
                        <div>
                            <p class="rk-card-kicker">Riwayat</p>
                            <h3 class="rk-card-title">Riwayat Izin Pegawai ({{ $totalRiwayat }} data)</h3>
                        </div>

                        <div class="rk-page-size">
                            <span>Per halaman:</span>
                            @foreach([3, 5, 10] as $size)
                                <a href="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}" class="rk-page-size-btn {{ $this->riwayatPerPage === $size ? 'is-active' : '' }}">{{ $size }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="rk-card-body">
                    @if($paginatedRiwayat->isEmpty())
                        <p class="rk-note">Belum ada data riwayat izin.</p>
                    @else
                        <div class="rk-list">
                            @foreach($paginatedRiwayat as $item)
                                @php
                                    $durasi = $item->tanggal_mulai->diffInDays($item->tanggal_selesai) + 1;
                                    $effectiveStatus = $item->status;
                                    if ($item->status === \App\Models\PegawaiIzin::STATUS_AKTIF && $item->tanggal_selesai->lt(now()->startOfDay())) {
                                        $effectiveStatus = \App\Models\PegawaiIzin::STATUS_SELESAI;
                                    }

                                    $statusLabel = $statusLabels[$effectiveStatus] ?? ucfirst((string) $effectiveStatus);
                                    $statusClass = $statusClassMap[$effectiveStatus] ?? 'rk-badge-neutral';
                                    $jenisLabel = $jenisIzinLabels[$item->jenis_izin] ?? ucfirst((string) $item->jenis_izin);
                                @endphp
                                <details class="rk-item" @if($loop->first) open @endif>
                                    <summary>
                                        <div class="rk-item-main">
                                            <p class="rk-item-title">{{ $jenisLabel }}</p>
                                            <p class="rk-item-subtitle">
                                                {{ $item->tanggal_mulai->translatedFormat('d F Y') }} - {{ $item->tanggal_selesai->translatedFormat('d F Y') }}
                                            </p>
                                        </div>
                                        <div class="rk-item-meta">
                                            <span class="rk-badge rk-badge-neutral">{{ $durasi }} hari</span>
                                            <span class="rk-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                        </div>
                                    </summary>

                                    <div class="rk-item-body">
                                        <div class="rk-item-grid">
                                            <div class="rk-field">
                                                <p class="rk-field-label">Tanggal Mulai</p>
                                                <p class="rk-field-value rk-time-soft">{{ $item->tanggal_mulai->translatedFormat('d F Y') }}</p>
                                            </div>
                                            <div class="rk-field">
                                                <p class="rk-field-label">Tanggal Selesai</p>
                                                <p class="rk-field-value rk-time-soft">{{ $item->tanggal_selesai->translatedFormat('d F Y') }}</p>
                                            </div>
                                            <div class="rk-field">
                                                <p class="rk-field-label">Petugas Piket</p>
                                                <p class="rk-field-value">{{ $item->nama_piket ?: '-' }}</p>
                                            </div>
                                            <div class="rk-field">
                                                <p class="rk-field-label">Diverifikasi Oleh</p>
                                                <p class="rk-field-value">{{ $item->diverifikasi_oleh ?: '-' }}</p>
                                            </div>
                                        </div>

                                        <p class="rk-note"><strong>Keterangan:</strong> {{ $item->keterangan ?: '-' }}</p>

                                        @if(filled($item->catatan_verifikasi))
                                            <p class="rk-note"><strong>Catatan Verifikasi:</strong> {{ $item->catatan_verifikasi }}</p>
                                        @endif
                                    </div>
                                </details>
                            @endforeach
                        </div>

                        @if($paginatedRiwayat->hasPages())
                            <div class="rk-pagination">
                                <div class="rk-pagination-meta">
                                    Menampilkan {{ $paginatedRiwayat->firstItem() }} - {{ $paginatedRiwayat->lastItem() }} dari {{ $paginatedRiwayat->total() }} data
                                </div>

                                <div class="rk-page-btns">
                                    <button wire:click="previousPage" class="rk-page-btn" @if($paginatedRiwayat->onFirstPage()) disabled @endif>&lsaquo;</button>
                                    @foreach(range(1, $paginatedRiwayat->lastPage()) as $pageNum)
                                        <button wire:click="gotoPage({{ $pageNum }})" class="rk-page-btn {{ $paginatedRiwayat->currentPage() == $pageNum ? 'is-active' : '' }}">{{ $pageNum }}</button>
                                    @endforeach
                                    <button wire:click="nextPage" class="rk-page-btn" @if(! $paginatedRiwayat->hasMorePages()) disabled @endif>&rsaquo;</button>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>


