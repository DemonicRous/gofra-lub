<?php

namespace App\Exports;

use App\Models\ScoringSheet;
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

class ScoringSheetExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $sheet;
    protected $requests;
    protected $rowData = [];
    protected $currentRow = 2;

    public function __construct(ScoringSheet $sheet)
    {
        $this->sheet = $sheet;
        $this->sheet->load(['user', 'requests.variants.entries.category', 'requests.variants.entries.category.parent']);
        $this->requests = $this->sheet->requests;
        $this->prepareRowData();
    }

    protected function prepareRowData()
    {
        $this->rowData = [];
        $requestNumber = 1;

        foreach ($this->requests as $request) {
            $firstVariant = true;
            $variantCount = $request->variants->count();

            foreach ($request->variants as $variant) {
                // Собираем все работы в одну строку с красивым форматированием
                $worksList = [];
                foreach ($variant->entries as $index => $entry) {
                    $workName = ($entry->category->parent->name ?? '') . ' → ' . ($entry->category->name ?? '');
                    $points = number_format($entry->points, 2, '.', '');
                    // Убираем .00 если это целое число, но оставляем дробные
                    $pointsFormatted = $this->formatPoints($entry->points);
                    $worksList[] = "▸ " . $workName . " (+" . $pointsFormatted . " баллов)";
                }
                $allWorks = implode("\n", $worksList) ?: '—';

                $this->rowData[] = [
                    'request_number' => $requestNumber,
                    'request_number_display' => $firstVariant ? $requestNumber : '',
                    'request_number_span' => $firstVariant ? $variantCount : 0,
                    'request_id' => $request->request_number,
                    'counterparty' => $request->counterparty ?? '—',
                    'manager_name' => $request->manager_name ?? '—',
                    'variant_name' => $variant->name ?? '—',
                    'works' => $allWorks,
                    'variant_total' => $variant->total_points,
                    'request_total' => $firstVariant ? $request->total_points : null,
                    'request_total_span' => $firstVariant ? $variantCount : 0,
                    'is_first_variant' => $firstVariant,
                ];

                $firstVariant = false;
            }
            $requestNumber++;
        }
    }

    /**
     * Форматирование баллов без потери точности
     * - Если число целое (например 5.00) -> "5"
     * - Если дробное (например 3.50) -> "3.5"
     * - Всегда сохраняем точность
     */
    protected function formatPoints($points)
    {
        if ($points === null || $points === '') return '0';

        $floatValue = (float) $points;

        // Проверяем, является ли число целым
        if ($floatValue == floor($floatValue)) {
            return (string) $floatValue;
        }

        // Убираем лишние нули в конце (1.50 -> 1.5)
        $formatted = rtrim(rtrim(number_format($floatValue, 2, '.', ''), '0'), '.');
        return $formatted;
    }

    public function collection()
    {
        $rows = collect();
        $rowNumber = 1;

        foreach ($this->rowData as $data) {
            $rows->push((object)[
                'row_number' => $rowNumber++,
                'request_number_display' => $data['request_number_display'],
                'request_id' => $data['request_id'],
                'counterparty' => $data['counterparty'],
                'manager_name' => $data['manager_name'],
                'variant_name' => $data['variant_name'],
                'works' => $data['works'],
                'variant_total' => $data['variant_total'],
                'request_total' => $data['request_total'],
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            '№',
            '№ Заявки',
            'Контрагент',
            'Менеджер',
            'Вариант',
            'Выполненные работы',
            'Итого по варианту',
            'Итого по заявке',
        ];
    }

    public function map($row): array
    {
        return [
            $row->row_number,
            $row->request_id,
            $row->counterparty,
            $row->manager_name,
            $row->variant_name,
            $row->works,
            $this->formatPoints($row->variant_total),
            $row->request_total !== null ? $this->formatPoints($row->request_total) : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->collection()->count() + 1;

        // Заголовки - градиентный эффект через заливку
        $sheet->getStyle('A1:H1')->applyFromArray([
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

        $sheet->getRowDimension(1)->setRowHeight(35);

        // Стили для строк данных
        for ($i = 2; $i <= $lastRow; $i++) {
            // Чередование цветов строк
            $fillColor = ($i % 2 == 0) ? 'F0F8FF' : 'FFFFFF';

            $sheet->getStyle("A{$i}:H{$i}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $fillColor],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

            // Автовысота строки
            $sheet->getRowDimension($i)->setRowHeight(-1);
        }

        // Выравнивание по центру для всех колонок кроме "Выполненные работы"
        $centerColumns = ['A', 'B', 'C', 'D', 'E', 'G', 'H'];
        foreach ($centerColumns as $col) {
            $sheet->getStyle("{$col}2:{$col}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Выравнивание по левому краю для колонки "Выполненные работы"
        $sheet->getStyle("F2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Стиль для колонки с работами
        $sheet->getStyle("F2:F{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Segoe UI',
                'size' => 10,
            ],
        ]);

        // Стиль для итоговых колонок
        $sheet->getStyle("G2:H{$lastRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '1B4F72'],
                'size' => 11,
            ],
        ]);

        // Стиль для номера строки
        $sheet->getStyle("A2:A{$lastRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '1B4F72'],
            ],
        ]);

        // Границы таблицы
        $sheet->getStyle("A1:H{$lastRow}")->applyFromArray([
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
            'A' => 6,   // №
            'B' => 14,  // № Заявки
            'C' => 35,  // Контрагент
            'D' => 30,  // Менеджер
            'E' => 25,  // Вариант
            'F' => 60,  // Выполненные работы
            'G' => 25,  // Итого по варианту
            'H' => 25,  // Итого по заявке
        ];
    }

    public function title(): string
    {
        return 'Ведомость_' . $this->sheet->period_date->format('Y_m');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowIndex = 2;

                foreach ($this->rowData as $data) {
                    if ($data['is_first_variant'] && $data['request_number_span'] > 1) {
                        $span = $data['request_number_span'];

                        // Объединяем ячейки для № Заявки
                        $mergeRange = "B{$rowIndex}:B" . ($rowIndex + $span - 1);
                        $sheet->mergeCells($mergeRange);
                        $sheet->getStyle($mergeRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle($mergeRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        // Объединяем ячейки для Контрагента
                        $mergeRange = "C{$rowIndex}:C" . ($rowIndex + $span - 1);
                        $sheet->mergeCells($mergeRange);
                        $sheet->getStyle($mergeRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle($mergeRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        // Объединяем ячейки для Менеджера
                        $mergeRange = "D{$rowIndex}:D" . ($rowIndex + $span - 1);
                        $sheet->mergeCells($mergeRange);
                        $sheet->getStyle($mergeRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle($mergeRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        // Объединяем ячейки для Итого по заявке
                        $mergeRange = "H{$rowIndex}:H" . ($rowIndex + $span - 1);
                        $sheet->mergeCells($mergeRange);
                        $sheet->getStyle($mergeRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle($mergeRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        // Жирный шрифт для объединённых ячеек
                        $sheet->getStyle("B{$rowIndex}:B" . ($rowIndex + $span - 1))->getFont()->setBold(true);
                        $sheet->getStyle("C{$rowIndex}:C" . ($rowIndex + $span - 1))->getFont()->setBold(true);
                        $sheet->getStyle("D{$rowIndex}:D" . ($rowIndex + $span - 1))->getFont()->setBold(true);
                        $sheet->getStyle("H{$rowIndex}:H" . ($rowIndex + $span - 1))->getFont()->setBold(true);
                    }

                    $rowIndex++;
                }

                // Добавляем строку с общим итогом
                $lastRow = $this->collection()->count() + 1;
                $totalRow = $lastRow + 2;

                // Пустая строка для отступа
                $sheet->getRowDimension($lastRow + 1)->setRowHeight(5);

                $totalPoints = $this->sheet->total_points;

                // Объединяем ячейки для текста "ОБЩИЙ ИТОГ:"
                $sheet->mergeCells("A{$totalRow}:F{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", "ОБЩИЙ ИТОГ:");
                $sheet->getStyle("A{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF'],
                        'name' => 'Segoe UI',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1B4F72'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Объединяем ячейки для суммы (с точным форматированием)
                $sheet->mergeCells("G{$totalRow}:H{$totalRow}");
                $sheet->setCellValue("G{$totalRow}", $this->formatPoints($totalPoints) . ' баллов');
                $sheet->getStyle("G{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF'],
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

                $sheet->getRowDimension($totalRow)->setRowHeight(30);

                // Добавляем информационную строку с датой генерации
                $infoRow = $totalRow + 1;
                $sheet->mergeCells("A{$infoRow}:H{$infoRow}");
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
}
