<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SnakeCell extends Model
{
    public const MOODS = ['warm' => 'تقارب', 'playful' => 'مرح', 'romantic' => 'رومانسية'];

    protected $fillable = ['number', 'title', 'content', 'mood', 'is_active'];

    protected function casts(): array
    {
        return ['number' => 'integer', 'is_active' => 'boolean'];
    }
}
