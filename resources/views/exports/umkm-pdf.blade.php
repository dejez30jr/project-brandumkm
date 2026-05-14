<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f59e0b;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-approved {
            color: #10b981;
            font-weight: bold;
        }
        .status-pending {
            color: #f59e0b;
            font-weight: bold;
        }
        .status-rejected {
            color: #ef4444;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
        .kriteria-ya {
            color: #10b981;
        }
        .kriteria-tidak {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Dicetak pada: {{ $date }}</p>
        <p>Total Data: {{ $umkms->count() }} UMKM</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Usaha</th>
                <th>Nama Pemilik</th>
                <th>Alamat</th>
                <th>No. WA</th>
                <th>Kota</th>
                <th>Area (m2)</th>
                <th>Kriteria</th>
                <th>Status</th>
                <th>PIC</th>
                <th>Tgl Submit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($umkms as $index => $umkm)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $umkm->nama_usaha }}</td>
                <td>{{ $umkm->nama_pemilik }}</td>
                <td>{{ Str::limit($umkm->alamat_usaha, 30) }}</td>
                <td>{{ $umkm->no_wa }}</td>
                <td>{{ $umkm->kota?->nama ?? '-' }}</td>
                <td>{{ number_format($umkm->total_area_branding ?? 0, 2) }}</td>
                <td class="{{ $umkm->memenuhi_kriteria ? 'kriteria-ya' : 'kriteria-tidak' }}">
                    {{ $umkm->memenuhi_kriteria ? 'Ya' : 'Tidak' }}
                </td>
                <td class="status-{{ $umkm->status }}">
                    {{ ucfirst($umkm->status) }}
                </td>
                <td>{{ $umkm->submittedBy?->name ?? '-' }}</td>
                <td>{{ $umkm->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>UMKM Branding Gerobak Alfamart - {{ now()->format('Y') }}</p>
    </div>
</body>
</html>