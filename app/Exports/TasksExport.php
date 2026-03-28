<?php
// app/Exports/TasksExport.php

namespace App\Exports;

use App\Models\Task;
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

class TasksExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $tasks;
    protected $filters;

    public function __construct($tasks, $filters = [])
    {
        $this->tasks = $tasks;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->tasks;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Название',
            'Описание',
            'Тип',
            'Статус',
            'Приоритет',
            'Видимость',
            'Создатель',
            'Ответственный',
            'Срок выполнения',
            'Прогресс',
            'Дата создания',
            'Дата обновления',
            'Дата завершения',
        ];
    }

    public function map($task): array
    {
        return [
            $task->id,
            $task->title,
            $task->description ?? '—',
            $this->getTypeName($task->type),
            $this->getStatusName($task->status),
            $this->getPriorityName($task->priority),
            $this->getVisibilityName($task->visibility),
            $task->creator ? $task->creator->full_name : '—',
            $task->assignee ? $task->assignee->full_name : 'Не назначен',
            $task->due_date ? date('d.m.Y H:i', strtotime($task->due_date)) : '—',
            $task->progress . '%',
            $task->created_at ? date('d.m.Y H:i', strtotime($task->created_at)) : '—',
            $task->updated_at ? date('d.m.Y H:i', strtotime($task->updated_at)) : '—',
            $task->completed_at ? date('d.m.Y H:i', strtotime($task->completed_at)) : '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Стиль для заголовков
        $sheet->getStyle('A1:N1')->applyFromArray([
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

        // Автоматическая ширина строк
        $sheet->getStyle('A2:N' . ($this->tasks->count() + 1))->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Цветовые индикаторы для статусов
        $row = 2;
        foreach ($this->tasks as $task) {
            $statusColor = $this->getStatusColor($task->status);
            $priorityColor = $this->getPriorityColor($task->priority);

            // Цвет для статуса
            $sheet->getStyle("E{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $statusColor],
                ],
                'font' => [
                    'color' => ['rgb' => 'FFFFFF'],
                    'bold' => true,
                ],
            ]);

            // Цвет для приоритета
            $sheet->getStyle("F{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $priorityColor],
                ],
                'font' => [
                    'color' => ['rgb' => 'FFFFFF'],
                    'bold' => true,
                ],
            ]);

            $row++;
        }

        // Границы для всей таблицы
        $sheet->getStyle('A1:N' . ($this->tasks->count() + 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 30,  // Название
            'C' => 40,  // Описание
            'D' => 12,  // Тип
            'E' => 14,  // Статус
            'F' => 12,  // Приоритет
            'G' => 12,  // Видимость
            'H' => 20,  // Создатель
            'I' => 20,  // Ответственный
            'J' => 18,  // Срок выполнения
            'K' => 10,  // Прогресс
            'L' => 18,  // Дата создания
            'M' => 18,  // Дата обновления
            'N' => 18,  // Дата завершения
        ];
    }

    public function title(): string
    {
        return 'Задачи_' . date('Y-m-d');
    }

    private function getTypeName($type): string
    {
        $map = [
            'task' => 'Задача',
            'urgent' => 'Срочная',
            'reminder' => 'Напоминание',
            'idea' => 'Идея',
            'bug' => 'Ошибка',
            'feature' => 'Новая функция',
        ];
        return $map[$type] ?? $type;
    }

    private function getStatusName($status): string
    {
        $map = [
            'backlog' => 'Бэклог',
            'todo' => 'К выполнению',
            'in_progress' => 'В работе',
            'in_review' => 'На проверке',
            'completed' => 'Выполнена',
            'cancelled' => 'Отменена',
        ];
        return $map[$status] ?? $status;
    }

    private function getPriorityName($priority): string
    {
        $map = [
            'low' => 'Низкий',
            'medium' => 'Средний',
            'high' => 'Высокий',
            'urgent' => 'Срочный',
            'critical' => 'Критический',
        ];
        return $map[$priority] ?? $priority;
    }

    private function getVisibilityName($visibility): string
    {
        $map = [
            'personal' => 'Личная',
            'department' => 'Отдел',
            'company' => 'Компания',
            'project' => 'Проект',
        ];
        return $map[$visibility] ?? $visibility;
    }

    private function getStatusColor($status): string
    {
        $map = [
            'backlog' => '6B7280',
            'todo' => '3B82F6',
            'in_progress' => 'F59E0B',
            'in_review' => '8B5CF6',
            'completed' => '10B981',
            'cancelled' => 'EF4444',
        ];
        return $map[$status] ?? '9CA3AF';
    }

    private function getPriorityColor($priority): string
    {
        $map = [
            'low' => '6B7280',
            'medium' => '3B82F6',
            'high' => 'F59E0B',
            'urgent' => 'EF4444',
            'critical' => '8B5CF6',
        ];
        return $map[$priority] ?? '9CA3AF';
    }
}
