<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScratchCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScratchCardController extends Controller
{
    public function index()
    {
        $cards = ScratchCard::orderBy('sort_order')->orderBy('number')->paginate(20);
        return view('admin.scratch-cards.index', compact('cards'));
    }

    public function create()
    {
        $nextNumber = (ScratchCard::max('number') ?? 0) + 1;
        return view('admin.scratch-cards.create', compact('nextNumber'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number'     => 'required|integer|min:1|unique:scratch_cards,number',
            'content'    => 'required|string|max:500',
            'level'      => 'sometimes|integer|in:1,2,3',
            'image'      => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('scratch', 'public');
        }

        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = ($request->input('sort_order') ?? 0);

        ScratchCard::create($data);

        return redirect()->route('admin.scratch-cards.index')
            ->with('success', 'تم إضافة الكارت بنجاح');
    }

    public function edit(ScratchCard $scratchCard)
    {
        return view('admin.scratch-cards.edit', compact('scratchCard'));
    }

    public function update(Request $request, ScratchCard $scratchCard)
    {
        $data = $request->validate([
            'number'     => 'required|integer|min:1|unique:scratch_cards,number,' . $scratchCard->id,
            'content'    => 'required|string|max:500',
            'level'      => 'sometimes|integer|in:1,2,3',
            'image'      => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($scratchCard->image) {
                Storage::disk('public')->delete($scratchCard->image);
            }
            $data['image'] = $request->file('image')->store('scratch', 'public');
        }

        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = ($request->input('sort_order') ?? 0);

        $scratchCard->update($data);

        return redirect()->route('admin.scratch-cards.index')
            ->with('success', 'تم تحديث الكارت بنجاح');
    }

    public function destroy(ScratchCard $scratchCard)
    {
        if ($scratchCard->image) {
            Storage::disk('public')->delete($scratchCard->image);
        }
        $scratchCard->delete();

        return redirect()->route('admin.scratch-cards.index')
            ->with('success', 'تم حذف الكارت بنجاح');
    }
}
