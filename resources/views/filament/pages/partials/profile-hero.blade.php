<style>
    .pf-hero {
        border-radius: 0.95rem;
        border: 1px solid #bfe6d8;
        background:
            radial-gradient(circle at top right, rgba(16, 185, 129, 0.16), transparent 42%),
            linear-gradient(135deg, #ecfdf5 0%, #f8fffc 100%);
        padding: 0.95rem;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
        display: grid;
        gap: 0.75rem;
    }

    .pf-hero-heading {
        margin: 0;
        font-size: 1rem;
        line-height: 1.35;
        font-weight: 800;
        color: #065f46;
    }

    .pf-hero-subheading {
        margin: 0.24rem 0 0;
        font-size: 0.83rem;
        line-height: 1.45;
        color: #0f766e;
    }

    .pf-grid {
        display: grid;
        gap: 0.62rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .pf-item {
        border-radius: 0.75rem;
        border: 1px solid #d9e5ef;
        background: #ffffff;
        padding: 0.66rem 0.72rem;
    }

    .pf-item-title {
        margin: 0;
        font-size: 0.78rem;
        line-height: 1.3;
        font-weight: 700;
        color: #0f172a;
    }

    .pf-item-desc {
        margin: 0.25rem 0 0;
        font-size: 0.76rem;
        line-height: 1.45;
        color: #475569;
    }

    .pf-note {
        margin: 0;
        border-radius: 0.7rem;
        border: 1px dashed #c8d5e2;
        background: #ffffff;
        padding: 0.58rem 0.68rem;
        font-size: 0.75rem;
        line-height: 1.45;
        color: #475569;
    }

    @media (max-width: 980px) {
        .pf-grid {
            grid-template-columns: 1fr;
        }
    }

    .dark .pf-hero {
        border-color: #14532d;
        background:
            radial-gradient(circle at top right, rgba(16, 185, 129, 0.22), transparent 42%),
            linear-gradient(135deg, #052e1f 0%, #07382c 100%);
    }

    .dark .pf-hero-heading,
    .dark .pf-hero-subheading {
        color: #d1fae5;
    }

    .dark .pf-item {
        border-color: #374151;
        background: #111827;
    }

    .dark .pf-item-title {
        color: #f8fafc;
    }

    .dark .pf-item-desc,
    .dark .pf-note {
        color: #cbd5e1;
    }

    .dark .pf-note {
        border-color: #475569;
        background: #111827;
    }
</style>

<div class="pf-hero">
    <div>
        <h3 class="pf-hero-heading">Pengaturan Profil Akun</h3>
        <p class="pf-hero-subheading">Pastikan data akun selalu akurat dan password diperbarui secara berkala untuk mencegah akses tidak sah.</p>
    </div>

    <div class="pf-grid">
        <article class="pf-item">
            <h4 class="pf-item-title">Identitas Akun</h4>
            <p class="pf-item-desc">Nama dan email digunakan sebagai identitas utama akun Anda di panel admin.</p>
        </article>

        <article class="pf-item">
            <h4 class="pf-item-title">Ganti Password Aman</h4>
            <p class="pf-item-desc">Masukkan password saat ini sebelum mengatur password baru yang lebih kuat.</p>
        </article>

        <article class="pf-item">
            <h4 class="pf-item-title">Verifikasi Login</h4>
            <p class="pf-item-desc">Anda bisa mengaktifkan autentikasi dua faktor untuk lapisan keamanan tambahan.</p>
        </article>
    </div>

    <p class="pf-note">Tips: gunakan password unik dan jangan gunakan ulang password yang pernah dipakai di layanan lain.</p>
</div>
