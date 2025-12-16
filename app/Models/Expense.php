<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;
    
    // add category_id  
    protected $fillable = [
        'user_id', 
        'category_id',  // here also
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
