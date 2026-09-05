<?php

namespace Database\Seeders;

use App\Models\ScratchCard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ScratchBoardSeeder extends Seeder
{
    public function run(): void
    {
        if (ScratchCard::count() >= 100) return;
        $library = require database_path('seeders/data/romantic-content.php');
        $tasks = array_merge($library['scratch'], ...array_values($library['cards']));
        $tasks = array_merge($tasks, array_column($library['challenges'], 2), [
            'كل واحد يرسم رمزًا صغيرًا لعلاقتكم، وبعدها اشرحوا الرسمة لبعض.',
            'اختاروا كلمة سر لطيفة معناها نسيب الموبايلات ونقعد سوا عشر دقائق.',
            'كل واحد يكتب عنوان فصل جديد في حكايتكم، ويحكي أول مشهد فيه.',
            'اختاروا ريحة أو لونًا أو أغنية بتفكركم بذكرى جميلة، واحكوا السبب.',
            'كل واحد يتخيل رسالة من نفسه بعد سنة، ويقرأ لشريكه جملة منها.',
            'قولوا حاجتين اتعلمتوهم من بعض، وحاجة جديدة نفسكم تتعلموها سوا.',
            'صمموا قائمة من ثلاثة أشياء بسيطة تعملوها في يوم مميز من غير استعجال.',
        ]);
        DB::transaction(function () use ($tasks) {
            $existing = ScratchCard::orderBy('sort_order')->orderBy('number')->lockForUpdate()->get();
            if ($existing->isNotEmpty() && !app()->environment('testing')) {
                $path = 'backups/scratch-board/'.now()->format('Ymd-His').'-'.Str::uuid().'.json';
                $json = $existing->toJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
                if (!Storage::disk('local')->put($path, $json) || Storage::disk('local')->get($path) !== $json) {
                    throw new RuntimeException('Could not verify the scratch-content backup. No changes made.');
                }
                $this->command?->info('Scratch backup: '.Storage::disk('local')->path($path));
            }
            $used = $existing->pluck('content')->all();
            $number = (int) ScratchCard::max('number');
            foreach (array_unique($tasks) as $task) {
                if (count($used) >= 100) break;
                if (in_array($task, $used, true)) continue;
                ScratchCard::create(['number' => ++$number, 'content' => $task, 'image' => null, 'level' => 1, 'is_active' => true, 'sort_order' => count($used) + 1]);
                $used[] = $task;
            }
            foreach (ScratchCard::orderBy('sort_order')->orderBy('number')->get() as $index => $card) {
                $card->update(['level' => $index < 30 ? 1 : ($index < 60 ? 2 : 3), 'sort_order' => $index + 1]);
            }
        });
    }
}
