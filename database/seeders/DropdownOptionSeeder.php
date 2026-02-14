<?php

namespace Database\Seeders;

use App\Models\DropdownOption;
use Illuminate\Database\Seeder;

class DropdownOptionSeeder extends Seeder
{
  public function run(): void
  {
    // =============================================
    // JENIS ID
    // =============================================
    $jenisIdOptions = [
      ['value' => 'KTP', 'label' => 'KTP', 'metadata' => ['id_label' => 'NIK', 'placeholder' => 'Masukkan 16 digit NIK', 'digits' => 16]],
      ['value' => 'SIM', 'label' => 'SIM', 'metadata' => ['id_label' => 'No. SIM', 'placeholder' => 'Masukkan 12 digit No. SIM', 'digits' => 12]],
      ['value' => 'Passport', 'label' => 'Passport', 'metadata' => ['id_label' => 'No. Passport', 'placeholder' => 'Masukkan nomor passport', 'digits' => null]],
      ['value' => 'Kartu Pelajar', 'label' => 'Kartu Pelajar', 'metadata' => ['id_label' => 'No. Induk Siswa', 'placeholder' => 'Masukkan NIS / NISN', 'digits' => null]],
      ['value' => 'Kartu Pers', 'label' => 'Kartu Pers', 'metadata' => ['id_label' => 'No. Kartu Pers', 'placeholder' => 'Masukkan nomor kartu pers', 'digits' => null]],
      ['value' => 'Kartu Pegawai', 'label' => 'Kartu Pegawai / ASN', 'metadata' => ['id_label' => 'NIP / No. Pegawai', 'placeholder' => 'Masukkan NIP atau no. pegawai', 'digits' => null]],
      ['value' => 'NIP', 'label' => 'NIP', 'metadata' => ['id_label' => 'NIP', 'placeholder' => 'Masukkan NIP (18 digit)', 'digits' => 18]],
      ['value' => 'KITAS', 'label' => 'KITAS / KITAP', 'metadata' => ['id_label' => 'No. KITAS/KITAP', 'placeholder' => 'Masukkan nomor KITAS/KITAP', 'digits' => null]],
      ['value' => 'Kartu Anggota', 'label' => 'Kartu Anggota', 'metadata' => ['id_label' => 'No. Anggota', 'placeholder' => 'Masukkan nomor kartu anggota', 'digits' => null]],
      ['value' => 'Lainnya', 'label' => 'Lainnya', 'metadata' => ['id_label' => 'Nomor Identitas', 'placeholder' => 'Masukkan nomor identitas Anda', 'digits' => null]],
    ];

    foreach ($jenisIdOptions as $i => $option) {
      DropdownOption::updateOrCreate(
        ['category' => DropdownOption::CATEGORY_JENIS_ID, 'value' => $option['value']],
        [
          'label' => $option['label'],
          'metadata' => $option['metadata'],
          'sort_order' => $i + 1,
          'is_active' => true,
        ]
      );
    }

    // =============================================
    // KEPERLUAN
    // =============================================
    $keperluanOptions = [
      'Koordinasi/Konsultasi',
      'Rapat',
      'Menyerahkan Surat/Berkas',
      'Legalisir',
      'Audiensi',
      'Pengambilan Dokumen',
      'Permohonan Izin',
      'Sosialisasi',
      'Pembinaan',
      'Monitoring/Evaluasi',
      'Pelaporan',
      'Pengaduan',
      'Kunjungan Kerja',
      'Tanda Tangan Dokumen',
      'Verifikasi Data',
      'Permohonan Rekomendasi',
      'Asistensi',
      'Pendataan',
      'Kerja Sama/MoU',
      'Undangan/Acara Resmi',
      'Lainnya',
    ];

    foreach ($keperluanOptions as $i => $option) {
      DropdownOption::updateOrCreate(
        ['category' => DropdownOption::CATEGORY_KEPERLUAN, 'value' => $option],
        [
          'label' => $option,
          'metadata' => null,
          'sort_order' => $i + 1,
          'is_active' => true,
        ]
      );
    }

    // =============================================
    // KABUPATEN/KOTA
    // =============================================
    $kabupatenKota = \App\Helpers\KabupatenKota::all();
    $i = 1;
    foreach ($kabupatenKota as $value => $label) {
      DropdownOption::updateOrCreate(
        ['category' => DropdownOption::CATEGORY_KABUPATEN_KOTA, 'value' => $value],
        [
          'label' => $label,
          'metadata' => null,
          'sort_order' => $i,
          'is_active' => true,
        ]
      );
      $i++;
    }

    // Clear cache after seeding
    DropdownOption::clearCache();

    // =============================================
    // BAGIAN YANG DITUJU (Ruangan KCD Cadisdik 13)
    // =============================================
    $bagianDitujuOptions = [
      'Kepala Cabang Dinas',
      'Subbag Tata Usaha',
      'Seksi Kurikulum',
      'Seksi Peserta Didik',
      'Seksi Sarana dan Prasarana',
      'Seksi Ketenagaan',
      'Pengawas Sekolah',
      'Operator Dapodik',
      'Bendahara',
      'Bagian Kepegawaian',
      'Bagian Keuangan',
      'Bagian Umum',
      'Ruang Rapat',
      'Ruang Arsip',
      'Front Office / Resepsionis',
      'Lainnya',
    ];

    foreach ($bagianDitujuOptions as $i => $option) {
      DropdownOption::updateOrCreate(
        ['category' => DropdownOption::CATEGORY_BAGIAN_DITUJU, 'value' => $option],
        [
          'label' => $option,
          'metadata' => null,
          'sort_order' => $i + 1,
          'is_active' => true,
        ]
      );
    }

    // =============================================
    // PEGAWAI PIKET
    // =============================================
    $pegawaiPiketOptions = [
      'Drs. H. Ahmad Suryadi, M.Pd.',
      'Hj. Siti Nurhaliza, S.Pd., M.M.',
      'Rina Marlina, S.Pd.',
      'Dedi Kurniawan, S.Pd., M.Pd.',
      'Yanti Susilawati, S.E.',
      'Asep Hidayat, S.Pd.',
      'Neni Rohaeni, S.Pd., M.M.',
      'Iwan Setiawan, S.Sos.',
      'Lina Herlina, A.Md.',
      'Ujang Suherman, S.Pd.',
      'Cucu Sumiati, S.Pd.',
      'Agus Rahmat, S.Pd., M.Pd.',
      'Teti Suhaeti, S.E., M.M.',
      'Cecep Rustandi, S.Pd.',
      'Nining Yuningsih, S.Pd.',
    ];

    foreach ($pegawaiPiketOptions as $i => $option) {
      DropdownOption::updateOrCreate(
        ['category' => DropdownOption::CATEGORY_PEGAWAI_PIKET, 'value' => $option],
        [
          'label' => $option,
          'metadata' => null,
          'sort_order' => $i + 1,
          'is_active' => true,
        ]
      );
    }

    // Clear cache after seeding
    DropdownOption::clearCache();

    $this->command->info('Dropdown options seeded successfully!');
    $this->command->info('- Jenis ID: ' . count($jenisIdOptions) . ' options');
    $this->command->info('- Keperluan: ' . count($keperluanOptions) . ' options');
    $this->command->info('- Kabupaten/Kota: ' . count($kabupatenKota) . ' options');
    $this->command->info('- Bagian Dituju: ' . count($bagianDitujuOptions) . ' options');
    $this->command->info('- Pegawai Piket: ' . count($pegawaiPiketOptions) . ' options');
  }
}
