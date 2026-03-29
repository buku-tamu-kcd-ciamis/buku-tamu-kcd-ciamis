<x-filament-panels::page>
    @php
        $tamu = $this->getTamu();
        $allKunjungan = $this->getAllKunjungan();
        $totalKunjungan = $allKunjungan->count();
        $lastVisitAt = $allKunjungan->first()?->created_at;
        $lastTarget = $tamu->staff_dituju ?? $tamu->bagian_dituju ?? '-';

        $phone = (string) ($tamu->nomor_hp ?? '');
        $formattedPhone = '-';
        if ($phone !== '') {
            $cleaned = preg_replace('/[^0-9]/', '', $phone);

            if ($cleaned !== '') {
                if (str_starts_with($cleaned, '62')) {
                    $cleaned = '0' . substr($cleaned, 2);
                } elseif (str_starts_with($cleaned, '8')) {
                    $cleaned = '0' . $cleaned;
                } elseif (! str_starts_with($cleaned, '0')) {
                    $cleaned = '0' . $cleaned;
                }

                $formattedPhone = $cleaned;
            }
        }

        $statusCounts = $allKunjungan->groupBy('status')->map->count();
        $statusLabels = \App\Models\BukuTamu::STATUS_LABELS;
        $statusMap = [
            'menunggu' => 'is-menunggu',
            'diproses' => 'is-diproses',
            'selesai' => 'is-selesai',
            'ditolak' => 'is-ditolak',
            'dibatalkan' => 'is-dibatalkan',
        ];
        $paginatedKunjungan = $this->getKunjunganPaginated();
    @endphp

    <style>
        .rt-shell {
            display: grid;
            gap: 1rem;
        }

        .rt-page {
            display: grid;
            gap: 1rem;
        }

        .rt-hero {
            border-radius: 1rem;
            border: 1px solid #bfe8da;
            background:
                radial-gradient(circle at top right, rgba(16, 185, 129, 0.16), transparent 45%),
                linear-gradient(135deg, #f0fdf4 0%, #f8fffc 100%);
            padding: 1rem 1.1rem;
            display: grid;
            gap: 0.45rem;
        }

        .rt-hero-title {
            margin: 0;
            font-size: 1.12rem;
            line-height: 1.25;
            font-weight: 800;
            color: #065f46;
        }

        .rt-hero-subtitle {
            margin: 0;
            color: #166534;
            font-size: 0.86rem;
        }

        .rt-hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.2rem;
        }

        .rt-hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            border-radius: 999px;
            border: 1px solid #a7f3d0;
            background: #ffffff;
            color: #065f46;
            padding: 0.23rem 0.62rem;
            font-size: 0.77rem;
            font-weight: 700;
        }

        .rt-card {
            background: #ffffff;
            border: 1px solid #dcebe5;
            border-radius: 0.9rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .rt-card-header {
            padding: 0.95rem 1rem;
            border-bottom: 1px solid #e7eeeb;
            background: linear-gradient(180deg, #f7fffb 0%, #eef8f4 100%);
        }

        .rt-kicker {
            display: inline-block;
            font-size: 0.7rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 700;
            color: #0f766e;
        }

        .rt-title {
            margin: 0.25rem 0 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
        }

        .rt-card-body {
            padding: 1rem;
            display: grid;
            gap: 0.9rem;
        }

        .rt-primary-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .rt-muted {
            margin: 0;
            color: #64748b;
            font-size: 0.84rem;
        }

        .rt-profile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.7rem;
        }

        .rt-field {
            border: 1px solid #e3ece8;
            border-radius: 0.7rem;
            padding: 0.7rem 0.8rem;
            background: #fbfffd;
        }

        .rt-field-label {
            margin: 0;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
            color: #64748b;
        }

        .rt-field-value {
            margin: 0.22rem 0 0;
            color: #111827;
            font-size: 0.94rem;
            font-weight: 600;
            word-break: break-word;
        }

        .rt-stats {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 0.65rem;
        }

        .rt-stat {
            border: 1px solid #dcebe5;
            border-radius: 0.8rem;
            background: #ffffff;
            padding: 0.8rem;
        }

        .rt-stat:nth-child(1) {
            background: linear-gradient(180deg, #ffffff 0%, #f8fffb 100%);
        }

        .rt-stat:nth-child(2) {
            background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%);
        }

        .rt-stat:nth-child(3) {
            background: linear-gradient(180deg, #ffffff 0%, #eff6ff 100%);
        }

        .rt-stat:nth-child(4) {
            background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%);
        }

        .rt-stat:nth-child(5) {
            background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%);
        }

        .rt-stat-label {
            margin: 0;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            font-weight: 700;
        }

        .rt-stat-value {
            margin: 0.25rem 0 0;
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
            color: #0f172a;
        }

        .rt-list {
            display: grid;
            gap: 0.75rem;
        }

        .rt-item {
            border: 1px solid #dbe3e8;
            border-radius: 0.75rem;
            background: #ffffff;
            overflow: hidden;
        }

        .rt-item > summary {
            list-style: none;
            cursor: pointer;
            padding: 0.8rem 0.9rem;
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
            justify-content: space-between;
        }

        .rt-item > summary::-webkit-details-marker {
            display: none;
        }

        .rt-item-main {
            min-width: 0;
        }

        .rt-item-title {
            margin: 0;
            font-size: 0.96rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
        }

        .rt-item-date {
            margin: 0.22rem 0 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        .rt-item-body {
            border-top: 1px solid #e8edf1;
            padding: 0.82rem 0.9rem 0.95rem;
            display: grid;
            gap: 0.75rem;
            background: #fbfdff;
        }

        .rt-item-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.65rem;
        }

        .rt-note {
            margin: 0;
            font-size: 0.88rem;
            color: #334155;
            background: #ffffff;
            border: 1px solid #e6edf2;
            border-radius: 0.55rem;
            padding: 0.65rem 0.75rem;
            line-height: 1.45;
        }

        .rt-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.2rem 0.55rem;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .rt-badge.is-menunggu {
            color: #854d0e;
            background: #fffbeb;
            border-color: #fde68a;
        }

        .rt-badge.is-diproses {
            color: #1e40af;
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .rt-badge.is-selesai {
            color: #166534;
            background: #ecfdf5;
            border-color: #86efac;
        }

        .rt-badge.is-ditolak,
        .rt-badge.is-dibatalkan {
            color: #991b1b;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .rt-badge.is-default {
            color: #334155;
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .rt-pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 0.25rem;
        }

        .rt-pagination-meta {
            margin: 0;
            color: #64748b;
            font-size: 0.84rem;
        }

        .rt-pagination-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }

        .rt-page-btn,
        .rt-page-btn-disabled,
        .rt-page-btn-current {
            min-width: 2rem;
            height: 2rem;
            border-radius: 0.55rem;
            border: 1px solid #d4dde4;
            background: #ffffff;
            color: #1f2937;
            font-weight: 700;
            font-size: 0.78rem;
            padding: 0 0.45rem;
        }

        .rt-page-btn {
            cursor: pointer;
        }

        .rt-page-btn:hover {
            background: #f8fafc;
        }

        .rt-page-btn-current {
            border-color: #0ea5e9;
            background: #0ea5e9;
            color: #ffffff;
        }

        .rt-page-btn-disabled {
            background: #f1f5f9;
            color: #94a3b8;
            border-color: #e2e8f0;
            cursor: not-allowed;
        }

        .rt-empty {
            border: 1px dashed #cbd5e1;
            border-radius: 0.8rem;
            padding: 1rem;
            text-align: center;
            color: #64748b;
            background: #f8fafc;
        }

        @media (max-width: 1024px) {
            .rt-stats {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 780px) {
            .rt-profile-grid,
            .rt-item-grid,
            .rt-stats {
                grid-template-columns: 1fr;
            }

            .rt-item > summary {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        .dark .rt-card,
        .dark .rt-stat,
        .dark .rt-item,
        .dark .rt-note,
        .dark .rt-field {
            background: #111827;
            border-color: #374151;
        }

        .dark .rt-hero {
            border-color: #14532d;
            background:
                radial-gradient(circle at top right, rgba(16, 185, 129, 0.2), transparent 45%),
                linear-gradient(135deg, #052e1f 0%, #064e3b 100%);
        }

        .dark .rt-hero-title,
        .dark .rt-hero-subtitle,
        .dark .rt-hero-pill {
            color: #d1fae5;
        }

        .dark .rt-hero-pill {
            border-color: #1f7a5d;
            background: rgba(5, 150, 105, 0.22);
        }

        .dark .rt-card-header,
        .dark .rt-item-body {
            background: #0f172a;
            border-color: #374151;
        }

        .dark .rt-title,
        .dark .rt-primary-name,
        .dark .rt-item-title,
        .dark .rt-field-value,
        .dark .rt-stat-value {
            color: #f9fafb;
        }

        .dark .rt-muted,
        .dark .rt-field-label,
        .dark .rt-item-date,
        .dark .rt-stat-label,
        .dark .rt-pagination-meta {
            color: #9ca3af;
        }

        .dark .rt-page-btn {
            background: #111827;
            border-color: #4b5563;
            color: #e5e7eb;
        }

        .dark .rt-page-btn:hover {
            background: #1f2937;
        }

        .dark .rt-page-btn-disabled {
            background: #1f2937;
            border-color: #374151;
            color: #6b7280;
        }

        .dark .rt-empty {
            background: #111827;
            border-color: #374151;
            color: #9ca3af;
        }
    </style>

    <div class="rt-shell">
        <section class="rt-hero">
            <h2 class="rt-hero-title">Detail Riwayat Pengunjung</h2>
            <p class="rt-hero-subtitle">Ringkasan kunjungan pengunjung untuk membantu pelacakan layanan.</p>
            <div class="rt-hero-meta">
                <span class="rt-hero-pill">NIK: {{ $tamu->nik }}</span>
                <span class="rt-hero-pill">Total: {{ $totalKunjungan }} kunjungan</span>
                <span class="rt-hero-pill">Terakhir: {{ $lastVisitAt?->translatedFormat('d M Y H:i') ?? '-' }}</span>
            </div>
        </section>

        <div class="rt-page">
        <section class="rt-card">
            <div class="rt-card-header">
                <span class="rt-kicker">Data Pengunjung</span>
                <h3 class="rt-title">Informasi Pengunjung</h3>
            </div>
            <div class="rt-card-body">
                <div>
                    <p class="rt-primary-name">{{ $tamu->nama_lengkap }}</p>
                    <p class="rt-muted">NIK: {{ $tamu->nik }}</p>
                </div>

                <div class="rt-profile-grid">
                    <div class="rt-field">
                        <p class="rt-field-label">Jenis Identitas</p>
                        <p class="rt-field-value">{{ $tamu->jenis_id ?? 'KTP' }}</p>
                    </div>
                    <div class="rt-field">
                        <p class="rt-field-label">Jabatan</p>
                        <p class="rt-field-value">{{ $tamu->jabatan ?? '-' }}</p>
                    </div>
                    <div class="rt-field">
                        <p class="rt-field-label">Instansi</p>
                        <p class="rt-field-value">{{ $tamu->instansi ?? '-' }}</p>
                    </div>
                    <div class="rt-field">
                        <p class="rt-field-label">Staff Yang Dituju</p>
                        <p class="rt-field-value">{{ $lastTarget }}</p>
                    </div>
                    <div class="rt-field">
                        <p class="rt-field-label">No. HP</p>
                        <p class="rt-field-value">{{ $formattedPhone }}</p>
                    </div>
                    <div class="rt-field">
                        <p class="rt-field-label">Kabupaten / Kota</p>
                        <p class="rt-field-value">{{ $tamu->kabupaten_kota ?? '-' }}</p>
                    </div>
                    <div class="rt-field">
                        <p class="rt-field-label">Email</p>
                        <p class="rt-field-value">{{ $tamu->email ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rt-stats">
            <article class="rt-stat">
                <p class="rt-stat-label">Total</p>
                <p class="rt-stat-value">{{ $totalKunjungan }}</p>
            </article>
            <article class="rt-stat">
                <p class="rt-stat-label">Menunggu</p>
                <p class="rt-stat-value">{{ $statusCounts->get('menunggu', 0) }}</p>
            </article>
            <article class="rt-stat">
                <p class="rt-stat-label">Diproses</p>
                <p class="rt-stat-value">{{ $statusCounts->get('diproses', 0) }}</p>
            </article>
            <article class="rt-stat">
                <p class="rt-stat-label">Selesai</p>
                <p class="rt-stat-value">{{ $statusCounts->get('selesai', 0) }}</p>
            </article>
            <article class="rt-stat">
                <p class="rt-stat-label">Ditolak / Dibatalkan</p>
                <p class="rt-stat-value">{{ $statusCounts->get('ditolak', 0) + $statusCounts->get('dibatalkan', 0) }}</p>
            </article>
        </section>

        <section class="rt-card">
            <div class="rt-card-header">
                <span class="rt-kicker">Riwayat Pengunjung</span>
                <h3 class="rt-title">Daftar Kunjungan</h3>
            </div>
            <div class="rt-card-body">
                @if($paginatedKunjungan->count() === 0)
                    <div class="rt-empty">Belum ada data kunjungan untuk pengunjung ini.</div>
                @else
                    <div class="rt-list">
                        @foreach($paginatedKunjungan as $index => $item)
                            @php
                                $globalIndex = ($paginatedKunjungan->currentPage() - 1) * $paginatedKunjungan->perPage() + $index + 1;
                                $statusClass = $statusMap[$item->status] ?? 'is-default';
                            @endphp
                            <details class="rt-item" @if($index === 0) open @endif>
                                <summary>
                                    <div class="rt-item-main">
                                        <p class="rt-item-title">{{ $globalIndex }}. {{ $item->keperluan }}</p>
                                        <p class="rt-item-date">{{ $item->created_at->translatedFormat('d F Y, H:i') }} ({{ $item->created_at->diffForHumans() }})</p>
                                    </div>
                                    <span class="rt-badge {{ $statusClass }}">
                                        {{ $statusLabels[$item->status] ?? ucfirst((string) $item->status) }}
                                    </span>
                                </summary>
                                <div class="rt-item-body">
                                    <div class="rt-item-grid">
                                        <div class="rt-field">
                                            <p class="rt-field-label">Staff Yang Dituju</p>
                                            <p class="rt-field-value">{{ $item->staff_dituju ?? $item->bagian_dituju ?? '-' }}</p>
                                        </div>
                                        <div class="rt-field">
                                            <p class="rt-field-label">Kabupaten / Kota</p>
                                            <p class="rt-field-value">{{ $item->kabupaten_kota ?? '-' }}</p>
                                        </div>
                                        <div class="rt-field">
                                            <p class="rt-field-label">Penerima</p>
                                            <p class="rt-field-value">{{ $item->nama_penerima ?? '-' }}</p>
                                        </div>
                                        <div class="rt-field">
                                            <p class="rt-field-label">Status</p>
                                            <p class="rt-field-value">{{ $statusLabels[$item->status] ?? ucfirst((string) $item->status) }}</p>
                                        </div>
                                    </div>

                                    @if($item->catatan)
                                        <p class="rt-note">{{ $item->catatan }}</p>
                                    @endif
                                </div>
                            </details>
                        @endforeach
                    </div>
                @endif

                @if($paginatedKunjungan->hasPages())
                    <div class="rt-pagination">
                        <p class="rt-pagination-meta">
                            Menampilkan {{ $paginatedKunjungan->firstItem() }} - {{ $paginatedKunjungan->lastItem() }} dari {{ $paginatedKunjungan->total() }} kunjungan
                        </p>
                        <div class="rt-pagination-controls">
                            @if($paginatedKunjungan->onFirstPage())
                                <span class="rt-page-btn-disabled">Prev</span>
                            @else
                                <button type="button" wire:click="previousPage" class="rt-page-btn">Prev</button>
                            @endif

                            @foreach($paginatedKunjungan->getUrlRange(1, $paginatedKunjungan->lastPage()) as $pageNum => $url)
                                @if($pageNum == $paginatedKunjungan->currentPage())
                                    <span class="rt-page-btn-current">{{ $pageNum }}</span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $pageNum }})" class="rt-page-btn">{{ $pageNum }}</button>
                                @endif
                            @endforeach

                            @if($paginatedKunjungan->hasMorePages())
                                <button type="button" wire:click="nextPage" class="rt-page-btn">Next</button>
                            @else
                                <span class="rt-page-btn-disabled">Next</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>
        </div>
    </div>
</x-filament-panels::page>
