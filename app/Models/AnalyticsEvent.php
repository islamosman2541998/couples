<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public const LABELS = ['page_view' => 'مشاهدة صفحة', 'game_preview' => 'معاينة لعبة', 'game_play' => 'فتح اللعب', 'checkout_view' => 'صفحة الاشتراك', 'register' => 'إنشاء حساب', 'login' => 'تسجيل دخول', 'receipt_uploaded' => 'رفع إيصال', 'subscription_approved' => 'تفعيل اشتراك', 'subscribe_click' => 'ضغط اشتراك', 'whatsapp_click' => 'ضغط واتساب', 'mood_select' => 'اختيار مود'];

    public function getLabelAttribute(): string
    {
        return self::LABELS[$this->name] ?? $this->name;
    }
}
