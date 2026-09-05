<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GameManageController extends Controller
{
    public function index()
    {
        $games = Game::orderBy('sort_order')->get();

        return view('admin.games.index', compact('games'));
    }

    public function create()
    {
        return view('admin.games.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'how_to_play' => 'nullable|string|max:4000',
            'type' => 'required|in:card,spinner,scratch,who,challenge,know_me',
            'is_free' => 'boolean',
            'price' => 'required|numeric|min:0|max:999999.99',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['is_free'] = $request->boolean('is_free');
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('games', 'public');
        }

        Game::create($validated);

        return redirect()->route('admin.games.index')->with('success', 'تم إضافة اللعبة بنجاح');
    }

    public function edit(Game $game)
    {
        return view('admin.games.edit', compact('game'));
    }

    public function update(Request $request, Game $game)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'how_to_play' => 'nullable|string|max:4000',
            'type' => 'required|in:card,spinner,scratch,who,challenge,know_me',
            'is_free' => 'boolean',
            'price' => 'required|numeric|min:0|max:999999.99',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['is_free'] = $request->boolean('is_free');
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($game->image) {
                Storage::disk('public')->delete($game->image);
            }
            $validated['image'] = $request->file('image')->store('games', 'public');
        }

        $game->update($validated);

        return redirect()->route('admin.games.index')->with('success', 'تم تحديث اللعبة بنجاح');
    }

    public function destroy(Game $game)
    {
        if ($game->image) {
            Storage::disk('public')->delete($game->image);
        }
        $game->delete();

        return back()->with('success', 'تم حذف اللعبة بنجاح');
    }

    public function toggle(Game $game)
    {
        $game->update(['is_active' => ! $game->is_active]);

        return back()->with('success', 'تم تحديث حالة اللعبة');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'game';
        $slug = $base;
        $suffix = 2;
        while (Game::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
