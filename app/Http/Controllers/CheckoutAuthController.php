<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CheckoutAuthController extends Controller
{
    public function register(Request $request)
    {
        if ($request->user()) {
            return $this->authenticated($request);
        }
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9٠-٩][0-9٠-٩\s()-]{6,19}$/u'],
            'password' => ['required', Password::defaults()],
        ], [
            'email.unique' => 'البريد الإلكتروني مسجل بالفعل. اختر «لديك حساب» لتسجيل الدخول.',
            'phone.regex' => 'أدخل رقم موبايل صحيحاً.',
        ]);
        $user = User::create($data);
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        return $this->authenticated($request);
    }

    public function login(LoginRequest $request)
    {
        if ($request->user()) {
            return $this->authenticated($request);
        }
        $request->authenticate();
        if (! $request->user()->is_active) {
            Auth::logout();
            $request->session()->migrate(true);
            throw ValidationException::withMessages(['email' => 'هذا الحساب موقوف. تواصل مع الدعم.']);
        }
        $request->session()->regenerate();
        return $this->authenticated($request);
    }

    private function authenticated(Request $request)
    {
        return response()->json([
            'user' => $request->user()->only(['name', 'email', 'phone']),
            'csrf_token' => csrf_token(),
        ]);
    }
}
