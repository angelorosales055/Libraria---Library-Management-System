<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id', 'book_id', 'issued_by',
        'action', 'status',
        'issued_date', 'due_date', 'returned_date',
        'fine', 'fine_paid', 'notes',
        'renewal_count', 'max_renewals',
    ];

    protected $casts = [
        'issued_date'    => 'date',
        'due_date'       => 'date',
        'returned_date'  => 'date',
        'fine'           => 'float',
        'fine_paid'      => 'boolean',
        'renewal_count'  => 'integer',
        'max_renewals'   => 'integer',
    ];

    const FINE_PER_DAY = 20; // ₱20 per day overdue

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Dynamically compute fine if overdue and not yet returned
     */
    public function getComputedFineAttribute(): float
    {
        if ($this->returned_date) return $this->fine ?? 0;
        if (!$this->due_date) return 0;

        $today = Carbon::today();
        if ($today->lte($this->due_date)) return 0;

        $days = $today->diffInDays($this->due_date);
        $fine = $days * self::FINE_PER_DAY;
        return min($fine, $this->book->fine_cap ?? 500.00);
    }

    public function getIsOverdueAttribute(): bool
    {
        return !$this->returned_date
            && $this->due_date
            && Carbon::today()->gt($this->due_date);
    }

    /**
     * Update status based on dates
     */
    public function calculateFine(?Carbon $onDate = null): float
    {
        if (!$this->due_date) {
            return 0;
        }

        $compareDate = $onDate ?? Carbon::today();
        if ($compareDate->lte($this->due_date)) {
            return 0;
        }

        $days = $compareDate->diffInDays($this->due_date);
        $fine = $days * self::FINE_PER_DAY;
        return min($fine, $this->book->fine_cap ?? 500.00);
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->due_date) {
            return 0;
        }

        $compareDate = $this->returned_date ? $this->returned_date : Carbon::today();
        return $compareDate->gt($this->due_date)
            ? $compareDate->diffInDays($this->due_date)
            : 0;
    }

    public function getIsPendingFineAttribute(): bool
    {
        return $this->fine > 0 && $this->fine_paid === false;
    }

    public function markReturned(bool $paid = false): self
    {
        $fine = $this->calculateFine(today());

        $this->update([
            'returned_date' => today(),
            'fine'          => $fine,
            'fine_paid'     => $paid || $fine === 0,
            'status'        => $fine > 0 && !$paid ? 'overdue' : 'returned',
            'action'        => 'return',
        ]);

        return $this;
    }

    public function collectFine(): self
    {
        if ($this->fine <= 0) {
            return $this;
        }

        $this->update([
            'fine_paid' => true,
            'status'    => 'returned',
        ]);

        return $this;
    }

    public function canRenew(): bool
    {
        return $this->renewal_count < $this->max_renewals
            && !$this->returned_date
            && !$this->book->pendingHolds()->exists()
            && !$this->member->is_suspended;
    }

    public function renew(): bool
    {
        if (!$this->canRenew()) {
            return false;
        }

        $this->update([
            'renewal_count' => $this->renewal_count + 1,
            'due_date' => $this->due_date->addDays($this->book->category->loan_period_days ?? 14),
            'action' => 'renew',
        ]);

        return true;
    }
}
