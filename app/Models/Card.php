<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_level_id', 'content', 'target', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function level()
    {
        return $this->belongsTo(CardLevel::class, 'card_level_id');
    }

    public function getTargetLabelAttribute(): string
    {
        return match($this->target) {
            'male'   => 'راجل',
            'female' => 'ست',
            default  => 'مشترك',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
