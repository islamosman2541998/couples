<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('is_admin', false);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        $users = $query->withCount('subscriptions')->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['subscriptions.game']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);
        $user->update($validated);
        return redirect()->route('admin.users.index')->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function destroy(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'لا يمكن حذف مدير النظام');
        }
        $user->delete();
        return back()->with('success', 'تم حذف المستخدم بنجاح');
    }

    public function toggle(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'لا يمكن تعطيل مدير النظام');
        }
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'تم تحديث حالة المستخدم');
    }
}
