<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpinnerImage extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image', 'color', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
