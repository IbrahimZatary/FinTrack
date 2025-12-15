<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== RELATIONSHIP TESTS ===\n\n";

$user = User::where('email', 'test@backend.com')->first();

if (!$user) {
    echo "❌ Test user not found. Run model tests first.\n";
    exit;
}

try {
    echo "1. User → Categories:\n";
    $categories = $user->categories;
    echo "   Count: " . $categories->count() . "\n";
    foreach ($categories as $cat) {
        echo "   - {$cat->name} (#{$cat->color})\n";
    }
    
    echo "\n2. User → Expenses:\n";
    $expenses = $user->expenses;
    echo "   Count: " . $expenses->count() . "\n";
    foreach ($expenses as $exp) {
        echo "   - \${$exp->amount} on {$exp->date}: {$exp->description}\n";
    }
    
    echo "\n3. User → Budgets:\n";
    $budgets = $user->budgets;
    echo "   Count: " . $budgets->count() . "\n";
    foreach ($budgets as $bud) {
        echo "   - \${$bud->amount} for {$bud->month}/{$bud->year}\n";
    }
    
    echo "\n4. Expense → Category:\n";
    $expense = $user->expenses()->first();
    if ($expense) {
        echo "   Expense amount: \${$expense->amount}\n";
        echo "   Category: {$expense->category->name}\n";
        echo "   Category color: {$expense->category->color}\n";
    }
    
    echo "\n5. Budget → Category:\n";
    $budget = $user->budgets()->first();
    if ($budget) {
        echo "   Budget amount: \${$budget->amount}\n";
        echo "   Category: {$budget->category->name}\n";
    }
    
    echo "\n6. Category → Expenses:\n";
    $category = $user->categories()->first();
    if ($category) {
        $categoryExpenses = $category->expenses;
        echo "   Category: {$category->name}\n";
        echo "   Total spent: \${$category->expenses()->sum('amount')}\n";
        echo "   Expense count: " . $categoryExpenses->count() . "\n";
    }
    
    echo "\n✅ All relationship tests passed!\n";
    
} catch (Exception $e) {
    echo "❌ Relationship test failed: " . $e->getMessage() . "\n";
}
