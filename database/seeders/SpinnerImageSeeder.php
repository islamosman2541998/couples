<?php

namespace Database\Seeders;

use App\Models\SpinnerImage;
use Illuminate\Database\Seeder;

class SpinnerImageSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'قبلة', 'color' => '#ec4899'],
            ['name' => 'عناق', 'color' => '#8b5cf6'],
            ['name' => 'مفاجأة', 'color' => '#f59e0b'],
            ['name' => 'رقصة', 'color' => '#3b82f6'],
            ['name' => 'هدية', 'color' => '#22c55e'],
            ['name' => 'أغنية', 'color' => '#ef4444'],
            ['name' => 'مغامرة', 'color' => '#14b8a6'],
            ['name' => 'وقت خاص', 'color' => '#f97316'],
        ];

        foreach ($items as $i => $item) {
            SpinnerImage::create([
                'name'       => $item['name'],
                'image'      => 'spinner/placeholder.png',
                'color'      => $item['color'],
                'is_active'  => true,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
