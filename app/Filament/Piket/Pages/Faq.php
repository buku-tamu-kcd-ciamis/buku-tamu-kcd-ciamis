<?php

namespace App\Filament\Piket\Pages;

use Filament\Pages\Page;

class Faq extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'FAQ';
    protected static ?string $title = 'Pertanyaan Umum (FAQ)';
    protected static ?string $navigationGroup = 'Bantuan';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.piket.pages.faq';

    public function getFaqs(): array
    {
        return [
            [
                'question' => 'Bagaimana cara menerima tamu yang datang?',
                'answer' => 'Arahkan tamu untuk mengisi formulir buku tamu digital di halaman utama. Tamu akan mengisi data diri, foto selfie, tanda tangan, dan keperluan kunjungan. Setelah tamu submit, data akan muncul di halaman Kunjungan Tamu.',
            ],
            [
                'question' => 'Bagaimana cara mengubah status kunjungan tamu?',
                'answer' => 'Buka halaman Kunjungan Tamu, klik icon titik 3 (⋮) pada baris data tamu, pilih "Ubah Status". Anda bisa mengubah status menjadi Menunggu, Diproses, Selesai, Ditolak, atau Dibatalkan. Tambahkan catatan jika diperlukan.',
            ],
            [
                'question' => 'Apa perbedaan halaman Kunjungan Tamu dan Pengantar Berkas?',
                'answer' => 'Halaman <strong>Kunjungan Tamu</strong> menampilkan semua data tamu yang masuk. Sedangkan halaman <strong>Pengantar Berkas</strong> hanya menampilkan tamu yang keperluannya terkait berkas, surat, dokumen, atau legalisir.',
            ],
            [
                'question' => 'Bagaimana cara mencetak bukti kunjungan tamu?',
                'answer' => 'Klik icon titik 3 (⋮) pada baris data tamu, lalu pilih "Cetak". Halaman cetak akan terbuka di tab baru dalam format potrait A4. Klik tombol "Cetak" hijau di pojok kanan atas untuk mencetak.',
            ],
            [
                'question' => 'Bagaimana cara melihat riwayat pengunjung?',
                'answer' => 'Buka halaman <strong>Riwayat Pengunjung</strong> di menu Layanan Tamu. Halaman ini menampilkan daftar pengunjung unik beserta jumlah total kunjungan dan tanggal kunjungan terakhir.',
            ],
            [
                'question' => 'Bagaimana cara mengelola data Pegawai Izin?',
                'answer' => 'Buka halaman <strong>Pegawai Izin</strong> di menu Kepegawaian. Anda bisa menambahkan data pegawai yang sedang izin (cuti, sakit, dinas luar, dll) dengan mengklik tombol "Buat Pegawai Izin". Isi data pegawai, jenis izin, tanggal mulai dan selesai.',
            ],
            [
                'question' => 'Data tamu otomatis terisi saat mengisi NIK, kenapa?',
                'answer' => 'Sistem memiliki fitur auto-fill. Jika NIK yang dimasukkan sudah pernah terdaftar sebelumnya, data nama, instansi, no HP, jabatan, kabupaten/kota, dan email akan otomatis terisi berdasarkan kunjungan terakhir. Data tersebut masih bisa diubah oleh tamu.',
            ],
            [
                'question' => 'Bagaimana cara mengubah profil / password saya?',
                'answer' => 'Klik avatar/inisial Anda di pojok kanan atas, lalu pilih "Profile". Anda bisa mengubah nama, email, dan password di halaman profil.',
            ],
        ];
    }
}
