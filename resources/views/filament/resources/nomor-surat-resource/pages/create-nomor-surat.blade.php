<x-filament-panels::page>
    <style>
        .ns-create-layout {
            display: grid;
            gap: 1rem;
        }

        .ns-create-hero {
            border-radius: 1rem;
            border: 1px solid #d4d4d8;
            background:
                radial-gradient(circle at top right, rgba(24, 24, 27, 0.08), transparent 48%),
                linear-gradient(135deg, #fafafa 0%, #f4f4f5 100%);
            padding: 1rem 1.1rem;
            display: grid;
            gap: 0.7rem;
        }

        .ns-create-title {
            margin: 0;
            font-size: 1.1rem;
            line-height: 1.25;
            font-weight: 800;
            color: #18181b;
        }

        .ns-create-subtitle {
            margin: 0;
            color: #52525b;
            font-size: 0.9rem;
        }

        .ns-create-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .ns-create-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid #d4d4d8;
            background: #ffffff;
            color: #18181b;
            padding: 0.25rem 0.65rem;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .ns-create-card {
            border-radius: 0.9rem;
            border: 1px solid #d4d4d8;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .ns-create-card-head {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e4e4e7;
            background: linear-gradient(180deg, #fafafa 0%, #f4f4f5 100%);
        }

        .ns-create-card-title {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: #18181b;
        }

        .ns-create-card-body {
            padding: 0.95rem;
        }

        .ns-create-card :where(.fi-section) {
            border-color: #e4e4e7;
            border-radius: 0.8rem;
        }

        .ns-create-card :where(.fi-section-header) {
            background: linear-gradient(180deg, #fafafa 0%, #f4f4f5 100%);
        }

        .ns-create-card :where(.fi-input-wrp:focus-within) {
            border-color: #6b7280;
            box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.18);
        }

        .dark .ns-create-hero {
            border-color: #3f3f46;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.06), transparent 48%),
                linear-gradient(135deg, #0f0f10 0%, #161618 100%);
        }

        .dark .ns-create-title {
            color: #f3f4f6;
        }

        .dark .ns-create-subtitle {
            color: #a1a1aa;
        }

        .dark .ns-create-badge {
            border-color: #52525b;
            background: #1f1f23;
            color: #f4f4f5;
        }

        .dark .ns-create-card {
            border-color: #3f3f46;
            background: #111113;
            box-shadow: none;
        }

        .dark .ns-create-card-head {
            border-bottom-color: #3f3f46;
            background: linear-gradient(180deg, #18181b 0%, #111113 100%);
        }

        .dark .ns-create-card-title {
            color: #f3f4f6;
        }

        .dark .ns-create-card :where(.fi-section) {
            border-color: #3f3f46;
        }

        .dark .ns-create-card :where(.fi-section-header) {
            background: linear-gradient(180deg, #18181b 0%, #111113 100%);
        }

        .dark .ns-create-card :where(.fi-input-wrp:focus-within) {
            border-color: #a1a1aa;
            box-shadow: 0 0 0 3px rgba(161, 161, 170, 0.22);
        }
    </style>

    <div class="ns-create-layout">
        <section class="ns-create-hero">
            <h2 class="ns-create-title">Tambah Pengaturan Nomor Surat</h2>
            <p class="ns-create-subtitle">Atur format nomor surat per jenis dokumen agar penomoran otomatis tetap konsisten dan mudah ditelusuri.</p>

            <div class="ns-create-badges">
                <span class="ns-create-badge">Jenis Surat</span>
                <span class="ns-create-badge">Template Nomor</span>
                <span class="ns-create-badge">Kode Surat</span>
                <span class="ns-create-badge">Padding Digit</span>
                <span class="ns-create-badge">Status Aktif</span>
            </div>
        </section>

        <section class="ns-create-card">
            <div class="ns-create-card-head">
                <h3 class="ns-create-card-title">Form Pengaturan Nomor Surat</h3>
            </div>

            <div class="ns-create-card-body">
                {{ $this->content }}
            </div>
        </section>
    </div>
</x-filament-panels::page>
