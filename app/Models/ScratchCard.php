<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScratchCard extends Model
{
    public function getImageUrlAttribute(): string
    {
        return route('games.media', ['type' => 'scratch', 'id' => $this->id]);
    }

    protected $fillable = ['number', 'content', 'image', 'level', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean', 'level' => 'integer'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
