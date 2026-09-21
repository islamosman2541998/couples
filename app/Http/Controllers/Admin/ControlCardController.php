<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ControlCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ControlCardController extends Controller
{
    public function index()
    {
        $cards = ControlCard::orderBy('sort_order')->orderBy('id')->paginate(20);

        return view('admin.control-cards.index', compact('cards'));
    }

    public function create()
    {
        return view('admin.control-cards.form', ['card' => new ControlCard(['is_active' => true, 'sort_order' => 0])]);
    }

    public function store(Request $request)
    {
        ControlCard::create($this->validatedData($request));

        return redirect()->route('admin.control-cards.index')->with('success', 'تم إضافة كارت السيطرة');
    }

    public function edit(ControlCard $controlCard)
    {
        return view('admin.control-cards.form', ['card' => $controlCard]);
    }

    public function update(Request $request, ControlCard $controlCard)
    {
        $oldImage = $controlCard->image;
        $data = $this->validatedData($request);
        $controlCard->update($data);
        if ($oldImage && array_key_exists('image', $data) && $oldImage !== $data['image']) {
            Storage::disk('premium')->delete($oldImage);
        }

        return redirect()->route('admin.control-cards.index')->with('success', 'تم تحديث كارت السيطرة');
    }

    public function destroy(ControlCard $controlCard)
    {
        $image = $controlCard->image;
        $controlCard->delete();
        if ($image) {
            Storage::disk('premium')->delete($image);
        }

        return redirect()->route('admin.control-cards.index')->with('success', 'تم حذف كارت السيطرة');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:2000',
            'image' => 'nullable|image|max:4096',
            'remove_image' => 'nullable|boolean',
            'sort_order' => 'required|integer|min:0|max:1000000',
            'is_active' => 'required|boolean',
        ]);
        unset($data['remove_image'], $data['image']);
        if ($request->boolean('remove_image')) {
            $data['image'] = null;
        }
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('control-cards', 'premium');
        }

        return $data;
    }
}
