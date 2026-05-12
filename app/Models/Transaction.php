<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id', 'book_id', 'issued_by', 'original_transaction_id',
        'action', 'status',
        'issued_date', 'due_date', 'returned_date',
        'fine', 'fine_paid', 'paid_at', 'collected_by', 'receipt_no', 'notes',
        'payment_method', 'paymongo_reference',
        'renewal_count', 'max_renewals',
    ];

    protected $casts = [
        'issued_date'    => 'date',
        'due_date'       => 'date',
        'returned_date'  => 'date',
        'fine'           => 'float',
        'fine_paid'      => 'boolean',
        'paid_at'        => 'datetime',
        'renewal_count'  => 'integer',
        'max_renewals'   => 'integer',
        'original_transaction_id' => 'integer',
    ];

    const FINE_PER_DAY = 25; // ₱25 per day overdue

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

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function originalTransaction()
    {
        return $this->belongsTo(Transaction::class, 'original_transaction_id');
    }

    public function renewRequests()
    {
        return $this->hasMany(Transaction::class, 'original_transaction_id');
    }

    public function canRequestRenew(): bool
    {
        return $this->status === 'active' 
            && $this->canRenew()
            && $this->renewRequests()->whereIn('status', ['renew_requested', 'pending'])->doesntExist();
    }

    public static function generateReceiptNo(): string
    {
        $date = now()->format('Ymd');
        $last = self::whereDate('updated_at', today())->whereNotNull('receipt_no')->count();
        return 'RCP-' . $date . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Dynamically compute fine if overdue and not yet returned
     */
    public function getComputedFineAttribute(): float
    {
        if ($this->returned_date) return max(0, $this->fine ?? 0);
        if (!$this->due_date) return 0;

        $today = Carbon::today();
        if ($today->lte($this->due_date)) return 0;

        $days = $today->diffInDays($this->due_date, true);
        $fine = $days * self::FINE_PER_DAY;
        return min(max(0, $fine), $this->book->fine_cap ?? 500.00);
    }

    public function getIsOverdueAttribute(): bool
    {
        return !$this->returned_date
            && $this->due_date
            && Carbon::today()->gt($this->due_date);
    }

    public function getOutstandingFineAttribute(): float
    {
        if ($this->fine > 0) {
            return $this->fine;
        }

        if (!in_array($this->status, ['active', 'overdue', 'damage_return'], true)) {
            return 0;
        }

        return $this->computed_fine;
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

        $days = $compareDate->diffInDays($this->due_date, true);
        $fine = $days * self::FINE_PER_DAY;
        return min(max(0, $fine), $this->book->fine_cap ?? 500.00);
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->due_date) {
            return 0;
        }

        $compareDate = $this->returned_date ? $this->returned_date : Carbon::today();
        return $compareDate->gt($this->due_date)
            ? max(0, $compareDate->diffInDays($this->due_date, true))
            : 0;
    }

    public function getOverdueFineAmountAttribute(): float
    {
        if (!$this->due_date) {
            return 0;
        }

        $compareDate = $this->returned_date ? $this->returned_date : Carbon::today();
        if ($compareDate->lte($this->due_date)) {
            return 0;
        }

        $days = $compareDate->diffInDays($this->due_date, true);
        $fine = $days * self::FINE_PER_DAY;
        return min(max(0, $fine), $this->book->fine_cap ?? 500.00);
    }

    public function getDamageFeeAmountAttribute(): float
    {
        return max(0, $this->fine - $this->overdue_fine_amount);
    }

    public function getIsPendingFineAttribute(): bool
    {
        if ($this->fine > 0 && $this->fine_paid === false && in_array($this->status, ['active', 'overdue', 'damage_return', 'damaged', 'returned'], true)) {
            return true;
        }

        return $this->fine_paid === false
            && !$this->returned_date
            && $this->due_date
            && in_array($this->status, ['active', 'overdue'], true)
            && Carbon::today()->gt($this->due_date);
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

        $data = [
            'fine_paid' => true,
        ];

        if ($this->action === 'damage_return' || $this->status === 'returned') {
            $data['status'] = 'returned';
        }

        $this->update($data);

        if ($this->action !== 'damage_return' && $this->book) {
            $this->book->increment('available_copies');
        }

        return $this;
    }

    public function completeFinePayment(string $paymentMethod): self
    {
        $fineAmount = $this->fine > 0 ? $this->fine : $this->computed_fine;
        if ($fineAmount <= 0) {
            return $this;
        }

        $status = $this->status;
        if ($this->action === 'damage_return' || $this->status === 'returned') {
            $status = 'returned';
        }

        $data = [
            'fine'           => $fineAmount,
            'fine_paid'      => true,
            'status'         => $status,
            'paid_at'        => now(),
            'collected_by'   => auth()->id(),
            'receipt_no'     => self::generateReceiptNo(),
            'payment_method' => $paymentMethod,
        ];

        if ($this->action === 'damage_return' && !$this->returned_date) {
            $data['returned_date'] = now();
        }

        $this->update($data);

        if ($this->action !== 'damage_return' && $this->book) {
            $this->book->increment('available_copies');
        }

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

    /**
     * Mark as damaged return - no qty increment to book
     */
    public function handleDamage(float $fee, string $reason): self
    {
        $this->update([
            'returned_date' => today(),
            'fine' => $fee,
            'fine_paid' => false,
            'status' => 'damage_return',
            'action' => 'damage_return',
            'notes' => trim(($this->notes ?? '') . "\n[DAMAGE: {$reason}]"),
        ]);

        return $this;
    }

    public function getIsDamageFineAttribute(): bool
    {
        return $this->action === 'damage_return' || $this->status === 'damaged' || $this->status === 'damage_return';
    }
}


