<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowMeQuestion extends Model
{
    protected $fillable = ['question', 'category', 'answer_type', 'choices', 'hint', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        'choices'   => 'array',
    ];

    public static array $categories = [
        'daily'       => ['label' => 'يومي',     'emoji' => '🌙', 'color' => '#6366f1'],
        'future'      => ['label' => 'مستقبل',   'emoji' => '🔮', 'color' => '#8b5cf6'],
        'family'      => ['label' => 'عائلة',    'emoji' => '👨‍👩‍👧', 'color' => '#ec4899'],
        'personality' => ['label' => 'شخصية',    'emoji' => '🧠', 'color' => '#f59e0b'],
        'values'      => ['label' => 'قيم',      'emoji' => '💜', 'color' => '#10b981'],
        'fun'         => ['label' => 'ترفيه',    'emoji' => '✈️', 'color' => '#06b6d4'],
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
