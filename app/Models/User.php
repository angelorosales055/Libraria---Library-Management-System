<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'member_id', 'is_active',
        'school_id', 'contact', 'address', 'type', 'suspended_until', 'suspension_reason',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['status'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'suspended_until' => 'date',
    ];

    // Roles: 'admin', 'librarian', 'user'
    public function isAdmin(): bool       { return $this->role === 'admin'; }
    public function isLibrarian(): bool   { return $this->role === 'librarian'; }
    public function isStaff(): bool       { return in_array($this->role, ['admin','librarian']); }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'member_id');
    }

    public function activeTransactions()
    {
        return $this->transactions()->whereIn('status', ['active','overdue']);
    }

    public function getTotalBorrowedAttribute(): int
    {
        return $this->activeTransactions()->count();
    }

    public function getAllBorrowedAttribute(): int
    {
        return $this->transactions()->count();
    }

    public function getOutstandingFineAttribute(): float
    {
        return $this->transactions()
            ->where('fine_paid', false)
            ->where('fine', '>', 0)
            ->sum('fine');
    }

    public static function generateMemberId(): string
    {
        $lastId = self::where('role', 'user')->max('id') ?? 0;
        return 'MBR-'.date('Y').'-'.str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getBorrowingLimitAttribute(): int
    {
        return match($this->type) {
            'student' => 3,
            'faculty' => 10,
            'public'  => 2,
            default   => 3,
        };
    }

    public function getLoanPeriodDaysAttribute(): int
    {
        return match($this->type) {
            'student' => 14,
            'faculty' => 30,
            'public'  => 7,
            default   => 14,
        };
    }

    public function getHasOverdueBooksAttribute(): bool
    {
        return $this->transactions()->where('status', 'overdue')->exists();
    }

    public function getIsSuspendedAttribute(): bool
    {
        return $this->suspended_until && $this->suspended_until->isFuture();
    }

    public function getCanBorrowAttribute(): bool
    {
        return !$this->is_suspended
            && !$this->has_overdue_books
            && $this->outstanding_fine < 5.00
            && $this->total_borrowed < $this->borrowing_limit;
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_suspended) {
            return 'suspended';
        }

        if ($this->transactions()->where('status', 'overdue')->exists()) {
            return 'overdue';
        }

        return 'active';
    }

    public function holds()
    {
        return $this->hasMany(Hold::class);
    }
}
