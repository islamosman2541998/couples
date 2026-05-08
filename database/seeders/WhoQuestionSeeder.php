<?php

namespace Database\Seeders;

use App\Models\WhoQuestion;
use Illuminate\Database\Seeder;

class WhoQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // مضحك
            ['question' => 'مين فيكم ينام بسرعة أكتر؟', 'category' => 'funny', 'challenge' => 'اللي اتاخر في النوم يعمل صوت شخير لـ 10 ثواني'],
            ['question' => 'مين فيكم بيتذكر الأحلام أكتر؟', 'category' => 'funny', 'challenge' => 'كل واحد يحكي آخر حلم اتذكره'],
            ['question' => 'مين فيكم أكتر واحد بيزعل من التفاهات؟', 'category' => 'funny', 'challenge' => 'اعترف بآخر حاجة زعلتك'],
            ['question' => 'مين فيكم بيصرف أكتر على الأكل؟', 'category' => 'funny', 'challenge' => 'اللي بيصرف يختار العشاء الليلة'],
            ['question' => 'مين فيكم كسلان أكتر؟', 'category' => 'funny', 'challenge' => 'الكسلان يعمل 10 قفزات دلوقتي'],
            ['question' => 'مين فيكم بيتأخر لما بتخرجوا مع بعض؟', 'category' => 'funny', 'challenge' => 'المتأخر يوعد بإنه يكون جاهز قبل شريكه المرة الجاية'],

            // رومانسي
            ['question' => 'مين فيكم أكتر واحد بيحب التدليل؟', 'category' => 'romantic', 'challenge' => 'دلّل شريكك لمدة دقيقتين'],
            ['question' => 'مين فيكم بيقول "بحبك" أكتر؟', 'category' => 'romantic', 'challenge' => 'قول لشريكك ثلاث جمل حب'],
            ['question' => 'مين فيكم أكتر واحد بيفتكر المناسبات؟', 'category' => 'romantic', 'challenge' => 'ارسم خطة رومانسية للأسبوع الجاي'],
            ['question' => 'مين فيكم بيعبّر عن حبه أكتر بالأفعال؟', 'category' => 'romantic', 'challenge' => 'اعمل حاجة حلوة لشريكك دلوقتي'],
            ['question' => 'مين فيكم الأكثر رومانسية؟', 'category' => 'romantic', 'challenge' => 'الأقل رومانسية يكتب رسالة حب لشريكه'],

            // جريء
            ['question' => 'مين فيكم أكثر غيرة؟', 'category' => 'bold', 'challenge' => 'احكي موقف اتغيّرت فيه وسببه'],
            ['question' => 'مين فيكم بيتعصب بسرعة أكتر؟', 'category' => 'bold', 'challenge' => 'اعتذر عن آخر مرة اتعصبت فيها'],
            ['question' => 'مين فيكم أجرأ في اتخاذ القرارات؟', 'category' => 'bold', 'challenge' => 'الأقل جرأة يقرر حاجة كان مترددها'],
            ['question' => 'مين فيكم يتكيّف مع الضغوط أحسن؟', 'category' => 'bold', 'challenge' => 'اللي بيتضايق من الضغط يقول طريقته في التعامل معاه'],

            // تفكير
            ['question' => 'مين فيكم أكتر تنظيماً؟', 'category' => 'thinking', 'challenge' => 'الأقل تنظيماً يرتب حاجة في البيت دلوقتي'],
            ['question' => 'مين فيكم الأكثر تفاؤلاً؟', 'category' => 'thinking', 'challenge' => 'كل واحد يقول حاجة إيجابية عن المستقبل'],
            ['question' => 'مين فيكم يفكر أكتر في المستقبل؟', 'category' => 'thinking', 'challenge' => 'اتفقوا على هدف مشترك للسنة الجاية'],
            ['question' => 'مين فيكم بيتعلم من أخطاءه أسرع؟', 'category' => 'thinking', 'challenge' => 'شارك درساً تعلمته من تجربة مؤخراً'],
            ['question' => 'مين فيكم أكثر صبراً؟', 'category' => 'thinking', 'challenge' => 'الأقل صبراً يحاول يتحمل شيء مزعجه لمدة 10 ثواني بصمت'],
        ];

        foreach ($questions as $i => $q) {
            WhoQuestion::create([
                'question'   => $q['question'],
                'category'   => $q['category'],
                'challenge'  => $q['challenge'] ?? null,
                'is_active'  => true,
                'sort_order' => $i,
            ]);
        }
    }
}
