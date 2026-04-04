<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ScoringSummaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $constructorSheets;
    protected $designerSheets;
    protected $date;

    public function __construct($constructorSheets, $designerSheets, $date)
    {
        $this->constructorSheets = $constructorSheets;
        $this->designerSheets = $designerSheets;
        $this->date = $date;
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->constructorSheets as $sheet) {
            $rows->push([
                'department' => 'Конструкторы',
                'full_name' => $sheet->user->full_name ?? '—',
                'status' => $sheet->status,
                'total_points' => $sheet->total_points,
                'entries_count' => $sheet->entries_count ?? 0,
            ]);
        }

        foreach ($this->designerSheets as $sheet) {
            $rows->push([
                'department' => 'Дизайнеры',
                'full_name' => $sheet->user->full_name ?? '—',
                'status' => $sheet->status,
                'total_points' => $sheet->total_points,
                'entries_count' => $sheet->entries_count ?? 0,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Подотдел',
            'Сотрудник',
            'Статус',
            'Баллы',
            'Количество записей',
        ];
    }

    public function map($row): array
    {
        $statusMap = [
            'draft' => 'Черновик',
            'confirmed' => 'Подтверждена',
            'approved' => 'Утверждена',
        ];

        return [
            $row['department'],
            $row['full_name'],
            $statusMap[$row['status']] ?? $row['status'],
            $row['total_points'],
            $row['entries_count'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3B82F6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $lastRow = $this->collection()->count() + 1;
        if ($lastRow > 1) {
            $sheet->getStyle('A1:E' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);
        }

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 15,
            'D' => 12,
            'E' => 18,
        ];
    }

    public function title(): string
    {
        return 'Сводка_' . date('Y_m', strtotime($this->date));
    }
}
