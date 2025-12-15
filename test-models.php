<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Budget;

echo "=== MODEL CREATION TESTS ===\n\n";

try {
    // Clean up test user
    User::where('email', 'test@backend.com')->delete();
    
    // 1. Create User
    echo "1. Creating User...\n";
    $user = User::create([
        'name' => 'Backend Test',
        'email' => 'test@backend.com',
        'password' => bcrypt('password123')
    ]);
    echo "   ✅ User ID: {$user->id}\n";
    
    // 2. Create Category
    echo "\n2. Creating Category...\n";
    $category = $user->categories()->create([
        'name' => 'Food & Dining',
        'color' => '#FF5733',
        'icon' => 'utensils'
    ]);
    echo "   ✅ Category ID: {$category->id}\n";
    echo "   Name: {$category->name}\n";
    echo "   Color: {$category->color}\n";
    
    // 3. Create Expense
    echo "\n3. Creating Expense...\n";
    $expense = $user->expenses()->create([
        'category_id' => $category->id,
        'amount' => 75.50,
        'date' => '2024-12-15',
        'description' => 'Dinner at restaurant'
    ]);
    echo "   ✅ Expense ID: {$expense->id}\n";
    echo "   Amount: \${$expense->amount}\n";
    echo "   Date: {$expense->date}\n";
    echo "   Description: {$expense->description}\n";
    
    // 4. Create Budget
    echo "\n4. Creating Budget...\n";
    $budget = $user->budgets()->create([
        'category_id' => $category->id,
        'amount' => 1000.00,
        'month' => 12,
        'year' => 2024
    ]);
    echo "   ✅ Budget ID: {$budget->id}\n";
    echo "   Amount: \${$budget->amount}\n";
    echo "   Month/Year: {$budget->month}/{$budget->year}\n";
    
    echo "\n🎉 All model creation tests passed!\n";
    
} catch (Exception $e) {
    echo "❌ Model test failed: " . $e->getMessage() . "\n";
}
