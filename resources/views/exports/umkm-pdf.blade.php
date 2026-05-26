<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h1 { margin: 0; font-size: 16px; color: #333; }
        .header p { margin: 3px 0; color: #666; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #f59e0b; color: white; font-weight: bold; font-size: 8px; white-space: nowrap; }
        tr:nth-child(even) { background-color: #fafafa; }
        td { max-width: 120px; overflow-wrap: break-word; }
        .status-approved, .status-design_approved { color: #10b981; font-weight: bold; }
        .status-pending { color: #f59e0b; font-weight: bold; }
        .status-rejected { color: #ef4444; font-weight: bold; }
        .status-branded, .status-terbranding_final { color: #059669; font-weight: bold; }
        .status-designing, .status-design_review, .status-menunggu_didesain { color: #3b82f6; font-weight: bold; }
        .status-waiting_installation { color: #8b5cf6; font-weight: bold; }
        .status-revision_needed, .status-revision { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 15px; text-align: right; font-size: 8px; color: #999; }
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
                <th>Pemilik</th>
                <th>Alamat</th>
                <th>No WA</th>
                <th>Kota</th>
                <th>Koordinat GPS</th>
                <th>Total Area (m²)</th>
                <th>Kriteria</th>
                <th>Status</th>
                <th>PIC Lapangan</th>
                <th>Desainer</th>
                <th>Team Pasang</th>
                <th>Tgl Pasang</th>
                <th>Tgl Submit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($umkms as $index => $umkm)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $umkm->nama_usaha }}</td>
                <td>{{ $umkm->nama_pemilik }}</td>
                <td>{{ $umkm->alamat_usaha }}</td>
                <td>{{ $umkm->no_wa }}</td>
                <td>{{ $umkm->kota?->nama ?? '-' }}</td>
                <td>{{ $umkm->latitude && $umkm->longitude ? $umkm->latitude . ', ' . $umkm->longitude : '-' }}</td>
                <td>{{ number_format($umkm->total_area_branding ?? 0, 2) }}</td>
                <td style="color: {{ $umkm->memenuhi_kriteria ? '#10b981' : '#ef4444' }}; font-weight: bold;">
                    {{ $umkm->memenuhi_kriteria ? 'Ya' : 'Tidak' }}
                </td>
                <td class="status-{{ $umkm->status }}">
                    {{ ucfirst(str_replace('_', ' ', $umkm->status)) }}
                </td>
                <td>{{ $umkm->submittedBy?->name ?? '-' }}</td>
                <td>{{ $umkm->umkmDesign?->nama_desainer ?? '-' }}</td>
                <td>{{ $umkm->nama_team_pasang ?? '-' }}</td>
                <td>{{ $umkm->tanggal_pasang ?? '-' }}</td>
                <td>{{ $umkm->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>UMKM Branding Gerobak - Dicetak {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
