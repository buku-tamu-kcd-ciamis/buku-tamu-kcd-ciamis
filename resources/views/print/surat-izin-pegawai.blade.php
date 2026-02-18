<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('img/logo-cadisdik.png') }}">
    <title>Surat Izin — {{ $pegawai->nama_pegawai }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #000;
            line-height: 1.5;
            background: #fff;
        }

        .page {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
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
            margin: 25px 0 20px;
        }

        .title h3 {
            font-size: 14pt;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* === CONTENT === */
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

        /* === DATA TABLE === */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .data-table td {
            padding: 6px 8px;
            vertical-align: top;
            font-size: 12pt;
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

        .status-aktif { background: #D1FAE5; color: #065F46; border: 1px solid #10B981; }
        .status-selesai { background: #F3F4F6; color: #374151; border: 1px solid #9CA3AF; }

        /* === SIGNATURE === */
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
            body { background: none; }
            .page { padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
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
    <button class="print-btn no-print" onclick="printClean()">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg>
        Cetak
    </button>

    <div class="page">
        <!-- HEADER -->
        <div class="header">
            <img src="{{ asset('img/logo-cadisdik.png') }}" alt="Logo" class="header-logo">
            <div class="header-text">
                <h2>Pemerintah Daerah Provinsi Jawa Barat</h2>
                <h3>Cabang Dinas Pendidikan Wilayah XIII</h3>
                <p>Jl. Mr. Iwa Kusumasomantri No. 12, Ciamis, Jawa Barat 46211</p>
                <p>Telp: (0265) 771045 | Email: cadisdik13@disdik.jabarprov.go.id</p>
            </div>
            <img src="{{ asset('img/logo-jawabarat.png') }}" alt="Logo Jawa Barat" class="header-logo-right">
        </div>

        <!-- TITLE -->
        <div class="title">
            <h3>Surat Izin {{ \App\Models\PegawaiIzin::JENIS_IZIN_LABELS[$pegawai->jenis_izin] ?? $pegawai->jenis_izin }}</h3>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <p class="no-indent">Yang bertanda tangan di bawah ini, menerangkan bahwa:</p>

            <table class="data-table">
                <tr>
                    <td class="label">Nama</td>
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

            <p>Mengajukan permohonan izin <strong>{{ \App\Models\PegawaiIzin::JENIS_IZIN_LABELS[$pegawai->jenis_izin] ?? $pegawai->jenis_izin }}</strong> dengan rincian sebagai berikut:</p>

            <table class="data-table">
                <tr>
                    <td class="label">Jenis Izin</td>
                    <td class="colon">:</td>
                    <td>{{ \App\Models\PegawaiIzin::JENIS_IZIN_LABELS[$pegawai->jenis_izin] ?? $pegawai->jenis_izin }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Mulai</td>
                    <td class="colon">:</td>
                    <td>{{ $pegawai->tanggal_mulai->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Selesai</td>
                    <td class="colon">:</td>
                    <td>{{ $pegawai->tanggal_selesai->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Lama Izin</td>
                    <td class="colon">:</td>
                    <td><strong>{{ $pegawai->tanggal_mulai->diffInDays($pegawai->tanggal_selesai) + 1 }} Hari</strong></td>
                </tr>
                @if($pegawai->keterangan)
                <tr>
                    <td class="label">Keterangan</td>
                    <td class="colon">:</td>
                    <td>{{ $pegawai->keterangan }}</td>
                </tr>
                @endif
            </table>

            <p>Demikian surat izin ini dibuat dengan sebenarnya untuk dapat digunakan sebagaimana mestinya.</p>
        </div>

        <!-- SIGNATURE -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">
                    <p>Mengetahui,</p>
                    <p>Kepala Cabang Dinas Pendidikan</p>
                    <p>Wilayah XIII,</p>
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

    <script>
        function printClean() {
            // Buat iframe tersembunyi untuk print tanpa watermark browser
            var iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            document.body.appendChild(iframe);

            var doc = iframe.contentWindow.document;
            doc.open();
            // Salin seluruh konten halaman ke iframe
            doc.write('<!DOCTYPE html><html lang="id"><head>');
            doc.write('<meta charset="UTF-8">');
            doc.write('<style>');
            // Ambil semua style dari halaman utama
            var styles = document.querySelectorAll('style');
            styles.forEach(function(style) {
                doc.write(style.innerHTML);
            });
            // Override @page margin jadi 0 di iframe
            doc.write('@page { size: A4 portrait; margin: 0; }');
            doc.write('.page { padding: 15mm 20mm !important; max-width: 100% !important; }');
            doc.write('.no-print { display: none !important; }');
            doc.write('</style>');
            doc.write('</head><body>');
            doc.write(document.querySelector('.page').outerHTML);
            doc.write('</body></html>');
            doc.close();

            // Tunggu images load lalu print
            iframe.contentWindow.onload = function() {
                setTimeout(function() {
                    iframe.contentWindow.print();
                    // Hapus iframe setelah print
                    setTimeout(function() {
                        document.body.removeChild(iframe);
                    }, 1000);
                }, 500);
            };
        }
    </script>
</body>
</html>
