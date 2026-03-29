<style>
    .um-guide {
        border-radius: 0.9rem;
        border: 1px solid #d5eadf;
        background: linear-gradient(135deg, #f0fdf6 0%, #f8fffc 100%);
        padding: 0.85rem;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .um-guide-grid {
        display: grid;
        gap: 0.65rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .um-guide-item {
        border-radius: 0.75rem;
        border: 1px solid #dbe7f1;
        background: #ffffff;
        padding: 0.7rem;
    }

    .um-guide-step {
        margin: 0;
        font-size: 0.71rem;
        line-height: 1.2;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #0f766e;
    }

    .um-guide-title {
        margin: 0.24rem 0 0;
        font-size: 0.91rem;
        line-height: 1.3;
        font-weight: 700;
        color: #0f172a;
    }

    .um-guide-desc {
        margin: 0.26rem 0 0;
        font-size: 0.79rem;
        line-height: 1.45;
        color: #475569;
    }

    .um-guide-tip {
        margin: 0.64rem 0 0;
        border: 1px dashed #c9d6e3;
        background: #ffffff;
        border-radius: 0.7rem;
        padding: 0.56rem 0.64rem;
        font-size: 0.78rem;
        line-height: 1.45;
        color: #475569;
    }

    @media (max-width: 1024px) {
        .um-guide-grid {
            grid-template-columns: 1fr;
        }
    }

    .dark .um-guide {
        border-color: #14532d;
        background: linear-gradient(135deg, #052e1f 0%, #08352b 100%);
    }

    .dark .um-guide-item {
        border-color: #374151;
        background: #111827;
    }

    .dark .um-guide-step {
        color: #6ee7b7;
    }

    .dark .um-guide-title {
        color: #f8fafc;
    }

    .dark .um-guide-desc,
    .dark .um-guide-tip {
        color: #cbd5e1;
    }

    .dark .um-guide-tip {
        border-color: #475569;
        background: #111827;
    }
</style>

<div class="um-guide">
    <div class="um-guide-grid">
        <article class="um-guide-item">
            <p class="um-guide-step">Langkah 1</p>
            <h4 class="um-guide-title">Lengkapi Identitas User</h4>
            <p class="um-guide-desc">Isi nama lengkap dan email aktif agar akun mudah dikenali dan valid untuk login.</p>
        </article>

        <article class="um-guide-item">
            <p class="um-guide-step">Langkah 2</p>
            <h4 class="um-guide-title">Tentukan Role Akses</h4>
            <p class="um-guide-desc">Pilih role sesuai tugas. Hak akses menu, status, dan fitur akan mengikuti role tersebut.</p>
        </article>

        <article class="um-guide-item">
            <p class="um-guide-step">Langkah 3</p>
            <h4 class="um-guide-title">Atur Keamanan Akun</h4>
            <p class="um-guide-desc">Buat password yang kuat. Konfirmasi password wajib sama agar akun tidak gagal login.</p>
        </article>
    </div>

    <p class="um-guide-tip">
        Tips: gunakan format email institusi dan role paling minimum yang dibutuhkan untuk menjaga keamanan akses aplikasi.
    </p>
</div>
