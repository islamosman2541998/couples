<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Setting;
use App\Support\HomeContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HomeContentController extends Controller
{
    private function linkRules(): array
    {
        return ['nullable', 'string', 'max:2000', function ($attribute, $value, $fail) {
            if (! preg_match('~^(https?://[^\s]+|/(?!/)[^\s]*|#[^\s]*)$~i', $value)) {
                $fail('استخدم رابط https أو مسار يبدأ بـ / أو #.');
            }
        }];
    }

    public function index()
    {
        return view('admin.home-content.index', [
            'settings' => HomeContent::settings(), 'slides' => HomeContent::items('slides'),
            'reviews' => HomeContent::items('reviews'), 'games' => Game::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'delay' => 'required|integer|between:2000,20000',
            'reviews_title' => 'required|string|max:150', 'countdown_title' => 'required|string|max:150',
            'countdown_text' => 'nullable|string|max:500', 'countdown_mode' => 'required|in:visitor,fixed',
            'hours' => 'required|integer|between:1,720', 'ends_at' => 'nullable|required_if:countdown_mode,fixed|date',
            'offer_label' => 'required|string|max:80', 'offer_url' => $this->linkRules(),
            'duration' => 'required|integer|between:3,10', 'interval' => 'required|integer|between:2,120',
            'position' => 'required|in:left,right', 'names' => 'required|string|max:10000',
            'game_ids' => 'nullable|array', 'game_ids.*' => ['integer', Rule::exists('games', 'id')],
            'slider_enabled' => 'sometimes|boolean', 'autoplay' => 'sometimes|boolean',
            'arrows' => 'sometimes|boolean', 'dots' => 'sometimes|boolean', 'reviews_enabled' => 'sometimes|boolean',
            'countdown_enabled' => 'sometimes|boolean', 'notifications_enabled' => 'sometimes|boolean',
            'restart_countdown' => 'sometimes|boolean',
        ]);
        foreach (['slider_enabled', 'autoplay', 'arrows', 'dots', 'reviews_enabled', 'countdown_enabled', 'notifications_enabled'] as $key) {
            $data[$key] = $request->boolean($key);
        }
        $data['names'] = array_values(array_unique(array_filter(array_map('trim', preg_split('/[\r\n،,]+/u', $data['names'])))));
        foreach ($data['names'] as $name) {
            abort_if(mb_strlen($name) > 60, 422, 'الاسم يجب ألا يزيد عن 60 حرفاً.');
        }
        $data['game_ids'] = array_map('intval', $data['game_ids'] ?? []);
        $old = HomeContent::settings();
        $data['countdown_version'] = $request->boolean('restart_countdown') || (int) $old['hours'] !== (int) $data['hours']
            ? (string) Str::uuid() : $old['countdown_version'];
        unset($data['restart_countdown']);
        Setting::set('home_settings', json_encode($data, JSON_UNESCAPED_UNICODE), 'homepage');
        return back()->with('success', 'تم حفظ إعدادات الصفحة الرئيسية');
    }

    public function saveItem(Request $request, string $type, ?string $id = null)
    {
        abort_unless(in_array($type, ['slides', 'reviews']), 404);
        $items = HomeContent::items($type);
        $index = $id === null ? null : collect($items)->search(fn ($item) => $item['id'] === $id);
        abort_if($id !== null && $index === false, 404);
        $old = $index === null ? [] : $items[$index];
        $data = $request->validate([
            'title' => 'required|string|max:150', 'url' => $this->linkRules(),
            'sort_order' => 'required|integer|between:0,9999', 'enabled' => 'sometimes|boolean',
            'image' => [empty($old['image']) && $type === 'reviews' ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'mobile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_mobile_image' => 'sometimes|boolean',
        ]);
        $item = array_merge($old, ['id' => $id ?? (string) Str::uuid(), 'title' => $data['title'],
            'url' => $data['url'] ?? null, 'sort_order' => (int) $data['sort_order'], 'enabled' => $request->boolean('enabled')]);
        $replaced = [];
        foreach (['image', 'mobile_image'] as $field) {
            $item[$field] = $old[$field] ?? null;
            if ($request->hasFile($field)) {
                $item[$field] = Storage::disk('public')->url($request->file($field)->store('homepage', 'public'));
                $replaced[] = $old[$field] ?? null;
            }
        }
        if ($request->boolean('remove_mobile_image') && ! $request->hasFile('mobile_image')) {
            $replaced[] = $item['mobile_image'];
            $item['mobile_image'] = null;
        }
        if ($index === null) { $items[] = $item; } else { $items[$index] = $item; }
        Setting::set('home_'.$type, json_encode(array_values($items), JSON_UNESCAPED_UNICODE), 'homepage');
        foreach ($replaced as $path) { $this->deleteImage($path); }
        return back()->with('success', 'تم حفظ المحتوى بنجاح');
    }

    public function destroy(string $type, string $id)
    {
        abort_unless(in_array($type, ['slides', 'reviews']), 404);
        $items = HomeContent::items($type);
        $item = collect($items)->firstWhere('id', $id);
        abort_unless($item, 404);
        Setting::set('home_'.$type, json_encode(array_values(array_filter($items, fn ($row) => $row['id'] !== $id)), JSON_UNESCAPED_UNICODE), 'homepage');
        $this->deleteImage($item['image'] ?? null);
        $this->deleteImage($item['mobile_image'] ?? null);
        return back()->with('success', 'تم حذف المحتوى');
    }

    private function deleteImage(?string $url): void
    {
        $prefix = Storage::disk('public')->url('homepage/');
        if ($url && str_starts_with($url, $prefix)) {
            Storage::disk('public')->delete('homepage/'.basename($url));
        }
    }
}
