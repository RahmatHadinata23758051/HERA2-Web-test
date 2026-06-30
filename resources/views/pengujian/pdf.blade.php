<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengujian Lapangan HERA</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #1a56db;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 12px;
        }
        .meta-info {
            margin-bottom: 15px;
        }
        .meta-info table {
            width: 100%;
            border: none;
        }
        .meta-info td {
            padding: 2px;
            border: none;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }
        table.data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-bold { font-weight: bold; }
        .text-primary { color: #1a56db; }
        .text-purple { color: #7e22ce; }
        .text-indigo { color: #4f46e5; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Pengujian Lapangan - HERA 2.0</h1>
        <p>Hexavalent Chromium & Nickel Real-time Mobile Analytics</p>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td width="15%"><strong>Tanggal Cetak</strong></td>
                <td>: {{ date('d F Y H:i:s') }}</td>
                <td width="15%"><strong>Total Data</strong></td>
                <td>: {{ $tests->count() }} Titik Pengujian</td>
            </tr>
            <tr>
                <td><strong>Filter Lokasi</strong></td>
                <td>: {{ request('location', 'Semua Lokasi') }}</td>
                <td><strong>Filter Waktu</strong></td>
                <td>: {{ request('from_date') ? request('from_date') . ' s/d ' . request('to_date') : 'Keseluruhan' }}</td>
            </tr>
            <tr>
                <td><strong>Filter Logam</strong></td>
                <td>: {{ request('metal') === 'cr' ? 'Hanya Chromium' : (request('metal') === 'ni' ? 'Hanya Nikel' : 'Semua') }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="14%">Waktu Validasi</th>
                <th width="12%">Nama Petugas</th>
                <th width="14%">GPS Koordinat</th>
                <th width="7%">Alt. (m)</th>
                <th width="5%">pH</th>
                <th width="8%">TDS (ppm)</th>
                <th width="8%">EC (mS)</th>
                <th width="10%">Suhu Air/Udr</th>
                <th width="9%">Cr Est. (mg/L)</th>
                <th width="10%">Ni Est. (mg/L)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tests as $index => $test)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $test->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-bold">{{ optional($test->user)->name ?? 'Unknown' }}</td>
                    <td>{{ number_format($test->latitude, 4) }}, {{ number_format($test->longitude, 4) }}</td>
                    <td>{{ $test->altitude !== null ? number_format($test->altitude, 1) : '-' }}</td>
                    <td>{{ $test->ph ?? '-' }}</td>
                    <td>{{ $test->tds ?? '-' }}</td>
                    <td>{{ $test->ec ?? '-' }}</td>
                    <td>{{ $test->suhu_air ?? '-' }} / {{ $test->suhu_lingkungan ?? '-' }} °C</td>
                    <td class="text-bold text-purple">{{ $test->cr_estimated !== null ? number_format($test->cr_estimated, 5) : '-' }}</td>
                    <td class="text-bold text-indigo">{{ $test->ni_estimated !== null ? number_format($test->ni_estimated, 5) : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="padding: 20px;">Tidak ada data pengujian lapangan ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh sistem HERA 2.0 pada {{ date('d-m-Y H:i:s') }}.<br>
        Hak Cipta Dilindungi Undang-Undang.
    </div>

</body>
</html>
