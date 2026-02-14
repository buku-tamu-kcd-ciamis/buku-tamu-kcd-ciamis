<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
  public function run(): void
  {
    $pegawaiList = [
      [
        'nama' => 'Drs. H. Ahmad Suryadi, M.Pd.',
        'nip' => '196805151993031008',
        'jabatan' => 'Kepala Cabang Dinas',
        'nomor_hp' => '812-3456-7890',
        'unit_kerja' => 'Cabang Dinas Pendidikan Wilayah XIII',
      ],
      [
        'nama' => 'Hj. Siti Nurhaliza, S.Pd., M.M.',
        'nip' => '197203221996032004',
        'jabatan' => 'Kasubag Tata Usaha',
        'nomor_hp' => '813-2345-6789',
        'unit_kerja' => 'Sub Bagian Tata Usaha',
      ],
      [
        'nama' => 'Ir. Bambang Hartono, M.T.',
        'nip' => '197508131999031005',
        'jabatan' => 'Kasi Kurikulum dan Penilaian',
        'nomor_hp' => '857-1234-5678',
        'unit_kerja' => 'Seksi Kurikulum dan Penilaian',
      ],
      [
        'nama' => 'Rina Marlina, S.Pd.',
        'nip' => '198101052005012003',
        'jabatan' => 'Kasi Peserta Didik dan Pembangunan Karakter',
        'nomor_hp' => '821-9876-5432',
        'unit_kerja' => 'Seksi Peserta Didik dan Pembangunan Karakter',
      ],
      [
        'nama' => 'Drs. Cecep Rustandi',
        'nip' => '196912181994031006',
        'jabatan' => 'Kasi Sarana dan Prasarana',
        'nomor_hp' => '852-3456-7891',
        'unit_kerja' => 'Seksi Sarana dan Prasarana',
      ],
      [
        'nama' => 'Yanti Herliana, S.E.',
        'nip' => '198305142006042007',
        'jabatan' => 'Bendahara Pengeluaran',
        'nomor_hp' => '878-5432-1098',
        'unit_kerja' => 'Sub Bagian Tata Usaha',
      ],
      [
        'nama' => 'Asep Saepudin, S.Pd.',
        'nip' => '198507202009031004',
        'jabatan' => 'Pengadministrasi Umum',
        'nomor_hp' => '815-6789-0123',
        'unit_kerja' => 'Sub Bagian Tata Usaha',
      ],
      [
        'nama' => 'Neneng Hasanah, S.Sos.',
        'nip' => '198012032003122001',
        'jabatan' => 'Pengadministrasi Kepegawaian',
        'nomor_hp' => '838-2345-6790',
        'unit_kerja' => 'Sub Bagian Tata Usaha',
      ],
      [
        'nama' => 'Dedi Mulyadi, S.Kom.',
        'nip' => '199001152014031002',
        'jabatan' => 'Pranata Komputer',
        'nomor_hp' => '856-7890-1234',
        'unit_kerja' => 'Sub Bagian Tata Usaha',
      ],
      [
        'nama' => 'Hj. Euis Komariah, M.Pd.',
        'nip' => '197604221998032005',
        'jabatan' => 'Pengawas Sekolah Madya',
        'nomor_hp' => '819-0123-4567',
        'unit_kerja' => 'Seksi Kurikulum dan Penilaian',
      ],
      [
        'nama' => 'Agus Firmansyah, S.Pd., M.Si.',
        'nip' => '198209102005011003',
        'jabatan' => 'Pengawas Sekolah Muda',
        'nomor_hp' => '822-3456-7892',
        'unit_kerja' => 'Seksi Kurikulum dan Penilaian',
      ],
      [
        'nama' => 'Lilis Suryani, A.Md.',
        'nip' => '198811252010032001',
        'jabatan' => 'Pengelola Barang Milik Negara',
        'nomor_hp' => '853-4567-8901',
        'unit_kerja' => 'Seksi Sarana dan Prasarana',
      ],
      [
        'nama' => 'Ujang Rahmat, S.Pd.',
        'nip' => '199105032015031001',
        'jabatan' => 'Analis Data dan Informasi',
        'nomor_hp' => '877-5678-9012',
        'unit_kerja' => 'Seksi Peserta Didik dan Pembangunan Karakter',
      ],
      [
        'nama' => 'Tati Sumiati, S.Pd.',
        'nip' => '197811142001032003',
        'jabatan' => 'Penata Layanan Operasional',
        'nomor_hp' => '814-6789-0124',
        'unit_kerja' => 'Sub Bagian Tata Usaha',
      ],
      [
        'nama' => 'Irfan Hakim, S.T.',
        'nip' => '199204172016031002',
        'jabatan' => 'Teknisi Sarana dan Prasarana',
        'nomor_hp' => '859-7890-1235',
        'unit_kerja' => 'Seksi Sarana dan Prasarana',
      ],
      [
        'nama' => 'Nana Suryana, S.Pd.',
        'nip' => '198603282010031005',
        'jabatan' => 'Penyusun Program Anggaran',
        'nomor_hp' => '831-8901-2345',
        'unit_kerja' => 'Sub Bagian Tata Usaha',
      ],
      [
        'nama' => 'Ai Nurjanah, S.Pd.',
        'nip' => '199307122017032001',
        'jabatan' => 'Verifikator Keuangan',
        'nomor_hp' => '858-9012-3456',
        'unit_kerja' => 'Sub Bagian Tata Usaha',
      ],
      [
        'nama' => 'Rusli Effendi, S.H.',
        'nip' => '197501092000031007',
        'jabatan' => 'Analis Hukum',
        'nomor_hp' => '816-0123-4568',
        'unit_kerja' => 'Sub Bagian Tata Usaha',
      ],
      [
        'nama' => 'Wawan Setiawan',
        'nip' => '198708152011011004',
        'jabatan' => 'Pengemudi',
        'nomor_hp' => '895-1234-5679',
        'unit_kerja' => 'Sub Bagian Tata Usaha',
      ],
      [
        'nama' => 'Cucu Jubaedah, A.Md.',
        'nip' => '199509182019032001',
        'jabatan' => 'Pengelola Arsip',
        'nomor_hp' => '823-2345-6793',
        'unit_kerja' => 'Sub Bagian Tata Usaha',
      ],
    ];

    foreach ($pegawaiList as $pegawai) {
      Pegawai::updateOrCreate(
        ['nip' => $pegawai['nip']],
        array_merge($pegawai, ['is_active' => true])
      );
    }
  }
}
