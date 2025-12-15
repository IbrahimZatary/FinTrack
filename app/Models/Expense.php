<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;
    
    // ADD category_id to fillable array
    protected $fillable = [
        'user_id', 
        'category_id',  // ← ADD THIS
        'amount', 
        'date', 
        'description', 
        'receipt_path'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];
    
    protected $dates = ['deleted_at'];
    
    // Fix type hint: BelongsTo not hasMany
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}