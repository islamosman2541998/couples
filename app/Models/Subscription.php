<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'game_id', 'full_name', 'phone', 'email',
        'receipt_image', 'status', 'admin_notes', 'approved_at', 'expires_at', 'is_bundle', 'amount',
    ];

    protected function casts(): array
    {
        return [
            'is_bundle' => 'boolean',
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'expires_at'  => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProductNameAttribute(): string
    {
        return $this->is_bundle ? 'باقة كل الألعاب' : ($this->game?->name ?? 'لعبة غير متاحة');
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function getReceiptUrlAttribute(): string
    {
        return route('admin.subscriptions.receipt', $this);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'approved' => 'مقبول',
            'rejected' => 'مرفوض',
            default    => 'قيد المراجعة',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'approved' => 'green',
            'rejected' => 'red',
            default    => 'yellow',
        };
    }
}
