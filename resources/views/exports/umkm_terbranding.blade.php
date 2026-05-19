<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export UMKM</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #111827;
        }

        .header p {
            margin: 4px 0;
            color: #6b7280;
        }

        .info-box {
            margin-bottom: 15px;
            padding: 8px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .info-box p {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f59e0b;
            color: white;
            font-weight: bold;
            font-size: 10px;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
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

        .kriteria-ya {
            color: #10b981;
            font-weight: bold;
        }

        .kriteria-tidak {
            color: #ef4444;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #6b7280;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>DATA UMKM TERBRANDING</h1>

        <p>
            Kota :
            <strong>{{ $kota ?? '-' }}</strong>
        </p>

        <p>
            Dicetak pada :
            {{ now()->format('d M Y H:i') }}
        </p>

        <p>
            Total Data :
            {{ $records->count() }} UMKM
        </p>
    </div>

    <div class="info-box">
        <p>
            <strong>Laporan Export Data UMKM</strong>
        </p>

        <p>
            Sistem Management UMKM Terbranding
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="25">No</th>
                <th>Nama Usaha</th>
                <th>Nama Pemilik</th>
                <th>Alamat</th>
                <th>No WA</th>
                <th>Kota</th>
                <th>Area</th>
                <th>Kriteria</th>
                <th>Status</th>
                <th>PIC</th>
                <th>Tanggal Submit</th>
            </tr>
        </thead>

        <tbody>
            @foreach($records as $index => $umkm)
                <tr>

                    <td>
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $umkm->nama_usaha ?? '-' }}
                    </td>

                    <td>
                        {{ $umkm->nama_pemilik ?? '-' }}
                    </td>

                    <td>
                        {{ \Illuminate\Support\Str::limit($umkm->alamat_usaha ?? '-', 40) }}
                    </td>

                    <td>
                        {{ $umkm->no_wa ?? '-' }}
                    </td>

                    <td>
                        {{ $umkm->kota?->nama ?? '-' }}
                    </td>

                    <td>
                        {{ number_format($umkm->total_area_branding ?? 0, 2) }} m2
                    </td>

                    <td
                        class="{{ ($umkm->memenuhi_kriteria ?? false) ? 'kriteria-ya' : 'kriteria-tidak' }}">
                        
                        {{ ($umkm->memenuhi_kriteria ?? false) ? 'Ya' : 'Tidak' }}

                    </td>

                    <td
                        class="status-{{ $umkm->status ?? 'pending' }}">

                        {{ ucfirst($umkm->status ?? '-') }}

                    </td>

                    <td>
                        {{ $umkm->submittedBy?->name ?? '-' }}
                    </td>

                    <td>
                        {{ optional($umkm->created_at)->format('d/m/Y') }}
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>
            UMKM Branding Gerobak Alfamart -
            {{ now()->format('Y') }}
        </p>
    </div>

</body>
</html>