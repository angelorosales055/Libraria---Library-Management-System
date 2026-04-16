<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    protected $casts = [
        'loan_period_days' => 'integer'
    ];

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function getBooksCountAttribute(): int
    {
        return $this->books()->count();
    }
}
