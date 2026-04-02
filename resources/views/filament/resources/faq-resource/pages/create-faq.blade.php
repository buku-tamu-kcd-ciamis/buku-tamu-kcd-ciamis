<x-filament-panels::page>
    <style>
        .faq-create-layout {
            display: grid;
            gap: 1rem;
        }

        .faq-create-hero {
            border-radius: 1rem;
            border: 1px solid #d4d4d8;
            background:
                radial-gradient(circle at top right, rgba(24, 24, 27, 0.06), transparent 48%),
                linear-gradient(135deg, #fafafa 0%, #f4f4f5 100%);
            padding: 1rem 1.1rem;
            display: grid;
            gap: 0.7rem;
        }

        .faq-create-title {
            margin: 0;
            font-size: 1.1rem;
            line-height: 1.25;
            font-weight: 800;
            color: #18181b;
        }

        .faq-create-subtitle {
            margin: 0;
            color: #52525b;
            font-size: 0.9rem;
        }

        .faq-create-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .faq-create-badge {
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

        .faq-create-card {
            border-radius: 0.9rem;
            border: 1px solid #d4d4d8;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .faq-create-card-head {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e4e4e7;
            background: linear-gradient(180deg, #fafafa 0%, #f4f4f5 100%);
        }

        .faq-create-card-title {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: #18181b;
        }

        .faq-create-card-body {
            padding: 0.95rem;
        }

        .faq-create-card :where(.fi-section) {
            border-color: #e4e4e7;
            border-radius: 0.8rem;
        }

        .faq-create-card :where(.fi-section-header) {
            background: linear-gradient(180deg, #fafafa 0%, #f4f4f5 100%);
        }

        .faq-create-card :where(.fi-input-wrp:focus-within) {
            border-color: #6b7280;
            box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.18);
        }

        .faq-create-card :where(.fi-select-input) {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dark .faq-create-hero {
            border-color: #3f3f46;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.05), transparent 48%),
                linear-gradient(135deg, #0f0f10 0%, #161618 100%);
        }

        .dark .faq-create-title {
            color: #f3f4f6;
        }

        .dark .faq-create-subtitle {
            color: #a1a1aa;
        }

        .dark .faq-create-badge {
            border-color: #52525b;
            background: #1f1f23;
            color: #f4f4f5;
        }

        .dark .faq-create-card {
            border-color: #3f3f46;
            background: #111113;
            box-shadow: none;
        }

        .dark .faq-create-card-head {
            border-bottom-color: #3f3f46;
            background: linear-gradient(180deg, #18181b 0%, #111113 100%);
        }

        .dark .faq-create-card-title {
            color: #f3f4f6;
        }

        .dark .faq-create-card :where(.fi-section) {
            border-color: #3f3f46;
        }

        .dark .faq-create-card :where(.fi-section-header) {
            background: linear-gradient(180deg, #18181b 0%, #111113 100%);
        }

        .dark .faq-create-card :where(.fi-input-wrp:focus-within) {
            border-color: #a1a1aa;
            box-shadow: 0 0 0 3px rgba(161, 161, 170, 0.22);
        }
    </style>

    <div class="faq-create-layout">
        <section class="faq-create-hero">
            <h2 class="faq-create-title">Tambah FAQ Baru</h2>
            <p class="faq-create-subtitle">Susun pertanyaan dan jawaban yang ringkas agar mudah dipahami pengguna di setiap panel.</p>

            <div class="faq-create-badges">
                <span class="faq-create-badge">Pertanyaan Jelas</span>
                <span class="faq-create-badge">Jawaban Ringkas</span>
                <span class="faq-create-badge">Target Panel</span>
                <span class="faq-create-badge">Urutan Tampil</span>
            </div>
        </section>

        <section class="faq-create-card">
            <div class="faq-create-card-head">
                <h3 class="faq-create-card-title">Form Input FAQ</h3>
            </div>

            <div class="faq-create-card-body">
                {{ $this->content }}
            </div>
        </section>
    </div>
</x-filament-panels::page>
