<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UmkmExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();

        // Header bold + background
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F59E0B']],
        ]);

        // Text wrap untuk semua cell
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getAlignment()->setVertical('top');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Nama Usaha
            'B' => 18, // Nama Pemilik
            'C' => 30, // Alamat
            'D' => 15, // No WA
            'E' => 10, // Radius
            'F' => 10, // Jam Buka
            'G' => 10, // Jam Tutup
            'H' => 25, // Request Text
            'I' => 25, // Catatan
            'J' => 18, // No Rekening
            'K' => 12, // Nama Bank
            'L' => 18, // Atas Nama
            'M' => 14, // Lat
            'N' => 14, // Lng
            'O' => 35, // Sharelock URL
            'P' => 10, 'Q' => 10, 'R' => 12,
            'S' => 10, 'T' => 10, 'U' => 12,
            'V' => 10, 'W' => 10, 'X' => 12,
            'Y' => 10, 'Z' => 10, 'AA' => 12,
            'AB' => 10, 'AC' => 10, 'AD' => 12,
            'AE' => 10, 'AF' => 10, 'AG' => 12,
            'AH' => 14, // Total Area
            'AI' => 12, // Kriteria
            'AJ' => 18, // Status
            'AK' => 30, // Alasan Reject
            'AL' => 18, // Tgl Approve
            'AM' => 18, // Approved By
            'AN' => 35, 'AO' => 35, 'AP' => 35, 'AQ' => 35, 'AR' => 35, 'AS' => 35, // Foto/Video URLs
            'AT' => 14, // Kota
            'AU' => 18, // PIC
            'AV' => 18, // Tgl Submit
            'AW' => 18, // Updated
            'AX' => 35, 'AY' => 35, 'AZ' => 35, 'BA' => 35, // Design URLs
            'BB' => 18, // Nama Desainer
            'BC' => 35, 'BD' => 35, 'BE' => 35, 'BF' => 35, // Stiker URLs
            'BG' => 14, // Tgl Pasang
            'BH' => 18, // Team Pasang
        ];
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
                $item->nama_usaha ?? '-',
                $item->nama_pemilik ?? '-',
                $item->alamat_usaha ?? '-',
                $item->no_wa ?? '-',
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

            'Design Final (FA)',
            'Mockup Gerobak Depan',
            'Mockup Gerobak Kiri',
            'Mockup Gerobak Kanan',
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
