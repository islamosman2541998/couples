<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $groups = ['general', 'contact', 'social', 'pages'];

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            if (in_array($key, ['site_logo'])) continue;

            $group = $this->resolveGroup($key);
            Setting::set($key, $value, $group);
        }

        return back()->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $old = Setting::get('site_logo');
        if ($old) Storage::disk('public')->delete($old);

        $path = $request->file('logo')->store('settings', 'public');
        Setting::set('site_logo', $path, 'general');

        return back()->with('success', 'تم رفع الشعار بنجاح');
    }

    private function resolveGroup(string $key): string
    {
        if (str_starts_with($key, 'social_')) return 'social';
        if (str_starts_with($key, 'contact_') || str_starts_with($key, 'bank_')) return 'contact';
        if (in_array($key, ['about_text', 'privacy_text'])) return 'pages';
        return 'general';
    }
}
