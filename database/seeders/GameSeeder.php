<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        Game::create([
            'name'        => 'لعبة الكروت',
            'slug'        => 'card-game',
            'description' => 'لعبة كروت مميزة بين شخصين مع 3 مستويات من التحديات والأحكام الممتعة. اختبر نفسك مع شريكك في مستويات سهل ومتوسط وصعب!',
            'type'        => 'card',
            'is_free'     => false,
            'price'       => 29.99,
            'is_active'   => true,
            'sort_order'  => 1,
        ]);

        Game::create([
            'name'        => 'لعبة السبينر',
            'slug'        => 'spinner-game',
            'description' => 'عجلة دوارة مثيرة! لف العجلة واكتشف ما تخفيه من مفاجآت ممتعة. أنيميشن سلس وصور جذابة.',
            'type'        => 'spinner',
            'is_free'     => true,
            'price'       => 0,
            'is_active'   => true,
            'sort_order'  => 2,
        ]);
    }
}
