<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('img/logo-cadisdik.png') }}">
    <title>Laporan Kunjungan Tamu — Cadisdik XIII</title>
    @php
        $settings = \App\Models\PengaturanKcd::getSettings();
        $paperSize = $settings->paper_size ?? 'a4';
        $isF4 = $paperSize === 'f4';
        $pageSize = $isF4 ? '330mm 215mm' : 'A4 landscape';
        $baseFontSize = $isF4 ? '11.5pt' : '11pt';
    @endphp
    <style>
        @page {
            size:
                {{ $pageSize }}
            ;
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
            line-height: 1.4;
            background: #fff;
        }

        .page {
            max-width:
                {{ $isF4 ? '330mm' : '297mm' }}
            ;
            margin: 0 auto;
            padding: 8mm;
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
            font-size: 13pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .header-text h3 {
            font-size: 12pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .header-text p {
            font-size: 9pt;
            margin: 0;
        }

        /* === TITLE === */
        .title {
            text-align: center;
            margin: 20px 0 15px;
        }

        .title h3 {
            font-size: 13pt;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .title p {
            font-size: 10pt;
            color: #555;
            margin-top: 3px;
        }

        /* === TABLE === */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }

        .data-table thead {
            background: #f3f4f6;
        }

        .data-table th {
            border: 1px solid #000;
            padding: 8px 6px;
            font-weight: bold;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #000;
            padding: 6px 6px;
            vertical-align: top;
        }

        .data-table td.no {
            text-align: center;
            width: 30px;
        }

        .data-table td.foto {
            text-align: center;
            width: 60px;
        }

        .data-table td.foto img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* === STATUS === */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-selesai {
            background: #D1FAE5;
            color: #065F46;
        }

        /* === SUMMARY === */
        .summary {
            margin-top: 20px;
            font-size: 10pt;
        }

        .summary p {
            margin-bottom: 5px;
        }

        /* === SIGNATURE === */
        .signature-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            width: 250px;
        }

        .signature-box p {
            font-size: 10pt;
            line-height: 1.3;
        }

        .signature-box .name {
            margin-top: 50px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        /* === FOOTER === */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8pt;
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
            <h3>Laporan Data Kunjungan Tamu</h3>
            <p>Per {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        </div>

        <!-- TABLE -->
        <table class="data-table">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th class="foto">Foto</th>
                    <th>Nama Lengkap</th>
                    <th>NIK</th>
                    <th>Instansi</th>
                    <th>Keperluan</th>
                    <th>Bagian Dituju</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tamuList as $index => $tamu)
                    <tr>
                        <td class="no">{{ $index + 1 }}</td>
                        <td class="foto">
                            @if($tamu->foto_selfie_url)
                                <img src="{{ $tamu->foto_selfie_url }}" alt="Foto">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $tamu->nama_lengkap }}</td>
                        <td>{{ $tamu->nik }}</td>
                        <td>{{ $tamu->instansi ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($tamu->keperluan, 50) }}</td>
                        <td>{{ $tamu->bagian_dituju }}</td>
                        <td>{{ $tamu->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                            Tidak ada data kunjungan yang selesai
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- SUMMARY -->
        <div class="summary">
            <p><strong>Total Data:</strong> {{ $tamuList->count() }} kunjungan</p>
        </div>

        <!-- SIGNATURE -->
        <div class="signature-section">
            <div class="signature-box">
                <p>Ciamis, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin-top: 10px;">Kepala Cabang Dinas Pendidikan</p>
                <p>Wilayah XIII,</p>
                <p class="name">{{ $kepalaCabdin->formatted_nama }}</p>
                <p style="font-size: 9pt; margin-top: 3px;">{{ $kepalaCabdin->formatted_nip }}</p>
            </div>
        </div>
    </div>
</body>

</html>