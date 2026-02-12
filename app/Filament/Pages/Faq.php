<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Faq extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'FAQ';
    protected static ?string $title = 'Pertanyaan Umum (FAQ)';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.faq';

    public function getFaqs(): array
    {
        $user = Auth::user();
        $roleName = $user?->role_user?->name;

        $commonFaqs = [
            [
                'question' => 'Apa itu aplikasi Buku Tamu Cadisdik XIII?',
                'answer' => 'Aplikasi Buku Tamu Cadisdik XIII adalah sistem digital untuk mencatat dan mengelola data kunjungan tamu di Cabang Dinas Pendidikan Wilayah XIII. Aplikasi ini memudahkan pencatatan, monitoring, dan pelaporan kunjungan secara real-time.',
            ],
            [
                'question' => 'Bagaimana alur kunjungan tamu?',
                'answer' => 'Tamu datang → Mengisi formulir digital (data diri, foto selfie, keperluan, tanda tangan) → Data masuk ke sistem → Petugas Piket memproses → Status diupdate (Menunggu → Diproses → Selesai/Ditolak) → Tamu dilayani.',
            ],
            [
                'question' => 'Bagaimana cara melihat data buku tamu?',
                'answer' => 'Buka menu <strong>Buku Tamu</strong> di sidebar. Anda bisa melihat semua data tamu yang masuk, mencari berdasarkan nama/NIK, dan memfilter berdasarkan tanggal. Klik icon titik 3 (⋮) untuk melihat detail atau menghapus data.',
            ],
            [
                'question' => 'Bagaimana cara mencetak bukti kunjungan?',
                'answer' => 'Fitur cetak tersedia di panel Piket. Petugas Piket dapat mencetak bukti kunjungan dalam format A4 potrait melalui menu Kunjungan Tamu atau Pengantar Berkas.',
            ],
        ];

        $superAdminFaqs = [
            [
                'question' => 'Bagaimana cara mengelola pengguna (user)?',
                'answer' => 'Buka menu <strong>Profiles → User</strong>. Anda bisa menambahkan user baru, mengedit data user, mengubah password, dan menetapkan role (Super Admin, Ketua KCD, Piket). Untuk menambah user, klik tombol "New User" di pojok kanan atas.',
            ],
            [
                'question' => 'Bagaimana cara mengelola role pengguna?',
                'answer' => 'Buka menu <strong>Profiles → Role User</strong>. Anda bisa melihat daftar role yang tersedia, menambah role baru, atau mengedit role yang sudah ada. Setiap role menentukan akses panel yang bisa dibuka oleh user.',
            ],
            [
                'question' => 'Apa perbedaan hak akses setiap role?',
                'answer' => '<strong>Super Admin</strong> memiliki akses penuh ke panel Admin dan Piket. <strong>Ketua KCD</strong> hanya bisa mengakses panel Admin untuk melihat data dan laporan. <strong>Piket</strong> hanya bisa mengakses panel Piket untuk mengelola kunjungan tamu sehari-hari.',
            ],
            [
                'question' => 'Bagaimana cara menghapus data buku tamu?',
                'answer' => 'Buka menu Buku Tamu, klik icon titik 3 (⋮) pada baris data, lalu pilih "Hapus". Anda juga bisa menghapus secara massal dengan mencentang beberapa data lalu klik tombol "Hapus" di bagian bawah tabel.',
            ],
            [
                'question' => 'Bagaimana jika lupa password user?',
                'answer' => 'Sebagai Super Admin, Anda bisa mereset password user melalui menu User. Klik edit pada user yang bersangkutan, lalu isi password baru di field Password. Simpan perubahan.',
            ],
        ];

        $ketuaKcdFaqs = [
            [
                'question' => 'Data apa saja yang bisa saya lihat?',
                'answer' => 'Anda bisa melihat semua data buku tamu yang masuk, termasuk data diri tamu, keperluan, bagian yang dituju, status kunjungan, foto selfie, dan tanda tangan. Anda juga bisa melihat statistik kunjungan.',
            ],
            [
                'question' => 'Apakah saya bisa mengubah data kunjungan?',
                'answer' => 'Sebagai Ketua KCD, Anda memiliki akses untuk melihat seluruh data kunjungan. Untuk pengelolaan operasional (ubah status, cetak) dilakukan oleh petugas Piket melalui panel Piket.',
            ],
            [
                'question' => 'Bagaimana cara memonitor kunjungan harian?',
                'answer' => 'Buka halaman Dashboard untuk melihat statistik kunjungan. Buka menu Buku Tamu untuk melihat daftar kunjungan lengkap. Gunakan filter tanggal untuk melihat kunjungan pada periode tertentu.',
            ],
        ];

        if ($roleName === 'Super Admin') {
            return array_merge($commonFaqs, $superAdminFaqs);
        }

        if ($roleName === 'Ketua KCD') {
            return array_merge($commonFaqs, $ketuaKcdFaqs);
        }

        return $commonFaqs;
    }
}
