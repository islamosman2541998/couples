<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SnakeCell;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SnakeCellController extends Controller
{
    public function index()
    {
        $cells = SnakeCell::orderBy('number')->paginate(20);

        return view('admin.snake-cells.index', compact('cells'));
    }

    public function edit(SnakeCell $snakeCell)
    {
        return view('admin.snake-cells.edit', ['cell' => $snakeCell]);
    }

    public function update(Request $request, SnakeCell $snakeCell)
    {
        $snakeCell->update($request->validate([
            'title' => 'required|string|max:100',
            'content' => 'required|string|max:1500',
            'mood' => ['required', Rule::in(array_keys(SnakeCell::MOODS))],
            'is_active' => 'required|boolean',
        ]));

        return redirect()->route('admin.snake-cells.index', ['page' => (int) ceil($snakeCell->number / 20)])
            ->with('success', 'تم تحديث خانة السلم والتعبان');
    }
}
