<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('img/logo-cadisdik.png') }}">
    <title>Surat Izin Pegawai (Multi Print)</title>
    @php
        $settings = \App\Models\PengaturanKcd::getSettings();
        $paperSize = \App\Support\PrintPaperSize::normalize($settings->paper_size ?? 'a4');
        $isF4 = $paperSize === 'f4';
        $pageSize = \App\Support\PrintPaperSize::pageSize($paperSize, 'portrait');
        $pageWidth = \App\Support\PrintPaperSize::pageWidth($paperSize, 'portrait');
        $baseFontSize = $isF4 ? '12.5pt' : '12pt';
    @endphp
    <style>
        @page {
            size: {{ $pageSize }};
            margin: 10mm 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: {{ $baseFontSize }};
            color: #000;
            line-height: 1.6;
            background: #fff;
        }

        .page {
            max-width: {{ $pageWidth }};
            margin: 0 auto;
            padding: {{ $isF4 ? '15mm' : '10mm' }};
        }

        .page-break {
            page-break-after: always;
            break-after: page;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            gap: 15px;
            justify-content: space-between;
        }

        .header-logo {
            width: 90px;
            height: auto;
            flex-shrink: 0;
        }

        .header-spacer {
            width: 90px;
            flex-shrink: 0;
        }

        .header-text {
            flex: 1;
            text-align: center;
        }

        .header-text h2 {
            font-size: 14pt;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header-text h3 {
            font-size: 18pt;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header-text p {
            font-size: 12pt;
            margin: 0;
        }

        .title {
            text-align: center;
            margin: 25px 0 20px;
        }

        .title h3 {
            font-size: 14pt;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .title p {
            margin-top: 6px;
            font-size: 10pt;
            color: #444;
        }

        .content {
            margin: 20px 0;
            text-align: justify;
        }

        .content p {
            margin-bottom: 12px;
            text-indent: 30px;
        }

        .content p.no-indent {
            text-indent: 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .data-table td {
            padding: 6px 8px;
            vertical-align: top;
            font-size: {{ $baseFontSize }};
        }

        .data-table td.label {
            width: 190px;
            font-weight: bold;
        }

        .data-table td.colon {
            width: 15px;
            text-align: center;
        }

        .signature-section {
            display: flex;
            justify-content: space-around;
            align-items: flex-start;
            margin-top: 40px;
            page-break-inside: avoid;
            gap: 20px;
        }

        .signature-box {
            text-align: center;
            width: 220px;
            max-width: 220px;
        }

        .signature-box p {
            font-size: 10pt;
            line-height: 1.3;
        }

        .signature-label {
            min-height: 65px;
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .signature-space {
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
        }

        .signature-box .name {
            margin-top: 10px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        .signature-box .nip {
            margin-top: 5px;
            font-size: 10pt;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10B981;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            z-index: 999;
        }

        .print-btn:hover {
            background: #059669;
        }

        .notice {
            max-width: {{ $pageWidth }};
            margin: 16px auto;
            border: 1px solid #d1d5db;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            background: #fff8e6;
            padding: 10px 12px;
            font-size: 10pt;
            color: #6b4f00;
        }

        .empty-state {
            max-width: 720px;
            margin: 40px auto;
            text-align: center;
            color: #666;
            line-height: 1.7;
        }

        @media print {
            body {
                background: none;
            }

            .page {
                padding: 0;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    @if($pegawaiList->isEmpty())
        <div class="empty-state">
            <h3>Tidak Ada Data yang Dapat Dicetak</h3>
            <p>Data yang dipilih belum diverifikasi oleh Kepala KCD, sehingga belum dapat dicetak.</p>
        </div>
    @else
        <button class="print-btn no-print" onclick="window.print()">Print / Simpan PDF</button>

        @if(($skippedCount ?? 0) > 0)
            <div class="notice no-print">
                {{ $skippedCount }} data dilewati karena belum diverifikasi oleh Kepala KCD.
            </div>
        @endif

        @foreach($pegawaiList as $pegawai)
            @php
                $statusLabel = \App\Models\PegawaiIzin::STATUS_LABELS[$pegawai->status] ?? ucfirst($pegawai->status);
                $jenisIzin = \App\Models\PegawaiIzin::JENIS_IZIN_LABELS[$pegawai->jenis_izin] ?? $pegawai->jenis_izin;
                $verifiedAt = $pegawai->diverifikasi_pada?->translatedFormat('d F Y, H:i');
            @endphp

            <div class="page {{ !$loop->last ? 'page-break' : '' }}">
                <div class="header">
                    <img src="{{ asset('img/logo-jabar.png') }}" alt="Logo Jabar" class="header-logo">
                    <div class="header-text">
                        <h2>PEMERINTAH DAERAH PROVINSI JAWA BARAT</h2>
                        <h3>DINAS PENDIDIKAN</h3>
                        <p>CABANG DINAS PENDIDIKAN WILAYAH XIII</p>
                        <p>Jalan Letnan Harun No. 14, Kota Tasikmalaya</p>
                    </div>
                    <img src="{{ asset('img/logo-cadisdik.png') }}" alt="Logo Cadisdik" class="header-logo">
                </div>

                <div class="title">
                    <h3>Surat Izin Pegawai</h3>
                    <p>{{ $pegawai->nama_pegawai }} (NIP: {{ $pegawai->nip }})</p>
                </div>

                <div class="content">
                    <p class="no-indent">Dengan ini menerangkan data pegawai sebagai berikut:</p>

                    <table class="data-table">
                        <tr>
                            <td class="label">Nama Pegawai</td>
                            <td class="colon">:</td>
                            <td>{{ $pegawai->nama_pegawai }}</td>
                        </tr>
                        <tr>
                            <td class="label">NIP</td>
                            <td class="colon">:</td>
                            <td>{{ $pegawai->nip }}</td>
                        </tr>
                        <tr>
                            <td class="label">Jabatan</td>
                            <td class="colon">:</td>
                            <td>{{ $pegawai->jabatan ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Unit Kerja</td>
                            <td class="colon">:</td>
                            <td>{{ $pegawai->unit_kerja ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Nomor HP</td>
                            <td class="colon">:</td>
                            <td>{{ $pegawai->nomor_hp ? (str_starts_with($pegawai->nomor_hp, '0') ? '+62' . substr($pegawai->nomor_hp, 1) : $pegawai->nomor_hp) : '-' }}</td>
                        </tr>
                    </table>

                    <p>Rincian izin pegawai:</p>

                    <table class="data-table">
                        <tr>
                            <td class="label">Jenis Izin</td>
                            <td class="colon">:</td>
                            <td>{{ $jenisIzin }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tanggal Mulai</td>
                            <td class="colon">:</td>
                            <td>{{ $pegawai->tanggal_mulai?->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tanggal Selesai</td>
                            <td class="colon">:</td>
                            <td>{{ $pegawai->tanggal_selesai?->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Lama Izin</td>
                            <td class="colon">:</td>
                            <td><strong>{{ $pegawai->tanggal_mulai && $pegawai->tanggal_selesai ? $pegawai->tanggal_mulai->diffInDays($pegawai->tanggal_selesai) + 1 : 0 }} Hari</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Status Verifikasi</td>
                            <td class="colon">:</td>
                            <td>{{ $statusLabel }}</td>
                        </tr>
                        <tr>
                            <td class="label">Diverifikasi Oleh</td>
                            <td class="colon">:</td>
                            <td>{{ $pegawai->diverifikasi_oleh ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Waktu Verifikasi</td>
                            <td class="colon">:</td>
                            <td>{{ $verifiedAt ?: '-' }}</td>
                        </tr>
                        @if($pegawai->keterangan)
                            <tr>
                                <td class="label">Keterangan</td>
                                <td class="colon">:</td>
                                <td>{{ $pegawai->keterangan }}</td>
                            </tr>
                        @endif
                        @if($pegawai->catatan_verifikasi)
                            <tr>
                                <td class="label">Catatan Verifikasi</td>
                                <td class="colon">:</td>
                                <td>{{ $pegawai->catatan_verifikasi }}</td>
                            </tr>
                        @endif
                    </table>
                </div>

                <div class="signature-section">
                    <div class="signature-box">
                        <div class="signature-label">
                            <p>Mengetahui,</p>
                            <p>Kepala Cabang Dinas Pendidikan</p>
                            <p>Wilayah XIII,</p>
                            @if($verifiedAt)
                                <p style="margin-top: 6px;">Diverifikasi: {{ $verifiedAt }}</p>
                            @endif
                        </div>
                        <div class="signature-space"></div>
                        <p class="name">{{ $kepalaCabdin->formatted_nama }}</p>
                        <p class="nip">{{ $kepalaCabdin->formatted_nip }}</p>
                    </div>
                    <div class="signature-box">
                        <div class="signature-label">
                            <p>Yang Mengajukan,</p>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        </div>
                        <div class="signature-space"></div>
                        <p class="name">{{ $pegawai->nama_pegawai }}</p>
                        <p class="nip">NIP. {{ $pegawai->nip }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</body>

</html>
