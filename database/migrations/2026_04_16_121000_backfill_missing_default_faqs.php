<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('faqs')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `faqs` MODIFY `target` ENUM('semua','admin','piket','staff') NOT NULL DEFAULT 'semua'");
        }

        $now = now();
        $hasCategory = Schema::hasColumn('faqs', 'category');

        $rows = [
            [
                'category' => 'umum',
                'question' => 'Apa itu aplikasi Buku Tamu Cadisdik XIII?',
                'answer' => 'Aplikasi Buku Tamu Cadisdik XIII adalah sistem digital untuk mencatat dan mengelola data kunjungan tamu secara real-time.',
                'target' => 'semua',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'category' => 'umum',
                'question' => 'Bagaimana alur kunjungan tamu?',
                'answer' => 'Tamu mengisi formulir digital, data masuk ke sistem, petugas memproses, lalu status kunjungan diperbarui sampai selesai.',
                'target' => 'semua',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'category' => 'umum',
                'question' => 'Bagaimana cara mencari data pengunjung?',
                'answer' => 'Gunakan kolom pencarian dan filter pada tabel Buku Tamu atau Riwayat Pengunjung sesuai panel Anda.',
                'target' => 'semua',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'category' => 'admin',
                'question' => 'Apa yang bisa dikelola oleh Super Admin dan Kepala Cabang Dinas?',
                'answer' => 'Panel admin digunakan untuk melihat data kunjungan, monitoring dashboard, serta pengelolaan konfigurasi sesuai hak akses akun.',
                'target' => 'admin',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'admin',
                'question' => 'Bagaimana cara mengelola FAQ?',
                'answer' => 'Buka menu Pengaturan > Manajemen FAQ untuk menambah, mengubah, menonaktifkan, dan mengatur urutan FAQ.',
                'target' => 'admin',
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'category' => 'piket',
                'question' => 'Apa tugas utama petugas Piket pada aplikasi ini?',
                'answer' => 'Petugas Piket memantau kunjungan masuk, melihat detail riwayat, dan melakukan proses operasional layanan tamu sesuai alur.',
                'target' => 'piket',
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'category' => 'piket',
                'question' => 'Bagaimana melihat riwayat pengunjung di panel Piket?',
                'answer' => 'Masuk ke menu Riwayat Pengunjung untuk melihat daftar pengunjung unik beserta total dan kunjungan terakhir.',
                'target' => 'piket',
                'sort_order' => 21,
                'is_active' => true,
            ],
            [
                'category' => 'staff',
                'question' => 'Apa yang bisa dilakukan Staff pada modul kunjungan?',
                'answer' => 'Staff dapat memantau kunjungan yang dituju, melihat detail data tamu, dan menyelesaikan proses layanan sesuai kewenangan.',
                'target' => 'staff',
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'category' => 'staff',
                'question' => 'Apakah Staff bisa melihat FAQ khusus?',
                'answer' => 'Ya, panel Staff menampilkan FAQ umum dan FAQ khusus Staff yang aktif.',
                'target' => 'staff',
                'sort_order' => 31,
                'is_active' => true,
            ],
        ];

        $payload = array_map(function (array $row) use ($hasCategory, $now): array {
            $data = [
                'question' => $row['question'],
                'answer' => $row['answer'],
                'target' => $row['target'],
                'sort_order' => $row['sort_order'],
                'is_active' => $row['is_active'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($hasCategory) {
                $data['category'] = $row['category'] ?? 'umum';
            }

            return $data;
        }, $rows);

        $existingQuestions = DB::table('faqs')
            ->whereIn('question', array_column($rows, 'question'))
            ->pluck('question')
            ->all();

        $toInsert = array_values(array_filter(
            $payload,
            fn(array $item): bool => !in_array($item['question'], $existingQuestions, true)
        ));

        if ($toInsert !== []) {
            DB::table('faqs')->insert($toInsert);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: keep FAQ data that may already be edited by admin.
    }
};
