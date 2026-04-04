<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ScoringSheet;

class ScoringSheetPdfExport
{
    protected $sheet;

    public function __construct(ScoringSheet $sheet)
    {
        $this->sheet = $sheet;
        $this->sheet->load(['user', 'requests.variants.entries.category', 'requests.variants.entries.category.parent']);
    }

    public function download()
    {
        $data = [
            'sheet' => $this->sheet,
            'user' => $this->sheet->user,
            'requests' => $this->sheet->requests,
            'generated_at' => now()->format('d.m.Y H:i:s'),
            'status_name' => $this->getStatusName($this->sheet->status),
        ];

        $pdf = Pdf::loadView('exports.scoring-sheet-pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('vedomost_' . $this->sheet->period_date->format('Y_m') . '.pdf');
    }

    private function getStatusName($status): string
    {
        $map = [
            'draft' => 'Черновик',
            'confirmed' => 'Подтверждена',
            'approved' => 'Утверждена',
        ];
        return $map[$status] ?? $status;
    }
}
