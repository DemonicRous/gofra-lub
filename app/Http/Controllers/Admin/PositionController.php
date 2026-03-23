<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $positions = Position::with(['department'])
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($request->department_id, function($query, $departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->orderBy('name')
            ->paginate(15);

        $departments = Department::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Admin/Positions/Index', [
            'positions' => $positions,
            'departments' => $departments,
            'filters' => $request->only(['search', 'department_id'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions',
            'code' => 'nullable|string|max:50|unique:positions',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'level' => 'required|in:junior,middle,senior,lead,head',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gt:salary_min',
        ]);

        Position::create($validated);

        return redirect()->back()->with('success', 'Должность успешно создана.');
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions,name,' . $position->id,
            'code' => 'nullable|string|max:50|unique:positions,code,' . $position->id,
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'level' => 'required|in:junior,middle,senior,lead,head',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gt:salary_min',
            'is_active' => 'boolean',
        ]);

        $position->update($validated);

        return redirect()->back()->with('success', 'Должность успешно обновлена.');
    }

    public function destroy(Position $position)
    {
        if ($position->users()->exists()) {
            return redirect()->back()->with('error', 'Нельзя удалить должность, на которой есть сотрудники.');
        }

        $position->delete();

        return redirect()->back()->with('success', 'Должность успешно удалена.');
    }

    public function getByDepartment($departmentId)
    {
        $positions = Position::where('department_id', $departmentId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'level']);

        return response()->json($positions);
    }
}
