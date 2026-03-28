<?php
// app/Exports/TasksPdfExport.php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;

class TasksPdfExport
{
    protected $tasks;
    protected $filters;
    protected $stats;

    public function __construct($tasks, $filters = [], $stats = [])
    {
        $this->tasks = $tasks;
        $this->filters = $filters;
        $this->stats = $stats;
    }

    public function download()
    {
        $data = [
            'tasks' => $this->tasks,
            'filters' => $this->filters,
            'stats' => $this->stats,
            'generated_at' => now()->format('d.m.Y H:i:s'),
        ];

        $pdf = Pdf::loadView('exports.tasks-pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('tasks_export_' . date('Y-m-d_His') . '.pdf');
    }
}
