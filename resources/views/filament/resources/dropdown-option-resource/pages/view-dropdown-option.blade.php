<x-filament-panels::page>
    @php
        /** @var \App\Models\DropdownOption $record */
        $record = $this->getRecord();
        $categoryLabel = \App\Models\DropdownOption::CATEGORY_LABELS[$record->category] ?? $record->category;
        $statusLabel = $record->is_active ? 'Aktif' : 'Nonaktif';
        $statusClass = $record->is_active ? 'do-pill-success' : 'do-pill-muted';
    @endphp

    <style>
        .do-page {
            display: grid;
            gap: 1rem;
        }

        .do-hero {
            border-radius: 1rem;
            border: 1px solid #bfe8da;
            background:
                radial-gradient(circle at top right, rgba(15, 148, 85, 0.16), transparent 45%),
                linear-gradient(135deg, #f0fdf4 0%, #f8fffc 100%);
            padding: 1rem 1.1rem;
            display: grid;
            gap: 0.65rem;
        }

        .do-title {
            margin: 0;
            font-size: 1.1rem;
            line-height: 1.2;
            font-weight: 800;
            color: #065f46;
        }

        .do-subtitle {
            margin: 0;
            color: #166534;
            font-size: 0.86rem;
        }

        .do-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .do-pill {
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

        .do-pill-success {
            border-color: #86efac;
            background: #ecfdf5;
            color: #166534;
        }

        .do-pill-muted {
            border-color: #d1d5db;
            background: #f3f4f6;
            color: #374151;
        }

        .do-content {
            border-radius: 0.9rem;
            border: 1px solid #dcebe5;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .do-content-head {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e7eeeb;
            background: linear-gradient(180deg, #f7fffb 0%, #eef8f4 100%);
        }

        .do-content-title {
            margin: 0;
            font-size: 0.96rem;
            font-weight: 700;
            color: #0f172a;
        }

        .do-content-body {
            padding: 0.95rem;
        }

        html.dark .do-content {
            border-color: #1f2937;
            background: #0b0b0b;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        html.dark .do-content-head {
            border-bottom-color: #1f2937;
            background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
        }

        html.dark .do-content-title {
            color: #e5e7eb;
        }

        html.dark .do-content-body {
            background: #0b0b0b;
        }

        .do-edit-theme-btn {
            background: linear-gradient(135deg, #0f9455 0%, #0b7a46 100%) !important;
            border-color: #0b7a46 !important;
            color: #ffffff !important;
            box-shadow: 0 10px 18px -12px rgba(11, 122, 70, 0.95) !important;
            transition: transform 0.16s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .do-edit-theme-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 22px -12px rgba(11, 122, 70, 0.95) !important;
            filter: brightness(1.02);
        }

        .do-edit-theme-btn span,
        .do-edit-theme-btn .fi-btn-label,
        .do-edit-theme-btn svg {
            color: #ffffff !important;
        }
    </style>

    <div class="do-page">
        <section class="do-hero">
            <h2 class="do-title">Detail Opsi Dropdown</h2>
            <p class="do-subtitle">Informasi lengkap opsi yang digunakan pada form Buku Tamu.</p>
            <div class="do-meta">
                <span class="do-pill">Kategori: {{ $categoryLabel }}</span>
                <span class="do-pill">Nilai: {{ $record->value }}</span>
                <span class="do-pill">Urutan: #{{ $record->sort_order }}</span>
                <span class="do-pill {{ $statusClass }}">Status: {{ $statusLabel }}</span>
            </div>
        </section>

        <section class="do-content">
            <div class="do-content-head">
                <h3 class="do-content-title">Informasi Detail</h3>
            </div>
            <div class="do-content-body">
                {{ $this->infolist }}
            </div>
        </section>
    </div>
</x-filament-panels::page>
