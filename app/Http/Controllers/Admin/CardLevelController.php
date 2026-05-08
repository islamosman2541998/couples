<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CardLevel;
use Illuminate\Http\Request;

class CardLevelController extends Controller
{
    public function index()
    {
        $levels = CardLevel::withCount('cards')->orderBy('sort_order')->get();
        return view('admin.card-levels.index', compact('levels'));
    }

    public function create()
    {
        return view('admin.card-levels.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'slug'       => 'required|string|unique:card_levels,slug',
            'color'      => 'required|string',
            'sort_order' => 'integer',
        ]);
        CardLevel::create($validated);
        return redirect()->route('admin.card-levels.index')->with('success', 'تم إضافة المستوى بنجاح');
    }

    public function edit(CardLevel $cardLevel)
    {
        return view('admin.card-levels.edit', compact('cardLevel'));
    }

    public function update(Request $request, CardLevel $cardLevel)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'color'      => 'required|string',
            'sort_order' => 'integer',
        ]);
        $cardLevel->update($validated);
        return redirect()->route('admin.card-levels.index')->with('success', 'تم تحديث المستوى بنجاح');
    }

    public function destroy(CardLevel $cardLevel)
    {
        $cardLevel->delete();
        return back()->with('success', 'تم حذف المستوى بنجاح');
    }
}
