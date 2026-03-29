<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User — Cadisdik XIII</title>
    @php
        $settings = \App\Models\PengaturanKcd::getSettings();
        $paperSize = \App\Support\PrintPaperSize::normalize($settings->paper_size ?? 'a4');
        $pageSize = \App\Support\PrintPaperSize::pageSize($paperSize, 'portrait');
        $pageWidth = \App\Support\PrintPaperSize::pageWidth($paperSize, 'portrait');
    @endphp
    <style>
        @page {
            size: {{ $pageSize }};
            margin: 10mm 14mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #111827;
            background: #ffffff;
            line-height: 1.45;
        }

        .page {
            max-width: {{ $pageWidth }};
            margin: 0 auto;
            padding: 6mm;
        }

        .title {
            text-align: center;
            margin-bottom: 14px;
        }

        .title h2 {
            font-size: 16pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .title p {
            font-size: 10pt;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 10pt;
        }

        th,
        td {
            border: 1px solid #111827;
            padding: 7px 6px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            font-weight: 700;
        }

        td.no,
        td.center {
            text-align: center;
        }

        .summary {
            margin-top: 12px;
            font-size: 10pt;
            color: #374151;
        }

        .signature {
            margin-top: 28px;
            display: flex;
            justify-content: flex-end;
        }

        .signature-box {
            width: 250px;
            text-align: center;
            font-size: 10pt;
        }

        .signature-box .name {
            margin-top: 54px;
            font-weight: 700;
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 210px;
            padding-bottom: 2px;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            border: none;
            border-radius: 8px;
            padding: 10px 22px;
            background: #0f766e;
            color: #ffffff;
            cursor: pointer;
            font-size: 14px;
            z-index: 999;
        }

        .print-btn:hover {
            background: #115e59;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .page {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <button class="print-btn no-print" onclick="window.print()">Print</button>

    <div class="page">
        <div class="title">
            <h2>Data User</h2>
            <p>Cabang Dinas Pendidikan Wilayah XIII</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 42px; text-align: center;">No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th style="width: 170px;">Role</th>
                    <th style="width: 120px; text-align: center;">Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $index => $user)
                    <tr>
                        <td class="no">{{ $index + 1 }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role_user->name ?? '-' }}</td>
                        <td class="center">{{ optional($user->created_at)->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center">Tidak ada data user yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p class="summary">Total data: {{ $users->count() }} user.</p>

        <div class="signature">
            <div class="signature-box">
                <p>Bandung, {{ now()->translatedFormat('d F Y') }}</p>
                <p>{{ $kepalaCabdin?->jabatan ?? 'Kepala Cabang Dinas Pendidikan Wilayah XIII' }}</p>
                <p class="name">{{ $kepalaCabdin?->nama ?? '(........................................)' }}</p>
                <p>{{ $kepalaCabdin?->nip ? 'NIP. ' . $kepalaCabdin->nip : '' }}</p>
            </div>
        </div>
    </div>
</body>

</html>
