<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'image',
        'type', 'is_free', 'price', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return asset('images/game-placeholder.jpg');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
