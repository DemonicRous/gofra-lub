<?php
// app/Http/Controllers/Admin/ManagerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManagerController extends Controller
{
    public function index(Request $request)
    {
        $managers = Manager::when($request->search, function($query, $search) {
            $query->where('full_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%");
        })
            ->orderBy('last_name')
            ->paginate(15);

        return Inertia::render('Admin/Managers/Index', [
            'managers' => $managers,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'patronymic' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Manager::create($validated);

        return redirect()->back()->with('success', 'Менеджер успешно добавлен');
    }

    public function update(Manager $manager, Request $request)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'patronymic' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $manager->update($validated);

        return redirect()->back()->with('success', 'Менеджер успешно обновлен');
    }

    public function destroy(Manager $manager)
    {
        $manager->delete();

        return redirect()->back()->with('success', 'Менеджер успешно удален');
    }
}
