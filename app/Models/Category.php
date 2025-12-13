<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{


protected $fillable =[
    'user_id',   
    'name',       
    'color',      
    'icon',  

];
// RELATIONSHIPS
public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

  
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

  
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }


    use HasFactory;
}
