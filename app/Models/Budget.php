<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    // ADD category_id to fillable
    protected $fillable = [
        'user_id', 
        'category_id',  // ← ADD THIS
        'amount', 
        'month', 
        'year'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'month' => 'integer',
        'year' => 'integer',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    public function getMonthYearAttribute(): string
    {
        return sprintf('%04d-%02d', $this->year, $this->month);
    }
}