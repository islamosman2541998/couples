<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChallengeCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChallengeCardController extends Controller
{
    public function index()
    {
        $cards      = ChallengeCard::orderBy('sort_order')->orderBy('id')->paginate(20);
        $categories = ChallengeCard::$categories;
        return view('admin.challenge-cards.index', compact('cards', 'categories'));
    }

    public function create()
    {
        $categories = ChallengeCard::$categories;
        return view('admin.challenge-cards.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'image'       => 'nullable|image|max:4096',
            'timer'       => 'nullable|integer|min:0|max:300',
            'category'    => 'required|in:general,romantic,bold,fun',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('challenges', 'public');
        }

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $request->input('sort_order', 0);
        $data['timer']      = $request->input('timer', 0);

        ChallengeCard::create($data);

        return redirect()->route('admin.challenge-cards.index')
            ->with('success', 'تم إضافة الكارت بنجاح');
    }

    public function edit(ChallengeCard $challengeCard)
    {
        $categories = ChallengeCard::$categories;
        return view('admin.challenge-cards.edit', compact('challengeCard', 'categories'));
    }

    public function update(Request $request, ChallengeCard $challengeCard)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'image'       => 'nullable|image|max:4096',
            'timer'       => 'nullable|integer|min:0|max:300',
            'category'    => 'required|in:general,romantic,bold,fun',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($challengeCard->image) {
                Storage::disk('public')->delete($challengeCard->image);
            }
            $data['image'] = $request->file('image')->store('challenges', 'public');
        }

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $request->input('sort_order', 0);
        $data['timer']      = $request->input('timer', 0);

        $challengeCard->update($data);

        return redirect()->route('admin.challenge-cards.index')
            ->with('success', 'تم تحديث الكارت بنجاح');
    }

    public function destroy(ChallengeCard $challengeCard)
    {
        if ($challengeCard->image) {
            Storage::disk('public')->delete($challengeCard->image);
        }
        $challengeCard->delete();

        return redirect()->route('admin.challenge-cards.index')
            ->with('success', 'تم حذف الكارت بنجاح');
    }
}
