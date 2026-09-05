<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScratchCard extends Model
{
    protected $fillable = ['number', 'content', 'image', 'level', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean', 'level' => 'integer'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
