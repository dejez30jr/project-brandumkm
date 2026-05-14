<?php

namespace App\Http\Controllers;

use App\Models\Umkm;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class UmkmExportController extends Controller
{
    public function exportPdf(Request $request)
    {
        $query = Umkm::with(['kota', 'submittedBy']);

        // Filter dari request
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->kota_id) {
            $query->where('kota_id', $request->kota_id);
        }

        // Filter berdasarkan role user
        $user = auth()->user();
        if ($user->isPicLapangan()) {
            $query->where('submitted_by', $user->id);
        } elseif ($user->isClient() && $user->kota_id) {
            $query->where('kota_id', $user->kota_id);
        }

        $umkms = $query->get();

        $pdf = Pdf::loadView('exports.umkm-pdf', [
            'umkms' => $umkms,
            'title' => 'Data UMKM Branding Gerobak',
            'date' => now()->format('d F Y'),
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('data-umkm-' . now()->format('Y-m-d') . '.pdf');
    }
}