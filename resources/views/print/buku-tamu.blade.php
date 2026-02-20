<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('img/logo-cadisdik.png') }}">
    <title>Surat Kunjungan — {{ $tamu->nama_lengkap }}</title>
    @php
        $settings = \App\Models\PengaturanKcd::getSettings();
        $paperSize = $settings->paper_size ?? 'a4';
        $isF4 = $paperSize === 'f4';
        $pageSize = $isF4 ? '215mm 330mm' : 'A4';
        $baseFontSize = $isF4 ? '12pt' : '11pt';
    @endphp
    <style>
        @page {
            size:
                {{ $pageSize }}
                portrait;
            margin: 10mm 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size:
                {{ $baseFontSize }}
            ;
            color: #000;
            line-height: 1.5;
            background: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .page {
            max-width:
                {{ $isF4 ? '215mm' : '210mm' }}
            ;
            margin: 0 auto;
            padding:
                {{ $isF4 ? '10mm' : '5mm' }}
            ;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1;
        }

        /* === HEADER === */
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

        .header-logo-right {
            width: 90px;
            height: auto;
            flex-shrink: 0;
        }

        .header-text {
            flex: 1;
            text-align: center;
        }

        .header-text h2 {
            font-size: 14pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .header-text h3 {
            font-size: 13pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .header-text p {
            font-size: 10pt;
            margin: 0;
        }

        /* === TITLE === */
        .title {
            text-align: center;
            margin: 15px 0 12px;
        }

        .title h3 {
            font-size: 13pt;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .title p {
            font-size: 9pt;
            color: #555;
            margin-top: 2px;
        }

        /* === TABLE DATA === */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .data-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 11pt;
        }

        .data-table td.label {
            width: 180px;
            font-weight: bold;
        }

        .data-table td.colon {
            width: 15px;
            text-align: center;
        }

        /* === STATUS === */
        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 4px;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-menunggu {
            background: #FEF3C7;
            color: #92400E;
            border: 1px solid #F59E0B;
        }

        .status-diproses {
            background: #DBEAFE;
            color: #1E40AF;
            border: 1px solid #3B82F6;
        }

        .status-selesai {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #10B981;
        }

        .status-ditolak {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #EF4444;
        }

        .status-dibatalkan {
            background: #F3F4F6;
            color: #374151;
            border: 1px solid #9CA3AF;
        }

        /* === FOTO SECTION === */
        .foto-section {
            display: flex;
            gap: 20px;
            margin: 15px 0;
            justify-content: center;
        }

        .foto-item {
            text-align: center;
        }

        .foto-item p {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .foto-item img {
            max-width: 150px;
            max-height: 150px;
            object-fit: contain;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .foto-placeholder {
            width: 150px;
            height: 100px;
            border: 1px dashed #ccc;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 9pt;
        }

        /* === SIGNATURE === */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 30px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            width: 180px;
        }

        .signature-box p {
            font-size: 10pt;
        }

        .signature-box .name {
            margin-top: 50px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        .signature-box img {
            max-width: 120px;
            max-height: 60px;
            object-fit: contain;
        }

        .signature-box .name-signed {
            margin-top: 5px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        /* === FOOTER === */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9pt;
            color: #777;
        }

        /* === PRINT === */
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

        /* === PRINT BUTTON === */
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
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 999;
        }

        .print-btn:hover {
            background: #059669;
        }
    </style>
</head>

<body>
    <button class="print-btn no-print" onclick="window.print()">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z" />
        </svg>
        Cetak
    </button>

    <div class="page">
        <div class="content">
            <!-- HEADER -->
            <div class="header">
                <img src="{{ asset('img/logo-jawabarat.png') }}" alt="Logo Jawa Barat" class="header-logo">
                <div class="header-text">
                    <h2>Pemerintah Daerah Provinsi Jawa Barat</h2>
                    <h3>Cabang Dinas Pendidikan Wilayah XIII</h3>
                    <p>Jl. Mr. Iwa Kusumasomantri No. 12, Ciamis, Jawa Barat 46211</p>
                    <p>Telp: (0265) 771045 | Email: cadisdik13@disdik.jabarprov.go.id</p>
                </div>
                <div class="header-spacer"></div>
            </div>

            <!-- TITLE -->
            <div class="title">
                <h3>Bukti Kunjungan Tamu</h3>
                @if($nomorSuratSetting)
                    <p>No. {{ $nomorSuratSetting->generateNomor($tamu->id, $tamu->created_at) }}</p>
                @else
                    <p>No. {{ str_pad($tamu->id, 6, '0', STR_PAD_LEFT) }} / BT /
                        {{ \Carbon\Carbon::parse($tamu->created_at)->format('m/Y') }}
                    </p>
                @endif
            </div>

            <!-- DATA TABLE -->
            <table class="data-table">
                <tr>
                    <td class="label">Nama Lengkap</td>
                    <td class="colon">:</td>
                    <td>{{ $tamu->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td class="label">{{ $tamu->jenis_id ?? 'Nomor ID' }}</td>
                    <td class="colon">:</td>
                    <td>{{ $tamu->nik }}</td>
                </tr>
                <tr>
                    <td class="label">Instansi</td>
                    <td class="colon">:</td>
                    <td>{{ $tamu->instansi ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Jabatan</td>
                    <td class="colon">:</td>
                    <td>{{ $tamu->jabatan ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor HP</td>
                    <td class="colon">:</td>
                    <td>{{ $tamu->nomor_hp }}</td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td class="colon">:</td>
                    <td>{{ $tamu->email ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Kabupaten / Kota</td>
                    <td class="colon">:</td>
                    <td>{{ $tamu->kabupaten_kota }}</td>
                </tr>
                <tr>
                    <td class="label">Bagian Yang Dituju</td>
                    <td class="colon">:</td>
                    <td>{{ $tamu->bagian_dituju }}</td>
                </tr>
                <tr>
                    <td class="label">Keperluan</td>
                    <td class="colon">:</td>
                    <td>{{ $tamu->keperluan }}</td>
                </tr>
                <tr>
                    <td class="label">Waktu Kunjungan</td>
                    <td class="colon">:</td>
                    <td>{{ \Carbon\Carbon::parse($tamu->created_at)->translatedFormat('d F Y, H:i') }} WIB</td>
                </tr>
                @if($tamu->catatan)
                    <tr>
                        <td class="label">Catatan</td>
                        <td class="colon">:</td>
                        <td>{{ $tamu->catatan }}</td>
                    </tr>
                @endif
            </table>

            <!-- FOTO -->
            <div class="foto-section">
                <div class="foto-item">
                    <p>Foto Selfie</p>
                    @if($tamu->foto_selfie_url)
                        <img src="{{ $tamu->foto_selfie_url }}" alt="Foto Selfie">
                    @else
                        <div class="foto-placeholder">Tidak ada foto</div>
                    @endif
                </div>
                @if($tamu->foto_penerimaan_url)
                    <div class="foto-item">
                        <p>Foto Penerimaan</p>
                        <img src="{{ $tamu->foto_penerimaan_url }}" alt="Foto Penerimaan">
                    </div>
                @endif
            </div>
        </div>

        <!-- SIGNATURE -->
        <div class="signature-section">
            <div class="signature-box">
                <p>Tamu,</p>
                @if($tamu->tanda_tangan_url)
                    <img src="{{ $tamu->tanda_tangan_url }}" alt="TTD">
                    <p class="name-signed">{{ $tamu->nama_lengkap }}</p>
                @else
                    <p class="name">{{ $tamu->nama_lengkap }}</p>
                @endif
            </div>
            <div class="signature-box">
                <p>Petugas Piket,</p>
                <p class="name">{{ $tamu->nama_penerima ?? '(.................................)' }}</p>
            </div>
        </div>
    </div>
</body>

</html>