<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'name',
        'color'
    ];
    
    // Prevent duplicate category names for same user
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            // Check if user already has category with same name
            $exists = self::where('user_id', $category->user_id)
                         ->where('name', $category->name)
                         ->exists();
            
            if ($exists) {
                throw new \Exception('You already have a category with this name.');
            }
        });
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
