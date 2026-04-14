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
            --do-hero-border: #d4d4d8;
            --do-hero-bg-1: rgba(113, 113, 122, 0.14);
            --do-hero-bg-2: #f8f8f8;
            --do-hero-bg-3: #f2f2f2;
            --do-title: #18181b;
            --do-subtitle: #52525b;
            --do-pill-border: #d4d4d8;
            --do-pill-bg: #ffffff;
            --do-pill-text: #27272a;
            --do-pill-success-border: #e4e4e7;
            --do-pill-success-bg: #fafafa;
            --do-pill-success-text: #27272a;
            --do-pill-muted-border: #e4e4e7;
            --do-pill-muted-bg: #f4f4f5;
            --do-pill-muted-text: #3f3f46;
            --do-content-border: #d4d4d8;
            --do-content-bg: #ffffff;
            --do-content-head-border: #e4e4e7;
            --do-content-head-bg-1: #fafafa;
            --do-content-head-bg-2: #f4f4f5;
            --do-content-title: #18181b;
            --do-content-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
            --do-infolist-section-bg: #ffffff;
            --do-infolist-item-border: #e5e7eb;
            --do-infolist-label: #52525b;
            --do-infolist-value: #18181b;

            display: grid;
            gap: 1rem;
        }

        html.dark .do-page {
            --do-hero-border: #3f3f46;
            --do-hero-bg-1: rgba(255, 255, 255, 0.06);
            --do-hero-bg-2: #0b0b0d;
            --do-hero-bg-3: #111113;
            --do-title: #f4f4f5;
            --do-subtitle: #a1a1aa;
            --do-pill-border: #52525b;
            --do-pill-bg: #18181b;
            --do-pill-text: #f4f4f5;
            --do-pill-success-border: #52525b;
            --do-pill-success-bg: #18181b;
            --do-pill-success-text: #f4f4f5;
            --do-pill-muted-border: #3f3f46;
            --do-pill-muted-bg: #111113;
            --do-pill-muted-text: #d4d4d8;
            --do-content-border: #3f3f46;
            --do-content-bg: #111113;
            --do-content-head-border: #3f3f46;
            --do-content-head-bg-1: #18181b;
            --do-content-head-bg-2: #111113;
            --do-content-title: #f4f4f5;
            --do-content-shadow: 0 14px 30px rgba(0, 0, 0, 0.35);
            --do-infolist-section-bg: #111113;
            --do-infolist-item-border: #3f3f46;
            --do-infolist-label: #a1a1aa;
            --do-infolist-value: #f4f4f5;
        }

        .do-hero {
            border-radius: 1rem;
            border: 1px solid var(--do-hero-border);
            background:
                radial-gradient(circle at top right, var(--do-hero-bg-1), transparent 45%),
                linear-gradient(135deg, var(--do-hero-bg-2) 0%, var(--do-hero-bg-3) 100%);
            padding: 1rem 1.1rem;
            display: grid;
            gap: 0.65rem;
        }

        .do-title {
            margin: 0;
            font-size: 1.1rem;
            line-height: 1.2;
            font-weight: 800;
            color: var(--do-title);
        }

        .do-subtitle {
            margin: 0;
            color: var(--do-subtitle);
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
            border: 1px solid var(--do-pill-border);
            background: var(--do-pill-bg);
            color: var(--do-pill-text);
            padding: 0.25rem 0.65rem;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .do-pill-success {
            border-color: var(--do-pill-success-border);
            background: var(--do-pill-success-bg);
            color: var(--do-pill-success-text);
        }

        .do-pill-muted {
            border-color: var(--do-pill-muted-border);
            background: var(--do-pill-muted-bg);
            color: var(--do-pill-muted-text);
        }

        .do-content {
            border-radius: 0.9rem;
            border: 1px solid var(--do-content-border);
            background: var(--do-content-bg);
            box-shadow: var(--do-content-shadow);
            overflow: hidden;
        }

        .do-content-head {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--do-content-head-border);
            background: linear-gradient(180deg, var(--do-content-head-bg-1) 0%, var(--do-content-head-bg-2) 100%);
        }

        .do-content-title {
            margin: 0;
            font-size: 0.96rem;
            font-weight: 700;
            color: var(--do-content-title);
        }

        .do-content-body {
            padding: 0.95rem;
            background: var(--do-content-bg);
        }

        .do-content :where(.fi-section) {
            background: var(--do-infolist-section-bg);
            border-color: var(--do-infolist-item-border);
        }

        .do-content :where(.fi-in) {
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .do-content :where(.fi-in > *) {
            max-width: 100%;
        }

        .do-content :where(.fi-in-entry-item) {
            border-color: var(--do-infolist-item-border);
        }

        .do-content :where(.fi-in-entry-item-label) {
            color: var(--do-infolist-label);
        }

        .do-content :where(.fi-in-entry-item-value) {
            color: var(--do-infolist-value);
        }

        .do-edit-theme-btn {
            background: linear-gradient(135deg, #52525b 0%, #3f3f46 100%) !important;
            border-color: #3f3f46 !important;
            color: #ffffff !important;
            box-shadow: 0 10px 18px -12px rgba(39, 39, 42, 0.85) !important;
            transition: transform 0.16s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .do-edit-theme-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 22px -12px rgba(39, 39, 42, 0.9) !important;
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
