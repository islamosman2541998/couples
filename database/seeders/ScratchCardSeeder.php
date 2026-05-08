<?php

namespace Database\Seeders;

use App\Models\ScratchCard;
use Illuminate\Database\Seeder;

class ScratchCardSeeder extends Seeder
{
    public function run(): void
    {
        $cards = [
            ['number' => 1,  'content' => 'قول لشريكك ثلاثة أشياء تحبها فيه/فيها'],
            ['number' => 2,  'content' => 'اعمل تدليك للكتف لمدة دقيقتين'],
            ['number' => 3,  'content' => 'غنّ أغنية رومانسية لمدة 30 ثانية'],
            ['number' => 4,  'content' => 'احكِ ذكرى جميلة جمعتكم معاً'],
            ['number' => 5,  'content' => 'قول أكثر شيء يجعلك تبتسم عند التفكير فيه'],
            ['number' => 6,  'content' => 'اعمل وجه مضحك وصوّر سيلفي مع شريكك'],
            ['number' => 7,  'content' => 'قول ثلاثة أشياء تتمنى فعلها مع شريكك'],
            ['number' => 8,  'content' => 'ارسم قلب على ورق وأعطه لشريكك'],
            ['number' => 9,  'content' => 'احكِ لماذا اخترت شريكك من بين الجميع'],
            ['number' => 10, 'content' => 'قول شيئاً يميّز شريكك عن غيره'],
            ['number' => 11, 'content' => 'اعمل حركة رقص كوميدية لمدة 20 ثانية'],
            ['number' => 12, 'content' => 'قول ما هو حلمك الأكبر في الحياة'],
        ];

        foreach ($cards as $i => $card) {
            ScratchCard::create([
                'number'     => $card['number'],
                'content'    => $card['content'],
                'image'      => null,
                'is_active'  => true,
                'sort_order' => $i,
            ]);
        }
    }
}
