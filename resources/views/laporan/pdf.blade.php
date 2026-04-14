<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Raw Data HERA</title>
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
        .status-normal { color: #059669; font-weight: bold; }
        .status-warning { color: #d97706; font-weight: bold; }
        .status-danger { color: #dc2626; font-weight: bold; }
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
        <h1>Laporan Monitoring Telemetri - {{ $app_settings['nama_aplikasi'] ?? 'HERA' }}</h1>
        <p>{{ $app_settings['deskripsi'] ?? 'Hexavalent Chromium Real-time Analytics' }}</p>
        <p>{{ $app_settings['nama_instansi'] ?? 'Universitas Hasanuddin' }}</p>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td width="15%"><strong>Tanggal Cetak</strong></td>
                <td>: {{ date('d F Y H:i:s') }}</td>
                <td width="15%"><strong>Total Data</strong></td>
                <td>: {{ $readings->count() }} Baris</td>
            </tr>
            <tr>
                <td><strong>Filter Status</strong></td>
                <td>: {{ request('status', 'Semua Status') }}</td>
                <td><strong>Filter Waktu</strong></td>
                <td>: {{ request('from_date') ? request('from_date') . ' s/d ' . request('to_date') : 'Keseluruhan' }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal & Waktu</th>
                <th width="10%">Cr (mg/L)</th>
                <th width="8%">pH</th>
                <th width="10%">EC (µS/cm)</th>
                <th width="10%">TDS (mg/L)</th>
                <th width="12%">Suhu Air/Ling.</th>
                <th width="10%">Kelembapan</th>
                <th width="10%">Tegangan</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($readings as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>{{ number_format($row->cr_estimated, 2) }}</td>
                    <td>{{ number_format($row->ph, 2) }}</td>
                    <td>{{ number_format($row->ec, 1) }}</td>
                    <td>{{ number_format($row->tds, 1) }}</td>
                    <td>{{ $row->suhu_air }} / {{ $row->suhu_lingkungan }} °C</td>
                    <td>{{ $row->kelembapan }}%</td>
                    <td>{{ $row->tegangan }}V</td>
                    <td class="status-{{ $row->status }}">{{ strtoupper($row->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="padding: 20px;">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh sistem {{ $app_settings['nama_aplikasi'] ?? 'HERA' }} pada {{ date('d-m-Y H:i:s') }}.<br>
        © {{ $app_settings['tahun'] ?? date('Y') }} {{ $app_settings['nama_instansi'] ?? 'Instansi' }}. Hak Cipta Dilindungi Undang-Undang.
    </div>

</body>
</html>
