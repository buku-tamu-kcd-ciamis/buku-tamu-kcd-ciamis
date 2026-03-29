<style>
    .um-policy {
        display: grid;
        gap: 0.7rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .um-policy-item {
        border: 1px solid #dbe6f0;
        border-radius: 0.8rem;
        padding: 0.72rem 0.78rem;
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }

    .um-policy-item--blue {
        border-left: 4px solid #3b82f6;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .um-policy-item--amber {
        border-left: 4px solid #f59e0b;
        background: linear-gradient(180deg, #ffffff 0%, #fffbf3 100%);
    }

    .um-policy-item--rose {
        border-left: 4px solid #ef4444;
        background: linear-gradient(180deg, #ffffff 0%, #fff6f6 100%);
    }

    .um-policy-title {
        margin: 0;
        font-size: 0.88rem;
        line-height: 1.35;
        font-weight: 700;
        color: #0f172a;
    }

    .um-policy-desc {
        margin: 0.32rem 0 0;
        font-size: 0.81rem;
        line-height: 1.5;
        color: #475569;
    }

    .um-policy-tip {
        margin: 0;
        grid-column: 1 / -1;
        border-radius: 0.72rem;
        border: 1px dashed #c7d2e0;
        background: #f8fafc;
        padding: 0.62rem 0.72rem;
        font-size: 0.77rem;
        line-height: 1.45;
        color: #475569;
    }

    @media (max-width: 1100px) {
        .um-policy {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 780px) {
        .um-policy {
            grid-template-columns: 1fr;
        }
    }

    .dark .um-policy-item {
        border-color: #374151;
        background: #111827;
    }

    .dark .um-policy-item--blue {
        background: linear-gradient(180deg, #111827 0%, #0f1d33 100%);
    }

    .dark .um-policy-item--amber {
        background: linear-gradient(180deg, #111827 0%, #2a1d06 100%);
    }

    .dark .um-policy-item--rose {
        background: linear-gradient(180deg, #111827 0%, #301214 100%);
    }

    .dark .um-policy-title {
        color: #f8fafc;
    }

    .dark .um-policy-desc,
    .dark .um-policy-tip {
        color: #cbd5e1;
    }

    .dark .um-policy-tip {
        border-color: #475569;
        background: #1f2937;
    }
</style>

<div class="um-policy">
    <article class="um-policy-item um-policy-item--blue">
        <h4 class="um-policy-title">Visibilitas Data</h4>
        <p class="um-policy-desc">User ber-role Super Admin disembunyikan dari daftar user pada panel ini.</p>
    </article>

    <article class="um-policy-item um-policy-item--amber">
        <h4 class="um-policy-title">Batas Kepala Cabang Dinas</h4>
        <p class="um-policy-desc">Role Kepala Cabang Dinas hanya boleh dimiliki 1 user aktif agar struktur organisasi tetap konsisten.</p>
    </article>

    <article class="um-policy-item um-policy-item--rose">
        <h4 class="um-policy-title">Konfirmasi Hapus User</h4>
        <p class="um-policy-desc">Aksi hapus user mewajibkan verifikasi password akun yang sedang login untuk mencegah penghapusan tidak sengaja.</p>
    </article>

    <p class="um-policy-tip">
        Kebijakan di atas diterapkan otomatis oleh sistem. Pastikan role dipilih sesuai kebutuhan kerja user.
    </p>
</div>
