<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'isbn', 'accession_no', 'title', 'author',
        'category_id', 'copies', 'available_copies',
        'shelf', 'cover_image', 'description',
        'fine_cap', 'replacement_cost', 'is_circulating',
    ];

    protected $casts = [
        'copies' => 'integer',
        'available_copies' => 'integer',
        'fine_cap' => 'decimal:2',
        'replacement_cost' => 'decimal:2',
        'is_circulating' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function activeTransactions()
    {
        return $this->transactions()->whereIn('status', ['active','overdue']);
    }

    public function getIsAvailableAttribute(): bool
    {
        return ($this->available_copies ?? $this->copies) > 0;
    }

    /**
     * Scope: available books only
     */
    public function scopeAvailable($q)
    {
        return $q->where('available_copies', '>', 0);
    }

    /**
     * Scope: search by title, author, ISBN
     */
    public function scopeSearch($q, string $term)
    {
        return $q->where(function($query) use ($term) {
            $query->where('title',  'like', "%{$term}%")
                  ->orWhere('author','like', "%{$term}%")
                  ->orWhere('isbn',  'like', "%{$term}%")
                  ->orWhere('accession_no', 'like', "%{$term}%");
        });
    }

/**
     * Scope: filter by category
     */
    public function scopeByCategory($q, $categoryId)
    {
        return $q->where('category_id', (int) $categoryId);
    }

    public function holds()
    {
        return $this->hasMany(Hold::class);
    }

    public function pendingHolds()
    {
        return $this->holds()->pending();
    }
}
