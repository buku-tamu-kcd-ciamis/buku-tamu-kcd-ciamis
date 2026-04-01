<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class RoleBasedFaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqItems = [
            Faq::TARGET_ADMIN => [
                [
                    'question' => 'Bagaimana mengatur akses menu untuk setiap role?',
                    'answer' => '<p>Buka menu <strong>Pengaturan Akses</strong>, lalu centang hak akses per role (Kepala Cabdin, Piket, Staff). Simpan perubahan, kemudian minta pengguna refresh panel mereka.</p>',
                ],
                [
                    'question' => 'Bagaimana mengelola FAQ untuk semua panel?',
                    'answer' => '<p>Gunakan menu <strong>Manajemen FAQ</strong> di panel admin. Pilih target FAQ (Admin, Piket, Staff, atau Semua), lalu isi pertanyaan dan jawaban sesuai kebutuhan.</p>',
                ],
                [
                    'question' => 'Apa yang dilakukan saat terjadi error 500 di panel?',
                    'answer' => '<p>Periksa <strong>storage/logs/laravel.log</strong>, jalankan migrasi jika ada tabel yang belum dibuat, lalu jalankan <code>php artisan optimize:clear</code>.</p>',
                ],
                [
                    'question' => 'Bagaimana melakukan backup log aktivitas?',
                    'answer' => '<p>Buka halaman <strong>Log Aktivitas</strong>, gunakan aksi backup yang tersedia, lalu simpan file hasil backup di lokasi aman.</p>',
                ],
            ],
            Faq::TARGET_PIKET => [
                [
                    'question' => 'Apa alur utama layanan tamu untuk petugas piket?',
                    'answer' => '<p>Cek data tamu masuk, verifikasi informasi, arahkan ke staff tujuan, update status kunjungan, lalu dokumentasikan hasil akhir kunjungan.</p>',
                ],
                [
                    'question' => 'Kapan harus menggunakan Chat Booking?',
                    'answer' => '<p>Gunakan <strong>Chat Booking</strong> saat perlu koordinasi cepat dengan staff terkait tamu yang sedang diproses.</p>',
                ],
                [
                    'question' => 'Bagaimana jika data tamu tidak lengkap?',
                    'answer' => '<p>Minta tamu melengkapi data penting terlebih dahulu sebelum diproses lebih lanjut agar pencatatan dan pelaporan tetap valid.</p>',
                ],
                [
                    'question' => 'Bagaimana menangani status kunjungan?',
                    'answer' => '<p>Pastikan perubahan status mengikuti kondisi nyata di lapangan, mulai dari menunggu, diproses, hingga selesai atau ditolak bila diperlukan.</p>',
                ],
            ],
            Faq::TARGET_STAFF => [
                [
                    'question' => 'Bagaimana memantau tamu yang diarahkan ke saya?',
                    'answer' => '<p>Pantau menu <strong>Buku Tamu</strong> dan <strong>Chat Booking</strong> secara berkala untuk melihat tamu terbaru dan koordinasi dengan petugas piket.</p>',
                ],
                [
                    'question' => 'Bagaimana menanggapi chat dari petugas piket?',
                    'answer' => '<p>Buka thread chat terkait, kirim konfirmasi kesiapan menerima tamu, dan gunakan balasan singkat yang jelas agar alur layanan tetap cepat.</p>',
                ],
                [
                    'question' => 'Bagaimana cara mengajukan izin?',
                    'answer' => '<p>Masuk ke menu <strong>Izin Saya</strong>, isi detail izin dengan benar, lalu kirim pengajuan. Pastikan rentang tanggal sesuai ketentuan yang berlaku.</p>',
                ],
                [
                    'question' => 'Di mana melihat riwayat kunjungan saya?',
                    'answer' => '<p>Gunakan menu <strong>Riwayat Kunjungan</strong> untuk melihat histori tamu yang pernah diarahkan kepada Anda.</p>',
                ],
            ],
        ];

        foreach ($faqItems as $target => $items) {
            foreach ($items as $index => $item) {
                Faq::updateOrCreate(
                    [
                        'target' => $target,
                        'question' => $item['question'],
                    ],
                    [
                        'answer' => $item['answer'],
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
