<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'color', 'sort_order'];

    public function cards()
    {
        return $this->hasMany(Card::class);
    }
}
