<x-filament-panels::page>
    @php
        /** @var \App\Models\ProfileChangeRequest $record */
        $record = $this->getRecord();

        $statusLabel = \App\Models\ProfileChangeRequest::STATUS_LABELS[$record->status] ?? ucfirst((string) $record->status);
        $statusColor = \App\Models\ProfileChangeRequest::STATUS_COLORS[$record->status] ?? 'gray';

        $statusClass = match ($statusColor) {
            'success' => 'vp-pill-success',
            'danger' => 'vp-pill-danger',
            'warning' => 'vp-pill-warning',
            default => 'vp-pill-muted',
        };

        $changedFields = $record->getChangedFields();
        $changedCount = count($changedFields);
    @endphp

    <style>
        .vp-page {
            display: grid;
            gap: 1rem;
        }

        .vp-hero {
            border-radius: 1rem;
            border: 1px solid #d4d4d8;
            background:
                radial-gradient(circle at top right, rgba(0, 0, 0, 0.08), transparent 45%),
                linear-gradient(135deg, #fafafa 0%, #f4f4f5 100%);
            padding: 1rem 1.1rem;
            display: grid;
            gap: 0.65rem;
        }

        .vp-title {
            margin: 0;
            font-size: 1.1rem;
            line-height: 1.2;
            font-weight: 800;
            color: #18181b;
        }

        .vp-subtitle {
            margin: 0;
            color: #52525b;
            font-size: 0.86rem;
        }

        .vp-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .vp-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            border: 1px solid #d4d4d8;
            background: #ffffff;
            color: #27272a;
            padding: 0.25rem 0.65rem;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .vp-pill-success {
            border-color: #a1a1aa;
            background: #f4f4f5;
            color: #27272a;
        }

        .vp-pill-danger {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .vp-pill-warning {
            border-color: #fde68a;
            background: #fffbeb;
            color: #854d0e;
        }

        .vp-pill-muted {
            border-color: #d1d5db;
            background: #f3f4f6;
            color: #374151;
        }

        .vp-content {
            border-radius: 0.9rem;
            border: 1px solid #d4d4d8;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .vp-content-head {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e4e4e7;
            background: linear-gradient(180deg, #fafafa 0%, #f4f4f5 100%);
        }

        .vp-content-title {
            margin: 0;
            font-size: 0.96rem;
            font-weight: 700;
            color: #18181b;
        }

        .vp-content-body {
            padding: 0.95rem;
        }

        .vp-content-body :where(.fi-badge) {
            white-space: normal;
            line-height: 1.3;
            text-align: left;
        }

        .dark .vp-hero {
            border-color: #3f3f46;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.06), transparent 45%),
                linear-gradient(135deg, #0f0f10 0%, #18181b 100%);
        }

        .dark .vp-title {
            color: #f4f4f5;
        }

        .dark .vp-subtitle {
            color: #a1a1aa;
        }

        .dark .vp-pill {
            border-color: #52525b;
            background: #18181b;
            color: #e4e4e7;
        }

        .dark .vp-pill-success {
            border-color: #52525b;
            background: #27272a;
            color: #f4f4f5;
        }

        .dark .vp-pill-danger {
            border-color: #7f1d1d;
            background: #450a0a;
            color: #fecaca;
        }

        .dark .vp-pill-warning {
            border-color: #78350f;
            background: #451a03;
            color: #fde68a;
        }

        .dark .vp-pill-muted {
            border-color: #52525b;
            background: #18181b;
            color: #d4d4d8;
        }

        .dark .vp-content {
            border-color: #3f3f46;
            background: #111113;
            box-shadow: none;
        }

        .dark .vp-content-head {
            border-bottom-color: #3f3f46;
            background: linear-gradient(180deg, #18181b 0%, #111113 100%);
        }

        .dark .vp-content-title {
            color: #f3f4f6;
        }
    </style>

    <div class="vp-page">
        <section class="vp-hero">
            <h2 class="vp-title">Detail Verifikasi Profil</h2>
            <p class="vp-subtitle">Ringkasan pengajuan perubahan profil Kepala Cabang Dinas dan status proses verifikasinya.</p>

            <div class="vp-meta">
                <span class="vp-pill">Pengaju: {{ $record->user?->name ?? '-' }}</span>
                <span class="vp-pill {{ $statusClass }}">Status: {{ $statusLabel }}</span>
                <span class="vp-pill">Perubahan Field: {{ $changedCount }}</span>
                <span class="vp-pill">Tanggal Pengajuan: {{ $record->created_at?->format('d/m/Y') ?? '-' }}</span>
            </div>
        </section>

        <section class="vp-content">
            <div class="vp-content-head">
                <h3 class="vp-content-title">Informasi Detail Pengajuan</h3>
            </div>
            <div class="vp-content-body">
                {{ $this->infolist }}
            </div>
        </section>
    </div>
</x-filament-panels::page>
