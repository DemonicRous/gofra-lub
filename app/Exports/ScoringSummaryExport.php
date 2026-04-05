<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ScoringSummaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $constructorSheets;
    protected $designerSheets;
    protected $date;
    protected $constructorTotalPoints;
    protected $designerTotalPoints;
    protected $constructorTotalRequests;
    protected $designerTotalRequests;

    public function __construct($constructorSheets, $designerSheets, $date)
    {
        $this->constructorSheets = $constructorSheets;
        $this->designerSheets = $designerSheets;
        $this->date = $date;

        $this->constructorTotalPoints = $constructorSheets->sum('total_points');
        $this->designerTotalPoints = $designerSheets->sum('total_points');
        $this->constructorTotalRequests = $constructorSheets->sum('requests_count');
        $this->designerTotalRequests = $designerSheets->sum('requests_count');
    }

    public function collection()
    {
        $rows = collect();

        // Заголовок конструкторов
        if ($this->constructorSheets->isNotEmpty()) {
            $rows->push([
                'department' => 'ОТДЕЛ КОНСТРУКТОРОВ',
                'full_name' => '',
                'position' => '',
                'status' => '',
                'total_points' => '',
                'requests_count' => '',
                'percentage' => '',
                'is_header' => true
            ]);
        }

        // Данные конструкторов
        foreach ($this->constructorSheets as $sheet) {
            $totalPoints = $this->constructorTotalPoints;
            $percentage = $totalPoints > 0 ? round(($sheet->total_points / $totalPoints) * 100) : 0;

            $rows->push([
                'department' => '',
                'full_name' => $sheet->user->full_name ?? '—',
                'position' => $sheet->user->position->name ?? '',
                'status' => $this->getStatusName($sheet->status),
                'total_points' => $this->formatPoints($sheet->total_points),
                'requests_count' => $sheet->requests_count ?? 0,
                'percentage' => $percentage,
                'is_header' => false
            ]);
        }

        // Пустая строка-разделитель
        if ($this->constructorSheets->isNotEmpty() && $this->designerSheets->isNotEmpty()) {
            $rows->push([
                'department' => '',
                'full_name' => '',
                'position' => '',
                'status' => '',
                'total_points' => '',
                'requests_count' => '',
                'percentage' => '',
                'is_header' => false
            ]);
        }

        // Заголовок дизайнеров
        if ($this->designerSheets->isNotEmpty()) {
            $rows->push([
                'department' => 'ОТДЕЛ ДИЗАЙНЕРОВ',
                'full_name' => '',
                'position' => '',
                'status' => '',
                'total_points' => '',
                'requests_count' => '',
                'percentage' => '',
                'is_header' => true
            ]);
        }

        // Данные дизайнеров
        foreach ($this->designerSheets as $sheet) {
            $totalPoints = $this->designerTotalPoints;
            $percentage = $totalPoints > 0 ? round(($sheet->total_points / $totalPoints) * 100) : 0;

            $rows->push([
                'department' => '',
                'full_name' => $sheet->user->full_name ?? '—',
                'position' => $sheet->user->position->name ?? '',
                'status' => $this->getStatusName($sheet->status),
                'total_points' => $this->formatPoints($sheet->total_points),
                'requests_count' => $sheet->requests_count ?? 0,
                'percentage' => $percentage,
                'is_header' => false
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Сотрудник',
            'Должность',
            'Статус',
            'Баллы',
            'Кол-во заявок',
            '% от отдела'
        ];
    }

    public function map($row): array
    {
        if ($row['is_header']) {
            return [$row['department'], '', '', '', '', ''];
        }

        return [
            $row['full_name'],
            $row['position'],
            $row['status'],
            $row['total_points'],
            $row['requests_count'],
            $row['percentage'] . '%'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Общий стиль для всех ячеек – центрирование по вертикали и горизонтали
        $sheet->getStyle('A1:F' . ($sheet->getHighestRow()))->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Заголовки
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
                'name' => 'Segoe UI',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1B4F72'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(30);

        // Определяем диапазоны строк для отделов
        $rows = $this->collection();
        $currentRow = 2;
        $constructorStartRow = null;
        $constructorEndRow = null;
        $designerStartRow = null;
        $designerEndRow = null;
        $inConstructor = false;
        $inDesigner = false;

        foreach ($rows as $item) {
            if ($item['is_header'] && $item['department'] === 'ОТДЕЛ КОНСТРУКТОРОВ') {
                $constructorStartRow = $currentRow;
                $inConstructor = true;
                $inDesigner = false;
            } elseif ($item['is_header'] && $item['department'] === 'ОТДЕЛ ДИЗАЙНЕРОВ') {
                $designerStartRow = $currentRow;
                $inConstructor = false;
                $inDesigner = true;
            } elseif (!$item['is_header'] && $inConstructor) {
                $constructorEndRow = $currentRow;
            } elseif (!$item['is_header'] && $inDesigner) {
                $designerEndRow = $currentRow;
            }
            $currentRow++;
        }

        // Стили для строк конструкторов
        if ($constructorStartRow && $constructorEndRow) {
            for ($i = $constructorStartRow; $i <= $constructorEndRow; $i++) {
                $fillColor = ($i % 2 == 0) ? 'E6F2FF' : 'FFFFFF';
                $sheet->getStyle("A{$i}:F{$i}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $fillColor],
                    ],
                ]);
            }
        }

        // Стили для строк дизайнеров
        if ($designerStartRow && $designerEndRow) {
            for ($i = $designerStartRow; $i <= $designerEndRow; $i++) {
                $fillColor = ($i % 2 == 0) ? 'F0E6FF' : 'FFFFFF';
                $sheet->getStyle("A{$i}:F{$i}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $fillColor],
                    ],
                ]);
            }
        }

        // Стиль для заголовков отделов
        if ($constructorStartRow) {
            $sheet->getStyle("A{$constructorStartRow}:F{$constructorStartRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => '1B4F72'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D6EAF8'],
                ],
            ]);
        }

        if ($designerStartRow) {
            $sheet->getStyle("A{$designerStartRow}:F{$designerStartRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => '6C3483'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8DAEF'],
                ],
            ]);
        }

        // Жирный шрифт для баллов и процентов
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("D2:D{$lastRow}")->getFont()->setBold(true);
        $sheet->getStyle("F2:F{$lastRow}")->getFont()->setBold(true);

        // Цвет для процентов
        $sheet->getStyle("F2:F{$lastRow}")->getFont()->getColor()->setARGB('FFD35400');

        // Границы таблицы
        $sheet->getStyle('A1:F' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'B0C4DE'],
                ],
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '1B4F72'],
                ],
            ],
        ]);

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,  // Сотрудник
            'B' => 30,  // Должность
            'C' => 18,  // Статус
            'D' => 15,  // Баллы
            'E' => 18,  // Кол-во заявок
            'F' => 15,  // % от отдела
        ];
    }

    public function title(): string
    {
        return 'Сводка_' . date('Y_m', strtotime($this->date));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Итоги по конструкторам
                if ($this->constructorSheets->isNotEmpty()) {
                    $constructorTotalRow = $lastRow + 1;
                    $sheet->setCellValue("A{$constructorTotalRow}", "ИТОГО ПО ОТДЕЛУ КОНСТРУКТОРОВ");
                    $sheet->setCellValue("D{$constructorTotalRow}", $this->formatPoints($this->constructorTotalPoints));
                    $sheet->setCellValue("E{$constructorTotalRow}", $this->constructorTotalRequests);

                    $sheet->getStyle("A{$constructorTotalRow}:F{$constructorTotalRow}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '1B4F72'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                        ],
                    ]);

                    $sheet->mergeCells("A{$constructorTotalRow}:C{$constructorTotalRow}");
                    $lastRow = $constructorTotalRow;
                }

                // Итоги по дизайнерам
                if ($this->designerSheets->isNotEmpty()) {
                    $designerTotalRow = $lastRow + 2;
                    $sheet->setCellValue("A{$designerTotalRow}", "ИТОГО ПО ОТДЕЛУ ДИЗАЙНЕРОВ");
                    $sheet->setCellValue("D{$designerTotalRow}", $this->formatPoints($this->designerTotalPoints));
                    $sheet->setCellValue("E{$designerTotalRow}", $this->designerTotalRequests);

                    $sheet->getStyle("A{$designerTotalRow}:F{$designerTotalRow}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '6C3483'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                        ],
                    ]);

                    $sheet->mergeCells("A{$designerTotalRow}:C{$designerTotalRow}");
                    $lastRow = $designerTotalRow;
                }

                // Общий итог
                $totalRow = $lastRow + 2;
                $totalPoints = $this->constructorTotalPoints + $this->designerTotalPoints;
                $totalRequests = $this->constructorTotalRequests + $this->designerTotalRequests;

                $sheet->setCellValue("A{$totalRow}", "ОБЩИЙ ИТОГ ПО ВСЕМ ОТДЕЛАМ");
                $sheet->setCellValue("D{$totalRow}", $this->formatPoints($totalPoints));
                $sheet->setCellValue("E{$totalRow}", $totalRequests);

                $sheet->getStyle("A{$totalRow}:F{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E4053'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                    ],
                ]);

                $sheet->mergeCells("A{$totalRow}:C{$totalRow}");

                // Информационная строка
                $infoRow = $totalRow + 1;
                $sheet->mergeCells("A{$infoRow}:F{$infoRow}");
                $sheet->setCellValue("A{$infoRow}", "Отчёт сгенерирован: " . now()->format('d.m.Y H:i:s'));
                $sheet->getStyle("A{$infoRow}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 9,
                        'color' => ['rgb' => '7F8C8D'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);
            },
        ];
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

    private function formatPoints($value): string
    {
        if ($value === null || $value === '') return '0';
        $floatValue = (float) $value;
        if ($floatValue == floor($floatValue)) {
            return (string) $floatValue;
        }
        return rtrim(rtrim(number_format($floatValue, 2, '.', ''), '0'), '.');
    }
}
