<x-filament-panels::page>
    <style>
        .doc-page {
            display: grid;
            gap: 1rem;
        }

        .doc-hero {
            border-radius: 1rem;
            border: 1px solid #bfe8da;
            background:
                radial-gradient(circle at top right, rgba(15, 148, 85, 0.16), transparent 45%),
                linear-gradient(135deg, #f0fdf4 0%, #f8fffc 100%);
            padding: 1rem 1.1rem;
            display: grid;
            gap: 0.65rem;
        }

        .doc-title {
            margin: 0;
            font-size: 1.1rem;
            line-height: 1.25;
            font-weight: 800;
            color: #065f46;
        }

        .doc-subtitle {
            margin: 0;
            color: #166534;
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
            border: 1px solid #a7f3d0;
            background: #ffffff;
            color: #065f46;
            padding: 0.25rem 0.65rem;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .doc-card {
            border-radius: 0.9rem;
            border: 1px solid #dcebe5;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .doc-card-head {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e7eeeb;
            background: linear-gradient(180deg, #f7fffb 0%, #eef8f4 100%);
        }

        .doc-card-title {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: #0f172a;
        }

        .doc-card-body {
            padding: 0.95rem;
        }

        .doc-card :where(.fi-section) {
            border-color: #dcebe5;
            border-radius: 0.8rem;
        }

        .doc-card :where(.fi-section-header) {
            background: linear-gradient(180deg, #f7fffb 0%, #eef8f4 100%);
        }

        .doc-card :where(.fi-input-wrp:focus-within) {
            border-color: #0f9455;
            box-shadow: 0 0 0 3px rgba(15, 148, 85, 0.13);
        }
    </style>

    <div class="doc-page">
        <section class="doc-hero">
            <h2 class="doc-title">Tambah Opsi Dropdown</h2>
            <p class="doc-subtitle">Buat opsi baru untuk kebutuhan form Buku Tamu dengan pengaturan yang konsisten.</p>
            <div class="doc-pills">
                <span class="doc-pill">Kategori Dinamis</span>
                <span class="doc-pill">Label & Value</span>
                <span class="doc-pill">Sort Order</span>
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
