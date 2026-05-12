<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'details', 'status', 'ip_address'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public static function log(?int $userId, string $type, string $details, string $status = 'success', ?string $ip = null): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'details' => $details,
            'status' => $status,
            'ip_address' => $ip ?? request()->ip(),
        ]);
    }
}
