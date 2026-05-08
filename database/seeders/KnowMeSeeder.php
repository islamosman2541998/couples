<?php

namespace Database\Seeders;

use App\Models\KnowMeQuestion;
use Illuminate\Database\Seeder;

class KnowMeSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // يومي
            ['question' => 'إيه هو طبقه المفضل؟', 'category' => 'daily', 'answer_type' => 'open', 'hint' => 'فكّر في آخر مرة طلبتم أكل'],
            ['question' => 'بيصحى الصبح بدري ولا متأخر؟', 'category' => 'daily', 'answer_type' => 'choice', 'choices' => ['بدري جداً قبل 7', 'ما بين 7 و9', 'متأخر بعد 9', 'حسب اليوم']],
            ['question' => 'إيه نوع القهوة أو الشاي اللي بيحبه؟', 'category' => 'daily', 'answer_type' => 'open'],
            ['question' => 'بيفضل البيت هادي ولا فيه ناس؟', 'category' => 'daily', 'answer_type' => 'choice', 'choices' => ['هادي تماماً', 'ناس بس أحياناً', 'دايماً فيه حياة', 'حسب مزاجه']],
            ['question' => 'إيه نشاطه المفضل في الإجازة؟', 'category' => 'daily', 'answer_type' => 'open'],

            // مستقبل
            ['question' => 'فين يحب يسكن في المستقبل؟', 'category' => 'future', 'answer_type' => 'choice', 'choices' => ['فيلا في هادي', 'شقة في المدينة', 'بيت قريب العيلة', 'أي مكان معاك']],
            ['question' => 'إيه حلمه المهني اللي يتمنى يوصله؟', 'category' => 'future', 'answer_type' => 'open', 'hint' => 'مش لازم شغله الحالي'],
            ['question' => 'بيفضل يسافر قريب أم بعيد؟', 'category' => 'future', 'answer_type' => 'choice', 'choices' => ['داخل البلد', 'دول عربية', 'أوروبا وأمريكا', 'أي مكان جديد']],
            ['question' => 'إيه أكتر حاجة بيتمنى يحققها خلال 5 سنين؟', 'category' => 'future', 'answer_type' => 'open'],

            // عائلة
            ['question' => 'كام طفل يتمنى يعيش في بيته؟', 'category' => 'family', 'answer_type' => 'choice', 'choices' => ['طفل واحد', 'اتنين', 'تلاتة', 'أكتر من تلاتة']],
            ['question' => 'إيه أهم قيمة يريد يعلمها لأطفاله؟', 'category' => 'family', 'answer_type' => 'open', 'hint' => 'قيمة أو مبدأ واحد فقط'],
            ['question' => 'بيفضل يقضي الإجازات مع العيلة أم مع بعض بس؟', 'category' => 'family', 'answer_type' => 'choice', 'choices' => ['مع العيلة دايماً', 'أحياناً مع العيلة', 'معظم الوقت مع بعض', 'نص نص']],
            ['question' => 'علاقته مع أمه / أبوه كيف؟', 'category' => 'family', 'answer_type' => 'choice', 'choices' => ['قريبة جداً', 'كويسة', 'عادية', 'تحتاج تتحسن']],

            // شخصية
            ['question' => 'لو زعل من حاجة بيفضل يقولها ولا يسكت؟', 'category' => 'personality', 'answer_type' => 'choice', 'choices' => ['يقولها فوراً', 'ينتظر الوقت المناسب', 'يسكت وبعدين يقول', 'يسكت خالص']],
            ['question' => 'إيه أكتر حاجة بتضايقه في الناس؟', 'category' => 'personality', 'answer_type' => 'open'],
            ['question' => 'بيتخذ قراراته بعقله ولا قلبه؟', 'category' => 'personality', 'answer_type' => 'choice', 'choices' => ['بالعقل دايماً', 'العقل أكتر', 'القلب أكتر', 'بالقلب دايماً']],
            ['question' => 'لو عنده وقت فراغ إيه أول حاجة يعملها؟', 'category' => 'personality', 'answer_type' => 'open', 'hint' => 'أول حاجة تيجي في دماغه'],

            // قيم
            ['question' => 'إيه أهم صفة يدور عليها في شريك حياته؟', 'category' => 'values', 'answer_type' => 'open'],
            ['question' => 'إيه موقفه من الاختلاف في الرأي في الجوزين؟', 'category' => 'values', 'answer_type' => 'choice', 'choices' => ['طبيعي ومهم', 'كويس لو محدود', 'بيعمل مشكلة', 'لازم نتفق في كل حاجة']],
            ['question' => 'إيه المبدأ الأساسي اللي بيحكم حياته؟', 'category' => 'values', 'answer_type' => 'open', 'hint' => 'جملة أو كلمة واحدة'],

            // ترفيه
            ['question' => 'إيه النوع المفضل من الأفلام؟', 'category' => 'fun', 'answer_type' => 'choice', 'choices' => ['أكشن ومغامرات', 'كوميدي', 'رومانسي', 'رعب وإثارة', 'وثائقي']],
            ['question' => 'لو سافر أسبوع فين يروح؟', 'category' => 'fun', 'answer_type' => 'open', 'hint' => 'مكان واحد بس'],
            ['question' => 'إيه هوايته اللي بيحبها أكتر؟', 'category' => 'fun', 'answer_type' => 'open'],
            ['question' => 'بيفضل الأكل في البيت ولا برّا؟', 'category' => 'fun', 'answer_type' => 'choice', 'choices' => ['في البيت دايماً', 'في البيت أغلب الوقت', 'برّا أغلب الوقت', 'برّا دايماً']],
        ];

        foreach ($questions as $i => $q) {
            KnowMeQuestion::create([
                'question'    => $q['question'],
                'category'    => $q['category'],
                'answer_type' => $q['answer_type'],
                'choices'     => $q['choices'] ?? null,
                'hint'        => $q['hint'] ?? null,
                'is_active'   => true,
                'sort_order'  => $i,
            ]);
        }
    }
}
