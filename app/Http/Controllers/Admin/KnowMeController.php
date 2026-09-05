<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowMeQuestion;
use Illuminate\Http\Request;

class KnowMeController extends Controller
{
    public function index()
    {
        $questions  = KnowMeQuestion::orderBy('category')->orderBy('sort_order')->paginate(25);
        $categories = KnowMeQuestion::$categories;
        return view('admin.know-me.index', compact('questions', 'categories'));
    }

    public function create()
    {
        $categories = KnowMeQuestion::$categories;
        return view('admin.know-me.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->normalizeChoices($request);
        $data = $request->validate([
            'question'    => 'required|string|max:400',
            'category'    => 'required|in:daily,future,family,personality,values,fun',
            'answer_type' => 'required|in:open,choice',
            'choices'     => 'exclude_unless:answer_type,choice|required|array|min:2',
            'choices.*'   => 'nullable|string|max:100',
            'hint'        => 'nullable|string|max:300',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        // Filter empty choices
        if ($data['answer_type'] === 'choice' && !empty($data['choices'])) {
            $data['choices'] = array_values(array_filter($data['choices'], fn ($value) => $value !== null && $value !== ''));
        } else {
            $data['choices'] = null;
        }

        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = ($request->input('sort_order') ?? 0);

        KnowMeQuestion::create($data);

        return redirect()->route('admin.know-me.index')
            ->with('success', 'تم إضافة السؤال بنجاح');
    }

    public function edit(KnowMeQuestion $knowMe)
    {
        $categories = KnowMeQuestion::$categories;
        return view('admin.know-me.edit', compact('knowMe', 'categories'));
    }

    public function update(Request $request, KnowMeQuestion $knowMe)
    {
        $this->normalizeChoices($request);
        $data = $request->validate([
            'question'    => 'required|string|max:400',
            'category'    => 'required|in:daily,future,family,personality,values,fun',
            'answer_type' => 'required|in:open,choice',
            'choices'     => 'exclude_unless:answer_type,choice|required|array|min:2',
            'choices.*'   => 'nullable|string|max:100',
            'hint'        => 'nullable|string|max:300',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        if ($data['answer_type'] === 'choice' && !empty($data['choices'])) {
            $data['choices'] = array_values(array_filter($data['choices'], fn ($value) => $value !== null && $value !== ''));
        } else {
            $data['choices'] = null;
        }

        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = ($request->input('sort_order') ?? 0);

        $knowMe->update($data);

        return redirect()->route('admin.know-me.index')
            ->with('success', 'تم تحديث السؤال بنجاح');
    }

    public function destroy(KnowMeQuestion $knowMe)
    {
        $knowMe->delete();
        return redirect()->route('admin.know-me.index')
            ->with('success', 'تم حذف السؤال بنجاح');
    }

    private function normalizeChoices(Request $request): void
    {
        if (is_array($request->input('choices'))) {
            $request->merge(['choices' => array_values(array_filter($request->input('choices'), fn ($value) => $value !== null && $value !== ''))]);
        }
    }
}
