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

    public function collection()
    {
        return collect($this->records)->map(function ($item) {

            return [

                'Nama Usaha' => $item->nama_usaha,
                'Nama Pemilik' => $item->nama_pemilik,
                'Alamat Usaha' => $item->alamat_usaha,
                'No WA' => $item->no_wa,
                'Radius' => $item->radius,
                'Jam Buka' => $item->jam_buka,
                'Jam Tutup' => $item->jam_tutup,
                'Request Teks Branding' => $item->request_text,
                'Catatan' => $item->catatan,

                'No Rekening' => $item->no_rekening,
                'Nama Bank' => $item->nama_bank,
                'Atas Nama Rekening' => $item->atas_nama_rekening,

                'Latitude' => $item->latitude,
                'Longitude' => $item->longitude,
                'Sharelock URL' => $item->sharelock_url,

                'Depan Panel Atas (m2)' => $item->depan_panel_atas_m2,
                'Depan Panel Tengah (m2)' => $item->depan_panel_tengah_m2,
                'Depan Panel Bawah (m2)' => $item->depan_panel_bawah_m2,

                'Kanan Panel Atas (m2)' => $item->kanan_panel_atas_m2,
                'Kanan Panel Tengah (m2)' => $item->kanan_panel_tengah_m2,
                'Kanan Panel Bawah (m2)' => $item->kanan_panel_bawah_m2,

                'Kiri Panel Atas (m2)' => $item->kiri_panel_atas_m2,
                'Kiri Panel Tengah (m2)' => $item->kiri_panel_tengah_m2,
                'Kiri Panel Bawah (m2)' => $item->kiri_panel_bawah_m2,

                'Total Area Branding' => $item->total_area_branding,

                'Memenuhi Kriteria' => $item->memenuhi_kriteria ? 'Ya' : 'Tidak',

                'Status' => $item->status,
                'Alasan Reject' => $item->alasan_reject,

                'Approved At' => $item->approved_at,
                'Approved By' => $item->approved_by,

                'Foto Depan' => $item->foto_depan,
                'Foto Kanan' => $item->foto_kanan,
                'Foto Kiri' => $item->foto_kiri,
                'Foto Plang Alfamart' => $item->foto_plang_alfamart,

                'Video Validasi' => $item->video_validasi,

                'Kota' => $item->kota?->nama ?? '-',

                'Submitted By' => $item->submittedBy?->name ?? '-',

                'Created At' => optional($item->created_at)->format('d-m-Y H:i'),
                'Updated At' => optional($item->updated_at)->format('d-m-Y H:i'),

                'Depan Atas W' => $item->depan_atas_w,
                'Depan Atas H' => $item->depan_atas_h,

                'Depan Tengah W' => $item->depan_tengah_w,
                'Depan Tengah H' => $item->depan_tengah_h,

                'Depan Bawah W' => $item->depan_bawah_w,
                'Depan Bawah H' => $item->depan_bawah_h,

                'Kanan Atas W' => $item->kanan_atas_w,
                'Kanan Atas H' => $item->kanan_atas_h,

                'Kanan Tengah W' => $item->kanan_tengah_w,
                'Kanan Tengah H' => $item->kanan_tengah_h,

                'Kanan Bawah W' => $item->kanan_bawah_w,
                'Kanan Bawah H' => $item->kanan_bawah_h,

                'Kiri Atas W' => $item->kiri_atas_w,
                'Kiri Atas H' => $item->kiri_atas_h,

                'Kiri Tengah W' => $item->kiri_tengah_w,
                'Kiri Tengah H' => $item->kiri_tengah_h,

                'Kiri Bawah W' => $item->kiri_bawah_w,
                'Kiri Bawah H' => $item->kiri_bawah_h,

                'Design Final' => $item->design_final,

                'Design Gerobak Depan' => $item->design_gerobak_depan,
                'Design Gerobak Kiri' => $item->design_gerobak_kiri,
                'Design Gerobak Kanan' => $item->design_gerobak_kanan,

                'Stiker Tampak Depan' => $item->stiker_tampak_depan,
                'Stiker Tampak Kanan' => $item->stiker_tampak_kanan,
                'Stiker Tampak Kiri' => $item->stiker_tampak_kiri,
                'Foto Wide' => $item->foto_wide,
                'Tanggal Pasang' => $item->tanggal_pasang,
                'Nama Team Pasang' => $item->nama_team_pasang,
                'Nama Desainer' => $item->umkmDesign?->nama_desainer ?? '-',
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

            'Depan Panel Atas (m2)',
            'Depan Panel Tengah (m2)',
            'Depan Panel Bawah (m2)',

            'Kanan Panel Atas (m2)',
            'Kanan Panel Tengah (m2)',
            'Kanan Panel Bawah (m2)',

            'Kiri Panel Atas (m2)',
            'Kiri Panel Tengah (m2)',
            'Kiri Panel Bawah (m2)',

            'Total Area Branding',

            'Memenuhi Kriteria',

            'Status',
            'Alasan Reject',

            'Approved At',
            'Approved By',

            'Foto Depan',
            'Foto Kanan',
            'Foto Kiri',
            'Foto Plang Alfamart',

            'Video Validasi',

            'Kota',

            'Submitted By',

            'Created At',
            'Updated At',

            'Depan Atas W',
            'Depan Atas H',

            'Depan Tengah W',
            'Depan Tengah H',

            'Depan Bawah W',
            'Depan Bawah H',

            'Kanan Atas W',
            'Kanan Atas H',

            'Kanan Tengah W',
            'Kanan Tengah H',

            'Kanan Bawah W',
            'Kanan Bawah H',

            'Kiri Atas W',
            'Kiri Atas H',

            'Kiri Tengah W',
            'Kiri Tengah H',

            'Kiri Bawah W',
            'Kiri Bawah H',

            'Design Final',

            'Design Gerobak Depan',
            'Design Gerobak Kiri',
            'Design Gerobak Kanan',

            'Stiker Tampak Depan',
            'Stiker Tampak Kanan',
            'Stiker Tampak Kiri',
            'Foto Wide',
            'Tanggal Pasang',
            'Nama Team Pasang',
            'Nama Desainer',
        ];
    }
}