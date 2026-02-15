<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Hapus data lama
    DB::table('faqs')->truncate();

    $faqs = [
      // === FAQ Umum (Semua Panel) ===
      [
        'question' => 'Apa itu aplikasi Buku Tamu Cadisdik XIII?',
        'answer' => 'Aplikasi Buku Tamu Cadisdik XIII adalah sistem digital untuk mencatat dan mengelola data kunjungan tamu di Cabang Dinas Pendidikan Wilayah XIII. Aplikasi ini memudahkan pencatatan, monitoring, dan pelaporan kunjungan secara real-time.',
        'target' => 'semua',
        'sort_order' => 1,
      ],
      [
        'question' => 'Bagaimana alur kunjungan tamu?',
        'answer' => 'Tamu datang → Mengisi formulir digital (data diri, foto selfie, keperluan, tanda tangan) → Data masuk ke sistem → Petugas Piket memproses → Status diupdate (Menunggu → Diproses → Selesai/Ditolak) → Tamu dilayani.',
        'target' => 'semua',
        'sort_order' => 2,
      ],
      [
        'question' => 'Bagaimana cara melihat data buku tamu?',
        'answer' => 'Buka menu <strong>Buku Tamu</strong> di sidebar. Anda bisa melihat semua data tamu yang masuk, mencari berdasarkan nama/NIK, dan memfilter berdasarkan tanggal. Klik icon titik 3 (⋮) untuk melihat detail atau menghapus data.',
        'target' => 'semua',
        'sort_order' => 3,
      ],
      [
        'question' => 'Bagaimana cara mencetak bukti kunjungan?',
        'answer' => 'Fitur cetak tersedia di panel Piket. Petugas Piket dapat mencetak bukti kunjungan dalam format A4 potrait melalui menu Kunjungan Tamu atau Pengantar Berkas.',
        'target' => 'semua',
        'sort_order' => 4,
      ],
      [
        'question' => 'Bagaimana cara mengubah profil / password saya?',
        'answer' => 'Klik avatar/inisial Anda di pojok kanan atas, lalu pilih "Profile". Anda bisa mengubah nama, email, dan password di halaman profil.',
        'target' => 'semua',
        'sort_order' => 5,
      ],

      // === FAQ Admin (Super Admin & Ketua KCD) ===
      [
        'question' => 'Bagaimana cara mengelola pengguna (user)?',
        'answer' => 'Buka menu <strong>Pengguna → User</strong>. Anda bisa menambahkan user baru, mengedit data user, mereset password, dan menetapkan role (Super Admin, Ketua KCD, Piket). Untuk menambah user, klik tombol "New User" di pojok kanan atas.',
        'target' => 'admin',
        'sort_order' => 6,
      ],
      [
        'question' => 'Apa perbedaan hak akses setiap role?',
        'answer' => '<strong>Super Admin</strong> memiliki akses penuh ke panel Admin dan Piket. <strong>Ketua KCD</strong> hanya bisa mengakses panel Admin untuk melihat data dan laporan. <strong>Piket</strong> hanya bisa mengakses panel Piket untuk mengelola kunjungan tamu sehari-hari.',
        'target' => 'admin',
        'sort_order' => 7,
      ],
      [
        'question' => 'Bagaimana cara menghapus data buku tamu?',
        'answer' => 'Buka menu Buku Tamu, klik icon titik 3 (⋮) pada baris data, lalu pilih "Hapus". Anda juga bisa menghapus secara massal dengan mencentang beberapa data lalu klik tombol "Hapus" di bagian bawah tabel.',
        'target' => 'admin',
        'sort_order' => 8,
      ],
      [
        'question' => 'Bagaimana jika lupa password user?',
        'answer' => 'Sebagai Super Admin, Anda bisa mereset password user melalui menu User. Klik tombol titik 3 (⋮) pada user yang bersangkutan, pilih "Reset Password", lalu isi password baru.',
        'target' => 'admin',
        'sort_order' => 9,
      ],
      [
        'question' => 'Bagaimana cara memonitor kunjungan harian?',
        'answer' => 'Buka halaman Dashboard untuk melihat statistik kunjungan. Buka menu Buku Tamu untuk melihat daftar kunjungan lengkap. Gunakan filter tanggal untuk melihat kunjungan pada periode tertentu.',
        'target' => 'admin',
        'sort_order' => 10,
      ],
      [
        'question' => 'Data apa saja yang bisa saya lihat sebagai Ketua KCD?',
        'answer' => 'Anda bisa melihat semua data buku tamu yang masuk, termasuk data diri tamu, keperluan, bagian yang dituju, status kunjungan, foto selfie, dan tanda tangan. Anda juga bisa melihat statistik kunjungan di dashboard.',
        'target' => 'admin',
        'sort_order' => 11,
      ],

      // === FAQ Piket ===
      [
        'question' => 'Bagaimana cara menerima tamu yang datang?',
        'answer' => 'Arahkan tamu untuk mengisi formulir buku tamu digital di halaman utama. Tamu akan mengisi data diri, foto selfie, tanda tangan, dan keperluan kunjungan. Setelah tamu submit, data akan muncul di halaman Kunjungan Tamu.',
        'target' => 'piket',
        'sort_order' => 12,
      ],
      [
        'question' => 'Bagaimana cara mengubah status kunjungan tamu?',
        'answer' => 'Buka halaman Kunjungan Tamu, klik icon titik 3 (⋮) pada baris data tamu, pilih "Ubah Status". Anda bisa mengubah status menjadi Menunggu, Diproses, Selesai, Ditolak, atau Dibatalkan. Tambahkan catatan jika diperlukan.',
        'target' => 'piket',
        'sort_order' => 13,
      ],
      [
        'question' => 'Apa perbedaan halaman Kunjungan Tamu dan Pengantar Berkas?',
        'answer' => 'Halaman <strong>Kunjungan Tamu</strong> menampilkan semua data tamu yang masuk. Sedangkan halaman <strong>Pengantar Berkas</strong> hanya menampilkan tamu yang keperluannya terkait berkas, surat, dokumen, atau legalisir.',
        'target' => 'piket',
        'sort_order' => 14,
      ],
      [
        'question' => 'Bagaimana cara mencetak bukti kunjungan tamu?',
        'answer' => 'Klik icon titik 3 (⋮) pada baris data tamu, lalu pilih "Cetak". Halaman cetak akan terbuka di tab baru dalam format potrait A4. Klik tombol "Cetak" hijau di pojok kanan atas untuk mencetak.',
        'target' => 'piket',
        'sort_order' => 15,
      ],
      [
        'question' => 'Bagaimana cara melihat riwayat pengunjung?',
        'answer' => 'Buka halaman <strong>Riwayat Pengunjung</strong> di menu Layanan Tamu. Halaman ini menampilkan daftar pengunjung unik beserta jumlah total kunjungan dan tanggal kunjungan terakhir.',
        'target' => 'piket',
        'sort_order' => 16,
      ],
      [
        'question' => 'Bagaimana cara mengelola data Pegawai Izin?',
        'answer' => 'Buka halaman <strong>Pegawai Izin</strong> di menu Kepegawaian. Anda bisa menambahkan data pegawai yang sedang izin (cuti, sakit, dinas luar, dll) dengan mengklik tombol "Buat Pegawai Izin". Isi data pegawai, jenis izin, tanggal mulai dan selesai.',
        'target' => 'piket',
        'sort_order' => 17,
      ],
      [
        'question' => 'Data tamu otomatis terisi saat mengisi NIK, kenapa?',
        'answer' => 'Sistem memiliki fitur auto-fill. Jika NIK yang dimasukkan sudah pernah terdaftar sebelumnya, data nama, instansi, no HP, jabatan, kabupaten/kota, dan email akan otomatis terisi berdasarkan kunjungan terakhir. Data tersebut masih bisa diubah oleh tamu.',
        'target' => 'piket',
        'sort_order' => 18,
      ],
    ];

    foreach ($faqs as $faq) {
      Faq::create($faq);
    }
  }
}
