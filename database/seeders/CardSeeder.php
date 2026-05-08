<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\CardLevel;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    public function run(): void
    {
        $easy   = CardLevel::where('slug', 'easy')->first()->id;
        $medium = CardLevel::where('slug', 'medium')->first()->id;
        $hard   = CardLevel::where('slug', 'hard')->first()->id;

        // مستوى سهل
        $easyCards = [
            ['content' => 'قل لشريكك شيئاً جميلاً يجعله يبتسم', 'target' => 'both'],
            ['content' => 'اذكر ثلاثة أشياء تحبها في شريكتك', 'target' => 'male'],
            ['content' => 'اذكري ثلاثة أشياء تحبينها في شريكك', 'target' => 'female'],
            ['content' => 'قلّد حركة مضحكة من اختيار شريكك', 'target' => 'both'],
            ['content' => 'ما هو أجمل ذكرى مشتركة بينكما؟', 'target' => 'both'],
            ['content' => 'غنِّ مقطعاً من أغنية رومانسية', 'target' => 'male'],
            ['content' => 'غنّي مقطعاً من أغنية رومانسية', 'target' => 'female'],
            ['content' => 'قل لشريكتك: "أنتِ أجمل ما في حياتي"', 'target' => 'male'],
            ['content' => 'قولي لشريكك جملة حلوة من قلبك', 'target' => 'female'],
            ['content' => 'اختر وصفاً جميلاً لعيون شريكك', 'target' => 'both'],
        ];

        // مستوى متوسط
        $mediumCards = [
            ['content' => 'أخبر شريكك بشيء لم تخبره من قبل', 'target' => 'both'],
            ['content' => 'ما هو أكثر شيء يزعجك في نفسك؟ شاركه مع شريكك', 'target' => 'both'],
            ['content' => 'تصرف كأنك في مقابلة عمل مع شريكتك لمدة دقيقة', 'target' => 'male'],
            ['content' => 'تصرفي كأنك تقدمين برنامج تلفزيوني لمدة دقيقة', 'target' => 'female'],
            ['content' => 'ما هو حلمك الأكبر الذي لم تحققه بعد؟', 'target' => 'both'],
            ['content' => 'اصنع وجه مضحك وحاول إضحاك شريكك', 'target' => 'male'],
            ['content' => 'اصنعي وجهاً مضحكاً وحاولي إضحاك شريكك', 'target' => 'female'],
            ['content' => 'ماذا ستفعل لو كان لديك يوم كامل مع شريكك بلا مسؤوليات؟', 'target' => 'both'],
            ['content' => 'أين تتمنى السفر مع شريكتك وماذا ستفعلان؟', 'target' => 'male'],
            ['content' => 'صفي شريكك بثلاث كلمات فقط', 'target' => 'female'],
        ];

        // مستوى صعب
        $hardCards = [
            ['content' => 'قل لشريكك أصعب شيء واجهته في علاقتكما', 'target' => 'both'],
            ['content' => 'ماذا تغيّر في نفسك من أجل علاقتكما؟', 'target' => 'both'],
            ['content' => 'ما هو أكثر موقف أحرجك أمام شريكتك؟', 'target' => 'male'],
            ['content' => 'ما هو أكثر موقف أحرجك أمام شريكك؟', 'target' => 'female'],
            ['content' => 'لو يمكنك تغيير شيء واحد في علاقتكما ما هو؟', 'target' => 'both'],
            ['content' => 'أخبر شريكتك بأكبر خوف لديك', 'target' => 'male'],
            ['content' => 'أخبري شريكك بأكبر خوف لديك', 'target' => 'female'],
            ['content' => 'متى شعرت آخر مرة أنك بحاجة لشريكك؟', 'target' => 'both'],
            ['content' => 'ما الذي تتمنى أن يفهمه شريكك عنك أكثر؟', 'target' => 'both'],
            ['content' => 'اعترف بشيء أخطأت فيه مع شريكك ولم تعتذر عنه', 'target' => 'both'],
        ];

        foreach ($easyCards as $card) {
            Card::create(array_merge($card, ['card_level_id' => $easy, 'is_active' => true]));
        }
        foreach ($mediumCards as $card) {
            Card::create(array_merge($card, ['card_level_id' => $medium, 'is_active' => true]));
        }
        foreach ($hardCards as $card) {
            Card::create(array_merge($card, ['card_level_id' => $hard, 'is_active' => true]));
        }
    }
}
