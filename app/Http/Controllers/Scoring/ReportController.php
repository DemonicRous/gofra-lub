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

        // Конструкторы
        $constructorSheets = ScoringSheet::whereHas('user', function($query) {
            $query->where('scoring_department', 'constructor');
        })
            ->whereYear('period_date', $date->year)
            ->whereMonth('period_date', $date->month)
            ->with(['user', 'user.position'])
            ->withCount('requests')  // <-- добавляем количество заявок
            ->get();

        // Дизайнеры
        $designerSheets = ScoringSheet::whereHas('user', function($query) {
            $query->where('scoring_department', 'designer');
        })
            ->whereYear('period_date', $date->year)
            ->whereMonth('period_date', $date->month)
            ->with(['user', 'user.position'])
            ->withCount('requests')  // <-- добавляем количество заявок
            ->get();

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $months[] = [
                'value' => $monthDate->format('Y-m'),
                'label' => $monthDate->translatedFormat('F Y'),
            ];
        }

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
            ->withCount('requests')
            ->get();

        $designerSheets = ScoringSheet::whereHas('user', function($query) {
            $query->where('scoring_department', 'designer');
        })
            ->whereYear('period_date', $date->year)
            ->whereMonth('period_date', $date->month)
            ->with('user')
            ->withCount('requests')
            ->get();

        return Excel::download(new ScoringSummaryExport($constructorSheets, $designerSheets, $date), 'scoring_summary_' . $date->format('Y_m') . '.xlsx');
    }
}
