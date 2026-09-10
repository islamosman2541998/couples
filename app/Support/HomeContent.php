<?php

namespace App\Support;

use App\Models\Setting;

class HomeContent
{
    public static function defaults(): array
    {
        return [
            'slider_enabled' => true, 'autoplay' => true, 'delay' => 5000,
            'arrows' => true, 'dots' => true,
            'reviews_enabled' => true, 'reviews_title' => 'حكايات حلوة من عملائنا',
            'countdown_enabled' => true, 'countdown_title' => 'وقت أقل… متعة أكتر!',
            'countdown_text' => 'اكتشف عروض الألعاب واختار لعبتك المفضلة قبل انتهاء الوقت.',
            'countdown_mode' => 'visitor', 'hours' => 12, 'ends_at' => null,
            'countdown_version' => '1', 'offer_label' => 'اكتشف الألعاب', 'offer_url' => '#games',
            'notifications_enabled' => true, 'duration' => 4, 'interval' => 10, 'position' => 'left',
            'names' => explode('،', 'إسلام،أحمد،محمد،محمود،مصطفى،علي،عمر،عمرو،يوسف،ياسين،آدم،مالك،سيف،زين،يحيى،حمزة،حسن،حسين،خالد،وليد،كريم،شريف،طارق،هشام،حسام،إبراهيم،إسماعيل،عبدالله،عبدالرحمن،عبدالعزيز،معاذ،أنس،باسم،باسل،رامي،رامز،مازن،مروان،زياد،إياد،أدهم،أمير،أكرم،أيمن،أشرف،عماد،فادي،هادي،نادر،نور،سارة،مريم،نورهان،نورا،ندى،هنا،هبة،منة،ملك،فريدة،ليلى،ليان،جنى،جنا،جودي،ريم،ريماس،روان،رنا،رحمة،بسمة،بسنت،آية،إسراء،أسماء،دعاء،دينا،داليا،ياسمين،يمنى،أميرة،أمل،إيمان،حنين،حبيبة،سلمى،سما،سمية،شهد،شيماء،ضحى،علا،غادة،فاطمة،فيروز،لينا،لميس،مها،مي،هاجر'),
            'game_ids' => [],
        ];
    }

    public static function settings(): array
    {
        return array_replace(self::defaults(), json_decode(Setting::get('home_settings', '{}'), true) ?: []);
    }

    public static function items(string $type): array
    {
        $defaults = $type === 'slides' ? [
            ['id' => 'welcome', 'title' => 'العب في المضمون', 'image' => '/images/home/couples-banner.jpg', 'mobile_image' => null, 'url' => '#games', 'sort_order' => 0, 'enabled' => true],
            ['id' => 'discover', 'title' => 'ليلة مختلفة تبدأ بلعبة', 'image' => null, 'mobile_image' => null, 'url' => '#games', 'sort_order' => 1, 'enabled' => true],
            ['id' => 'together', 'title' => 'تحديات تقرّبكم من بعض', 'image' => null, 'mobile_image' => null, 'url' => '#games', 'sort_order' => 2, 'enabled' => true],
        ] : [];
        $stored = Setting::get('home_'.$type);
        return $stored === null ? $defaults : (json_decode($stored, true) ?: []);
    }

    public static function visible(string $type): array
    {
        return collect(self::items($type))->where('enabled', true)->sortBy('sort_order')->values()->all();
    }
}
