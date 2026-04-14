<x-filament-panels::page>
    <style>
        .doc-page {
            display: grid;
            gap: 1rem;
        }

        .doc-hero {
            border-radius: 1rem;
            border: 1px solid #d4d4d8;
            background:
                radial-gradient(circle at top right, rgba(17, 24, 39, 0.08), transparent 45%),
                linear-gradient(135deg, #f8f8f8 0%, #f2f2f2 100%);
            padding: 1rem 1.1rem;
            display: grid;
            gap: 0.65rem;
        }

        .doc-title {
            margin: 0;
            font-size: 1.1rem;
            line-height: 1.25;
            font-weight: 800;
            color: #111827;
        }

        .doc-subtitle {
            margin: 0;
            color: #4b5563;
            font-size: 0.86rem;
        }

        .doc-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .doc-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid #d4d4d8;
            background: #ffffff;
            color: #111827;
            padding: 0.25rem 0.65rem;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .doc-card {
            border-radius: 0.9rem;
            border: 1px solid #d4d4d8;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .doc-card-head {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e4e4e7;
            background: linear-gradient(180deg, #fafafa 0%, #f4f4f5 100%);
        }

        .doc-card-title {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: #111827;
        }

        .doc-card-body {
            padding: 0.95rem;
        }

        .doc-card :where(.fi-section) {
            border-color: #e4e4e7;
            border-radius: 0.8rem;
        }

        .doc-card :where(.fi-section-header) {
            background: linear-gradient(180deg, #fafafa 0%, #f4f4f5 100%);
        }

        .doc-card :where(.fi-input-wrp:focus-within) {
            border-color: #6b7280;
            box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.18);
        }

        .dark .doc-hero {
            border-color: #3f3f46;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.06), transparent 45%),
                linear-gradient(135deg, #0b0b0d 0%, #111113 100%);
        }

        .dark .doc-title {
            color: #f3f4f6;
        }

        .dark .doc-subtitle {
            color: #a1a1aa;
        }

        .dark .doc-pill {
            border-color: #52525b;
            background: #18181b;
            color: #f4f4f5;
        }

        .dark .doc-card {
            border-color: #3f3f46;
            background: #111113;
            box-shadow: none;
        }

        .dark .doc-card-head {
            border-bottom-color: #3f3f46;
            background: linear-gradient(180deg, #18181b 0%, #111113 100%);
        }

        .dark .doc-card-title {
            color: #f3f4f6;
        }

        .dark .doc-card :where(.fi-section) {
            border-color: #3f3f46;
        }

        .dark .doc-card :where(.fi-section-header) {
            background: linear-gradient(180deg, #18181b 0%, #111113 100%);
        }

        .dark .doc-card :where(.fi-input-wrp:focus-within) {
            border-color: #a1a1aa;
            box-shadow: 0 0 0 3px rgba(161, 161, 170, 0.22);
        }
    </style>

    <div class="doc-page">
        <section class="doc-hero">
            <h2 class="doc-title">Tambah Opsi Dropdown</h2>
            <p class="doc-subtitle">Buat opsi baru untuk kebutuhan form Buku Tamu dengan pengaturan yang konsisten.</p>
            <div class="doc-pills">
                <span class="doc-pill">Kategori Dinamis</span>
                <span class="doc-pill">Label Otomatis</span>
                <span class="doc-pill">Status Aktif</span>
            </div>
        </section>

        <section class="doc-card">
            <div class="doc-card-head">
                <h3 class="doc-card-title">Form Input Opsi Dropdown</h3>
            </div>
            <div class="doc-card-body">
                {{ $this->content }}
            </div>
        </section>
    </div>
</x-filament-panels::page>
