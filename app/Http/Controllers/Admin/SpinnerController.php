<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpinnerImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SpinnerController extends Controller
{
    public function index()
    {
        $images = SpinnerImage::orderBy('sort_order')->get();
        return view('admin.spinner.index', compact('images'));
    }

    public function create()
    {
        return view('admin.spinner.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'image'      => 'required|image|max:2048',
            'color'      => 'required|string',
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['image'] = $request->file('image')->store('spinner', 'public');

        SpinnerImage::create($validated);
        return redirect()->route('admin.spinner-images.index')->with('success', 'تم إضافة الصورة بنجاح');
    }

    public function edit(SpinnerImage $spinnerImage)
    {
        return view('admin.spinner.edit', compact('spinnerImage'));
    }

    public function update(Request $request, SpinnerImage $spinnerImage)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'image'      => 'nullable|image|max:2048',
            'color'      => 'required|string',
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        $oldImage = $spinnerImage->image;
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('spinner', 'public');
            abort_unless($validated['image'], 500, 'تعذر حفظ الصورة. حاول مرة أخرى.');
        }

        $spinnerImage->update($validated);
        if ($request->hasFile('image') && $oldImage && $oldImage !== 'spinner/placeholder.png') {
            Storage::disk('public')->delete($oldImage);
        }
        return redirect()->route('admin.spinner-images.index')->with('success', 'تم تحديث الصورة بنجاح');
    }

    public function destroy(SpinnerImage $spinnerImage)
    {
        Storage::disk('public')->delete($spinnerImage->image);
        $spinnerImage->delete();
        return back()->with('success', 'تم حذف الصورة بنجاح');
    }

    public function toggle(SpinnerImage $image)
    {
        $image->update(['is_active' => !$image->is_active]);
        return back()->with('success', 'تم تحديث حالة الصورة');
    }
}
