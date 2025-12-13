<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{


    public function run(): void
    {
                $user = User::first(); 
        
        $defaultCategories = [
            ['name' => 'Food', 'color' => '#FF6B6B'],
            ['name' => 'Transport', 'color' => '#4ECDC4'],
            ['name' => 'Entertainment', 'color' => '#FFD166'],
            ['name' => 'Utilities', 'color' => '#06D6A0'],
            ['name' => 'Shopping', 'color' => '#118AB2'],
            ['name' => 'Healthcare', 'color' => '#EF476F'],
        ];

        foreach ($defaultCategories as $category) {
            $user->categories()->create($category);
        }
    }
}