<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsVisit extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['last_seen_at' => 'datetime'];
    }

    public function visitor()
    {
        return $this->belongsTo(AnalyticsVisitor::class, 'visitor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(AnalyticsEvent::class, 'visit_id');
    }
}
