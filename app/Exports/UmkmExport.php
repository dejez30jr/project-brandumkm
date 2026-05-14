<?php

namespace App\Exports;

use App\Models\Umkm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class UmkmExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected ?string $status;
    protected ?int $kotaId;

    public function __construct(?string $status = null, ?int $kotaId = null)
    {
        $this->status = $status;
        $this->kotaId = $kotaId;
    }

    public function collection()
    {
        $query = Umkm::with(['kota', 'submittedBy']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->kotaId) {
            $query->where('kota_id', $this->kotaId);
        }

        // Filter berdasarkan role user
        $user = auth()->user();
        if ($user->isPicLapangan()) {
            $query->where('submitted_by', $user->id);
        } elseif ($user->isClient() && $user->kota_id) {
            $query->where('kota_id', $user->kota_id);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Usaha',
            'Nama Pemilik',
            'Alamat',
            'No. WhatsApp',
            'Kota',
            'Radius',
            'Total Area (m2)',
            'Memenuhi Kriteria',
            'Status',
            'PIC Lapangan',
            'Tanggal Submit',
            'No. Rekening',
            'Nama Bank',
            'Atas Nama',
        ];
    }

    public function map($umkm): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $umkm->nama_usaha,
            $umkm->nama_pemilik,
            $umkm->alamat_usaha,
            $umkm->no_wa,
            $umkm->kota?->nama ?? '-',
            $umkm->radius ?? '-',
            $umkm->total_area_branding ?? 0,
            $umkm->memenuhi_kriteria ? 'Ya' : 'Tidak',
            ucfirst($umkm->status),
            $umkm->submittedBy?->name ?? '-',
            $umkm->created_at->format('d/m/Y'),
            $umkm->no_rekening ?? '-',
            $umkm->nama_bank ?? '-',
            $umkm->atas_nama_rekening ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}