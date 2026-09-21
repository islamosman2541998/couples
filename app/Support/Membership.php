<?php

namespace App\Support;

use App\Models\Game;
use App\Models\Setting;

class Membership
{
    public static function price(): float
    {
        $configured = Setting::get('membership_price');
        return $configured !== null && $configured !== ''
            ? max(0, (float) $configured)
            : (float) Game::active()->where('is_free', false)->sum('price');
    }

    public static function product(): object
    {
        return (object) ['id' => 'all', 'name' => 'باقة كل الألعاب', 'price' => self::price()];
    }

    public static function teaser(string $type): array
    {
        return match ($type) {
            'know_me' => ['♡', 'تعارف', 'فاكر إنك عارف كل إجاباته؟', 'كل إجابة فرصة تكتشفوا حاجة جديدة عن بعض.'],
            'who' => ['↔', 'ضحك', 'مين فيكم؟ الإجابة مش دايمًا متوقعة.', 'اختياراتكم هتفتح حكايات وتفكّركم بمواقف كتير.'],
            'control' => ['♛', 'تحدي', 'مين هيمسك زمام اللعبة؟', 'أدوار بتتبدّل وقرارات بتغيّر مسار السهرة.'],
            'snakes' => ['⚄', 'تحدي', 'رمية واحدة ممكن تقلب كل حاجة.', 'رحلة على اللوحة، وفي كل خطوة مفاجأة.'],
            'scratch' => ['✦', 'مفاجأة', 'ورا كل كارت… بداية حكاية.', 'اختاروا كارت واكتشفوا سوا التحدي اللي مستخبي.'],
            'spinner' => ['◉', 'ضحك', 'سيبوا الاختيار للحظ.', 'لفّة جديدة، اختيار مختلف، ولحظة تستاهل التجربة.'],
            'challenge' => ['⚡', 'تحدي', 'الكلام سهل… جاهزين للتحدي؟', 'حماس ومنافسة لطيفة على طريقتكم.'],
            default => ['♠', 'تعارف', 'كارت صغير يفتح كلام كتير.', 'أسئلة ومراحل تختاروا منها اللي يناسب مودكم.'],
        };
    }
}
