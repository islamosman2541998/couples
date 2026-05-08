<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhoQuestion extends Model
{
    protected $fillable = ['question', 'category', 'challenge', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public static array $categories = [
        'funny'     => ['label' => 'مضحك',    'emoji' => '😂', 'color' => '#f59e0b'],
        'romantic'  => ['label' => 'رومانسي', 'emoji' => '💕', 'color' => '#ec4899'],
        'bold'      => ['label' => 'جريء',    'emoji' => '🔥', 'color' => '#ef4444'],
        'thinking'  => ['label' => 'تفكير',   'emoji' => '🤔', 'color' => '#8b5cf6'],
    ];

    public function getCategoryLabelAttribute(): string
    {
        return self::$categories[$this->category]['label'] ?? $this->category;
    }

    public function getCategoryEmojiAttribute(): string
    {
        return self::$categories[$this->category]['emoji'] ?? '❓';
    }

    public function getCategoryColorAttribute(): string
    {
        return self::$categories[$this->category]['color'] ?? '#8b5cf6';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
