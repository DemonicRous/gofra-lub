<?php

namespace App\Http\Controllers\Scoring;

use App\Http\Controllers\Controller;
use App\Exports\ScoringSheetExport;
use App\Exports\ScoringSheetPdfExport;
use App\Exports\ScoringSummaryExport;
use App\Models\ScoringSheet;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Экспорт ведомости в Excel
     */
    public function exportSheet(ScoringSheet $sheet, Request $request)
    {
        // Проверка доступа
        if ($sheet->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            abort(403, 'У вас нет доступа к этой ведомости');
        }

        return Excel::download(new ScoringSheetExport($sheet), 'vedomost_' . $sheet->period_date->format('Y_m') . '.xlsx');
    }

    /**
     * Экспорт ведомости в PDF
     */
    public function exportSheetPdf(ScoringSheet $sheet, Request $request)
    {
        // Проверка доступа
        if ($sheet->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            abort(403, 'У вас нет доступа к этой ведомости');
        }

        $export = new ScoringSheetPdfExport($sheet);
        return $export->download();
    }

    /**
     * Сводка по отделам
     */
    public function summary(Request $request)
    {
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();

        // Получаем ведомости для конструкторов за выбранный месяц
        $constructorSheets = ScoringSheet::whereHas('user', function($query) {
            $query->where('scoring_department', 'constructor');
        })
            ->whereYear('period_date', $date->year)
            ->whereMonth('period_date', $date->month)
            ->with('user')
            ->get()
            ->map(function($sheet) {
                // Подсчитываем количество записей
                $sheet->entries_count = $sheet->requests->sum(function($request) {
                    return $request->variants->sum(function($variant) {
                        return $variant->entries->count();
                    });
                });
                return $sheet;
            });

        // Получаем ведомости для дизайнеров за выбранный месяц
        $designerSheets = ScoringSheet::whereHas('user', function($query) {
            $query->where('scoring_department', 'designer');
        })
            ->whereYear('period_date', $date->year)
            ->whereMonth('period_date', $date->month)
            ->with('user')
            ->get()
            ->map(function($sheet) {
                // Подсчитываем количество записей
                $sheet->entries_count = $sheet->requests->sum(function($request) {
                    return $request->variants->sum(function($variant) {
                        return $variant->entries->count();
                    });
                });
                return $sheet;
            });

        // Формируем список месяцев для фильтра (последние 6 месяцев)
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $months[] = [
                'value' => $monthDate->format('Y-m'),
                'label' => $monthDate->translatedFormat('F Y'),
            ];
        }

        // Передаём данные в компонент Summary
        return inertia('Scoring/Summary', [
            'constructorSheets' => $constructorSheets,
            'designerSheets' => $designerSheets,
            'currentDate' => $date->format('Y-m'),
            'months' => $months,
        ]);
    }

    /**
     * Экспорт сводки в Excel
     */
    public function exportSummary(Request $request)
    {
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();

        $constructorSheets = ScoringSheet::whereHas('user', function($query) {
            $query->where('scoring_department', 'constructor');
        })
            ->whereYear('period_date', $date->year)
            ->whereMonth('period_date', $date->month)
            ->with('user')
            ->get()
            ->map(function($sheet) {
                $sheet->entries_count = $sheet->requests->sum(function($request) {
                    return $request->variants->sum(function($variant) {
                        return $variant->entries->count();
                    });
                });
                return $sheet;
            });

        $designerSheets = ScoringSheet::whereHas('user', function($query) {
            $query->where('scoring_department', 'designer');
        })
            ->whereYear('period_date', $date->year)
            ->whereMonth('period_date', $date->month)
            ->with('user')
            ->get()
            ->map(function($sheet) {
                $sheet->entries_count = $sheet->requests->sum(function($request) {
                    return $request->variants->sum(function($variant) {
                        return $variant->entries->count();
                    });
                });
                return $sheet;
            });

        return Excel::download(new ScoringSummaryExport($constructorSheets, $designerSheets, $date), 'scoring_summary_' . $date->format('Y_m') . '.xlsx');
    }
}
