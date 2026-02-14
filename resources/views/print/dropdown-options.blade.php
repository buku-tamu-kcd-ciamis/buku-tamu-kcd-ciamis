<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Dropdown Options</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12pt;
            color: #333;
            background: #fff;
            padding: 30px;
            line-height: 1.5;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h2 {
            font-size: 18pt;
            margin-bottom: 8px;
            color: #222;
            font-weight: 600;
        }

        .header p {
            font-size: 11pt;
            color: #666;
            margin: 3px 0;
        }

        .category-section {
            margin-bottom: 35px;
        }

        .category-title {
            background: #f8f9fa;
            padding: 10px 15px;
            font-size: 14pt;
            font-weight: 600;
            margin-bottom: 12px;
            color: #444;
            border-radius: 4px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11pt;
        }

        .data-table th {
            background: #e9ecef;
            border: 1px solid #adb5bd;
            padding: 10px;
            font-weight: 600;
            text-align: left;
            color: #495057;
        }

        .data-table td {
            border: 1px solid #dee2e6;
            padding: 8px 10px;
            vertical-align: middle;
        }

        .data-table td.no {
            text-align: center;
            width: 50px;
            color: #6c757d;
        }

        .data-table td.order {
            text-align: center;
            width: 80px;
        }

        .data-table td.status {
            text-align: center;
            width: 100px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 10pt;
            font-weight: 500;
        }

        .status-aktif {
            background: #d1f2eb;
            color: #0f5132;
        }

        .status-nonaktif {
            background: #f8d7da;
            color: #842029;
        }

        .summary {
            margin-top: 25px;
            font-size: 11pt;
            color: #666;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        /* Print button */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 999;
            font-family: inherit;
        }

        .print-btn:hover {
            background: #0b5ed7;
        }

        @media print {
            .print-btn {
                display: none !important;
            }
            body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Cetak Halaman</button>

    <div class="header">
        <h2>Data Opsi Dropdown</h2>
        <p>Dicetak tanggal: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}</p>
        @if($category !== 'all')
            <p>Kategori: {{ $categoryLabels[$category] ?? $category }}</p>
        @else
            <p>Kategori: Semua Kategori</p>
        @endif
    </div>

    @foreach($options as $cat => $items)
        <div class="category-section">
            <div class="category-title">
                {{ $categoryLabels[$cat] ?? $cat }} - {{ $items->count() }} item
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th class="no">No</th>
                        <th>Nilai</th>
                        <th>Label Tampilan</th>
                        <th class="order">Urutan</th>
                        <th class="status">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                    <tr>
                        <td class="no">{{ $index + 1 }}</td>
                        <td>{{ $item->value }}</td>
                        <td>{{ $item->label }}</td>
                        <td class="order">{{ $item->sort_order }}</td>
                        <td class="status">
                            @if($item->is_active)
                                <span class="status-badge status-aktif">Aktif</span>
                            @else
                                <span class="status-badge status-nonaktif">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="summary">
        Total Keseluruhan: {{ $options->flatten()->count() }} opsi dropdown
    </div>
</body>
</html>
