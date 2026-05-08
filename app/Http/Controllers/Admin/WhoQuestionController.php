<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhoQuestion;
use Illuminate\Http\Request;

class WhoQuestionController extends Controller
{
    public function index()
    {
        $questions  = WhoQuestion::orderBy('sort_order')->orderBy('id')->paginate(20);
        $categories = WhoQuestion::$categories;
        return view('admin.who-questions.index', compact('questions', 'categories'));
    }

    public function create()
    {
        $categories = WhoQuestion::$categories;
        return view('admin.who-questions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question'   => 'required|string|max:300',
            'category'   => 'required|in:funny,romantic,bold,thinking',
            'challenge'  => 'nullable|string|max:300',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $request->input('sort_order', 0);

        WhoQuestion::create($data);

        return redirect()->route('admin.who-questions.index')
            ->with('success', 'تم إضافة السؤال بنجاح');
    }

    public function edit(WhoQuestion $whoQuestion)
    {
        $categories = WhoQuestion::$categories;
        return view('admin.who-questions.edit', compact('whoQuestion', 'categories'));
    }

    public function update(Request $request, WhoQuestion $whoQuestion)
    {
        $data = $request->validate([
            'question'   => 'required|string|max:300',
            'category'   => 'required|in:funny,romantic,bold,thinking',
            'challenge'  => 'nullable|string|max:300',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $request->input('sort_order', 0);

        $whoQuestion->update($data);

        return redirect()->route('admin.who-questions.index')
            ->with('success', 'تم تحديث السؤال بنجاح');
    }

    public function destroy(WhoQuestion $whoQuestion)
    {
        $whoQuestion->delete();
        return redirect()->route('admin.who-questions.index')
            ->with('success', 'تم حذف السؤال بنجاح');
    }
}
