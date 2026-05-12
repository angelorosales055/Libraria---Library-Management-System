<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'data', 'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function markAsRead(): self
    {
        $this->update(['read_at' => now()]);
        return $this;
    }

    public static function markAllAsRead(int $userId): int
    {
        return self::forUser($userId)->unread()->update(['read_at' => now()]);
    }

    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'borrow_success' => 'fa-book',
            'return_approved' => 'fa-check-circle',
            'book_damaged' => 'fa-exclamation-triangle',
            'payment_success' => 'fa-receipt',
            'overdue_reminder' => 'fa-clock',
            'rejected' => 'fa-times-circle',
            default => 'fa-bell',
        };
    }

    public function getIconColorAttribute(): string
    {
        return match ($this->type) {
            'borrow_success' => 'var(--accent)',
            'return_approved' => 'var(--accent)',
            'book_damaged' => 'var(--danger)',
            'payment_success' => 'var(--accent)',
            'overdue_reminder' => 'var(--danger)',
            'rejected' => 'var(--danger)',
            default => 'var(--muted)',
        };
    }
}
