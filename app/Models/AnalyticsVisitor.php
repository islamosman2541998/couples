<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsVisitor extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function visits()
    {
        return $this->hasMany(AnalyticsVisit::class, 'visitor_id');
    }
}
