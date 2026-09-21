<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlCard extends Model
{
    public function getImageUrlAttribute(): string
    {
        return route('games.media', ['type' => 'control', 'id' => $this->id]);
    }

    protected $fillable = ['seed_key', 'title', 'description', 'image', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
