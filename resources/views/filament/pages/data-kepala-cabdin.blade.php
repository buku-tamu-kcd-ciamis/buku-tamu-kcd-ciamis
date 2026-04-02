<x-filament-panels::page>
    <style>
        .kcd-preview-shell {
            margin-top: 40px;
            overflow: hidden;
            border-radius: 14px;
            border: 1px solid #d4d4d8;
            background: #fafafa;
        }

        .kcd-preview-header {
            border-bottom: 1px solid #e4e4e7;
            padding: 16px 24px;
        }

        .kcd-preview-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 700;
            color: #18181b;
        }

        .kcd-preview-subtitle {
            margin: 6px 0 0;
            font-size: 0.95rem;
            color: #52525b;
        }

        .kcd-preview-body {
            padding: 28px 24px;
        }

        .kcd-preview-card {
            margin: 0 auto;
            max-width: 420px;
            border-radius: 10px;
            border: 2px dashed #a1a1aa;
            background: #ffffff;
            padding: 24px;
            text-align: center;
            font-family: 'Times New Roman', serif;
            line-height: 1.45;
            color: #18181b;
        }

        .kcd-sign-line {
            margin: 8px auto 0;
            width: 250px;
            height: 2px;
            background: #18181b;
        }

        .kcd-submit-wrap {
            margin-top: 56px;
            padding-top: 24px;
            border-top: 1px solid #d4d4d8;
            display: flex;
            justify-content: flex-end;
        }

        .dark .kcd-preview-shell {
            border-color: rgba(161, 161, 170, 0.28);
            background: rgba(39, 39, 42, 0.38);
        }

        .dark .kcd-preview-header {
            border-bottom-color: rgba(161, 161, 170, 0.22);
        }

        .dark .kcd-preview-title {
            color: #f4f4f5;
        }

        .dark .kcd-preview-subtitle {
            color: #d4d4d8;
        }

        .dark .kcd-preview-card {
            border-color: rgba(161, 161, 170, 0.55);
            background: rgba(24, 24, 27, 0.72);
            color: #f4f4f5;
        }

        .dark .kcd-sign-line {
            background: #e4e4e7;
        }

        .dark .kcd-submit-wrap {
            border-top-color: rgba(148, 163, 184, 0.28);
        }
    </style>

    <form wire:submit="save">
        {{ $this->form }}

        @php
            $nama = trim((string) ($this->data['nama_kepala'] ?? ''));
            $nip = trim((string) ($this->data['nip_kepala'] ?? ''));
            $jabatan = trim((string) ($this->data['jabatan'] ?? ''));
        @endphp

        <section class="kcd-preview-shell">
            <div class="kcd-preview-header">
                <h3 class="kcd-preview-title">Preview Tanda Tangan</h3>
                <p class="kcd-preview-subtitle">Tampilan tanda tangan pada halaman cetak.</p>
            </div>

            <div class="kcd-preview-body">
                <div class="kcd-preview-card">
                    <p style="margin: 0 0 4px; font-size: 1rem;">Ciamis, {{ now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 8px 0 0; font-size: 1rem;">{{ $jabatan !== '' ? $jabatan : 'Kepala Cabang Dinas Pendidikan Wilayah XIII' }},</p>

                    <p style="margin: 64px 0 0; font-size: 1.05rem; font-weight: 700;">{{ $nama !== '' ? $nama : '(...............................................)' }}</p>
                    <div class="kcd-sign-line"></div>
                    <p style="margin: 10px 0 0; font-size: 0.95rem;">{{ $nip !== '' ? 'NIP. ' . $nip : 'NIP. ..............................' }}</p>
                </div>
            </div>
        </section>

        <div class="kcd-submit-wrap">
            <x-filament::button type="submit" icon="heroicon-o-check-circle">
                Simpan Perubahan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
