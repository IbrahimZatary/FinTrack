<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;  // ← MAKE SURE THIS IS IMPORTED

class Category extends Model
{
    protected $fillable = [
        'user_id', 
        'name', 
        'color', 
        'icon'
    ];
    
    // Return type should match the actual return
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // This returns HasMany, so type hint should be HasMany
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
    
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}