<?php
// app/Http/Controllers/Scoring/ReportController.php

namespace App\Http\Controllers\Scoring;

use App\Http\Controllers\Controller;
use App\Services\Scoring\SheetService;
use App\Models\ScoringSheet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $sheetService;

    public function __construct(SheetService $sheetService)
    {
        $this->sheetService = $sheetService;
    }

    /**
     * Сводка по подотделам
     */
    public function summary(Request $request)
    {
        $date = $request->get('date')
            ? Carbon::parse($request->get('date'))
            : Carbon::now();

        $summary = $this->sheetService->getSummary($date);

        return Inertia::render('Scoring/Summary', [
            'summary' => $summary,
            'currentDate' => $date->format('Y-m'),
            'months' => $this->getAvailableMonths(),
        ]);
    }

    /**
     * Экспорт личной ведомости в Excel
     */
    public function exportSheet($sheetId, Request $request)
    {
        $sheet = ScoringSheet::with(['user', 'entries.category', 'entries.variants'])
            ->findOrFail($sheetId);

        // Проверяем доступ
        if (!$this->canViewSheet($sheet, $request->user())) {
            abort(403, 'У вас нет доступа к этой ведомости');
        }

        // TODO: Создать класс экспорта
        // return Excel::download(
        //     new SheetExport($sheet),
        //     "vedomost_{$sheet->user->short_name}_{$sheet->period_date->format('Y_m')}.xlsx"
        // );

        return redirect()->back()->with('info', 'Экспорт в разработке');
    }

    /**
     * Экспорт сводки в Excel
     */
    public function exportSummary(Request $request)
    {
        $date = $request->get('date')
            ? Carbon::parse($request->get('date'))
            : Carbon::now();

        $summary = $this->sheetService->getSummary($date);

        // TODO: Создать класс экспорта
        // return Excel::download(
        //     new SummaryExport($summary, $date),
        //     "svodka_{$date->format('Y_m')}.xlsx"
        // );

        return redirect()->back()->with('info', 'Экспорт в разработке');
    }

    /**
     * Получить список доступных месяцев
     */
    private function getAvailableMonths(): array
    {
        $months = [];
        $start = Carbon::now()->subMonths(6)->startOfMonth();
        $end = Carbon::now();

        for ($date = $start; $date <= $end; $date->addMonth()) {
            $months[] = [
                'value' => $date->format('Y-m'),
                'label' => $date->translatedFormat('F Y'),
            ];
        }

        return $months;
    }

    /**
     * Проверка доступа к ведомости
     */
    private function canViewSheet(ScoringSheet $sheet, $user): bool
    {
        if ($sheet->user_id === $user->id) {
            return true;
        }

        if ($user->hasRole('manager') || $user->hasRole('admin')) {
            return $sheet->user->scoring_department === $user->scoring_department;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }
}
