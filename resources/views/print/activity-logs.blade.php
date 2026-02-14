<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('img/logo-cadisdik.png') }}">
    <title>Log Aktivitas Sistem — Cadisdik XIII</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 20mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.4;
            background: #fff;
        }

        .page {
            max-width: 297mm;
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

        /* === FILTER INFO === */
        .filter-info {
            margin: 15px 0;
            padding: 10px;
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 10pt;
        }

        .filter-info p {
            margin: 3px 0;
        }

        .filter-info strong {
            font-weight: bold;
        }

        /* === TABLE === */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9.5pt;
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

        .data-table td.waktu {
            width: 120px;
        }

        .data-table td.user {
            width: 120px;
        }

        .data-table td.modul {
            width: 100px;
        }

        .data-table td.aksi {
            text-align: center;
            width: 70px;
        }

        /* === BADGES === */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success { background: #D1FAE5; color: #065F46; }
        .badge-info { background: #DBEAFE; color: #1E40AF; }
        .badge-warning { background: #FEF3C7; color: #92400E; }
        .badge-danger { background: #FEE2E2; color: #991B1B; }
        .badge-gray { background: #F3F4F6; color: #374151; }
        .badge-primary { background: #E0E7FF; color: #3730A3; }

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

        /* === PRINT BUTTON === */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #10B981;
            color: white;
            border: none;
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

        @media print {
            .print-btn, .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
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
            <h3>Log Aktivitas Sistem</h3>
            <p>Dicetak pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</p>
        </div>

        <!-- FILTER INFO -->
        @php
            $filterActive = request()->has('user_id') || request()->has('log_name') || request()->has('event') || request()->has('start_date') || request()->has('end_date');
        @endphp

        @if($filterActive)
        <div class="filter-info no-print">
            <p><strong>Filter yang Diterapkan:</strong></p>
            @if(request()->has('user_id') && request()->user_id)
                @php
                    $user = \App\Models\User::find(request()->user_id);
                @endphp
                <p>• User: {{ $user ? $user->name : '-' }}</p>
            @endif
            @if(request()->has('log_name') && request()->log_name)
                <p>• Modul: {{ \App\Filament\Resources\ActivityLogResource::getLogNameLabel(request()->log_name) }}</p>
            @endif
            @if(request()->has('event') && request()->event)
                <p>• Event: {{ ucfirst(request()->event) }}</p>
            @endif
            @if(request()->has('start_date') && request()->start_date)
                <p>• Tanggal Mulai: {{ \Carbon\Carbon::parse(request()->start_date)->translatedFormat('d F Y') }}</p>
            @endif
            @if(request()->has('end_date') && request()->end_date)
                <p>• Tanggal Akhir: {{ \Carbon\Carbon::parse(request()->end_date)->translatedFormat('d F Y') }}</p>
            @endif
            <p>• Batas Data: {{ $limit }} log terakhir</p>
        </div>
        @endif

        <!-- TABLE -->
        <table class="data-table">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th class="waktu">Waktu</th>
                    <th class="user">User</th>
                    <th class="modul">Modul</th>
                    <th class="aksi">Aksi</th>
                    <th>Deskripsi Aktivitas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activityLogs as $index => $log)
                <tr>
                    <td class="no">{{ $index + 1 }}</td>
                    <td class="waktu">{{ $log->created_at->translatedFormat('d/m/Y H:i:s') }}</td>
                    <td class="user">{{ $log->causer ? $log->causer->name : 'System' }}</td>
                    <td class="modul">
                        @php
                            $labelClass = match($log->log_name) {
                                'buku_tamu' => 'badge-success',
                                'pegawai_izin' => 'badge-info',
                                'auth' => 'badge-warning',
                                'cetak' => 'badge-gray',
                                'user' => 'badge-danger',
                                'dropdown_option' => 'badge-primary',
                                'pegawai' => 'badge-info',
                                'pengaturan' => 'badge-warning',
                                default => 'badge-gray',
                            };
                            $label = \App\Filament\Resources\ActivityLogResource::getLogNameLabel($log->log_name);
                        @endphp
                        <span class="badge {{ $labelClass }}">{{ $label }}</span>
                    </td>
                    <td class="aksi">
                        @php
                            $eventClass = match($log->event) {
                                'created' => 'badge-success',
                                'updated' => 'badge-warning',
                                'deleted' => 'badge-danger',
                                'login' => 'badge-info',
                                'logout' => 'badge-gray',
                                'print' => 'badge-primary',
                                default => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $eventClass }}">{{ ucfirst($log->event) }}</span>
                    </td>
                    <td>{{ $log->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #999;">
                        Tidak ada data log aktivitas
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- SUMMARY -->
        <div class="summary">
            <p><strong>Total Data:</strong> {{ $activityLogs->count() }} log aktivitas</p>
            @if($filterActive)
                <p><strong>Catatan:</strong> Data yang ditampilkan telah difilter sesuai kriteria yang dipilih</p>
            @endif
        </div>

        <!-- SIGNATURE -->
        <div class="signature-section">
            <div class="signature-box">
                <p>Ciamis, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin-top: 10px;">Kepala Cabang Dinas Pendidikan</p>
                <p>Wilayah XIII,</p>
                <p class="name">{{ $ketuaKcd->formatted_nama }}</p>
                <p style="font-size: 9pt; margin-top: 3px;">{{ $ketuaKcd->formatted_nip }}</p>
            </div>
        </div>
    </div>
</body>
</html>
