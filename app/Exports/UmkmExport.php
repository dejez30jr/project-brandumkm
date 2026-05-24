<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UmkmExport implements FromCollection, WithHeadings
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    private function fileUrl(?string $path): string
    {
        if (!$path) return '-';
        return url('storage/' . $path);
    }

    public function collection()
    {
        return collect($this->records)->map(function ($item) {
            return [
                $item->nama_usaha,
                $item->nama_pemilik,
                $item->alamat_usaha,
                $item->no_wa,
                $item->radius ?? '-',
                $item->jam_buka ?? '-',
                $item->jam_tutup ?? '-',
                $item->request_text ?? '-',
                $item->catatan ?? '-',

                $item->no_rekening ?? '-',
                $item->nama_bank ?? '-',
                $item->atas_nama_rekening ?? '-',

                $item->latitude ?? '-',
                $item->longitude ?? '-',
                $item->sharelock_url ?? '-',

                $item->depan_atas_w ?? '-',
                $item->depan_atas_h ?? '-',
                $item->depan_panel_atas_m2 ?? '-',
                $item->depan_bawah_w ?? '-',
                $item->depan_bawah_h ?? '-',
                $item->depan_panel_bawah_m2 ?? '-',

                $item->kanan_atas_w ?? '-',
                $item->kanan_atas_h ?? '-',
                $item->kanan_panel_atas_m2 ?? '-',
                $item->kanan_bawah_w ?? '-',
                $item->kanan_bawah_h ?? '-',
                $item->kanan_panel_bawah_m2 ?? '-',

                $item->kiri_atas_w ?? '-',
                $item->kiri_atas_h ?? '-',
                $item->kiri_panel_atas_m2 ?? '-',
                $item->kiri_bawah_w ?? '-',
                $item->kiri_bawah_h ?? '-',
                $item->kiri_panel_bawah_m2 ?? '-',

                $item->total_area_branding ?? '-',
                $item->memenuhi_kriteria ? 'Ya' : 'Tidak',

                ucfirst(str_replace('_', ' ', $item->status)),
                $item->alasan_reject ?? '-',

                optional($item->approved_at)->format('d-m-Y H:i') ?? '-',
                $item->approvedBy?->name ?? '-',

                $this->fileUrl($item->foto_depan),
                $this->fileUrl($item->foto_kanan),
                $this->fileUrl($item->foto_kiri),
                $this->fileUrl($item->foto_plang_alfamart),
                $this->fileUrl($item->foto_tampak_jauh),
                $this->fileUrl($item->video_validasi),

                $item->kota?->nama ?? '-',
                $item->submittedBy?->name ?? '-',

                optional($item->created_at)->format('d-m-Y H:i'),
                optional($item->updated_at)->format('d-m-Y H:i'),

                $this->fileUrl($item->design_final),
                $this->fileUrl($item->design_gerobak_depan),
                $this->fileUrl($item->design_gerobak_kiri),
                $this->fileUrl($item->design_gerobak_kanan),
                $item->umkmDesign?->nama_desainer ?? '-',

                $this->fileUrl($item->stiker_tampak_depan),
                $this->fileUrl($item->stiker_tampak_kanan),
                $this->fileUrl($item->stiker_tampak_kiri),
                $this->fileUrl($item->foto_wide),
                $item->tanggal_pasang ?? '-',
                $item->nama_team_pasang ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Usaha',
            'Nama Pemilik',
            'Alamat Usaha',
            'No WA',
            'Radius',
            'Jam Buka',
            'Jam Tutup',
            'Request Teks Branding',
            'Catatan',

            'No Rekening',
            'Nama Bank',
            'Atas Nama Rekening',

            'Latitude',
            'Longitude',
            'Sharelock URL',

            'Depan Atas W (cm)',
            'Depan Atas H (cm)',
            'Depan Panel Atas (m²)',
            'Depan Bawah W (cm)',
            'Depan Bawah H (cm)',
            'Depan Panel Bawah (m²)',

            'Kanan Atas W (cm)',
            'Kanan Atas H (cm)',
            'Kanan Panel Atas (m²)',
            'Kanan Bawah W (cm)',
            'Kanan Bawah H (cm)',
            'Kanan Panel Bawah (m²)',

            'Kiri Atas W (cm)',
            'Kiri Atas H (cm)',
            'Kiri Panel Atas (m²)',
            'Kiri Bawah W (cm)',
            'Kiri Bawah H (cm)',
            'Kiri Panel Bawah (m²)',

            'Total Area Branding (m²)',
            'Memenuhi Kriteria',

            'Status',
            'Alasan Reject',

            'Tanggal Approve',
            'Diapprove Oleh',

            'Foto Depan',
            'Foto Kanan',
            'Foto Kiri',
            'Foto Plang Alfamart',
            'Foto Tampak Jauh',
            'Video Validasi',

            'Kota',
            'PIC Lapangan',

            'Tanggal Submit',
            'Terakhir Update',

            'Design Final',
            'Design Gerobak Depan',
            'Design Gerobak Kiri',
            'Design Gerobak Kanan',
            'Nama Desainer',

            'Stiker Tampak Depan',
            'Stiker Tampak Kanan',
            'Stiker Tampak Kiri',
            'Foto Wide',
            'Tanggal Pasang',
            'Nama Team Pasang',
        ];
    }
}
