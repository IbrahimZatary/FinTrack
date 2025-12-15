<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Budget;

echo "=== MODEL RELATIONSHIPS TEST ===\n\n";

// Get or create user
$user = User::firstOrCreate(
    ['email' => 'test@test.com'],
    ['name' => 'Test', 'password' => bcrypt('password123')]
);

// TEST 1: User → Categories
echo "1. User → Categories: ";
try {
    $category = $user->categories()->create(['name' => 'Food', 'color' => '#FF0000']);
    echo ($category->id) ? "✅ WORKING (Created ID: {$category->id})" : "❌ FAILED";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
echo "\n";

// TEST 2: User → Expenses
echo "2. User → Expenses: ";
try {
    $expense = $user->expenses()->create([
        'category_id' => $category->id,
        'amount' => 50.00,
        'date' => date('Y-m-d'),
        'description' => 'Test'
    ]);
    echo ($expense->id) ? "✅ WORKING (Created ID: {$expense->id})" : "❌ FAILED";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
echo "\n";

// TEST 3: User → Budgets
echo "3. User → Budgets: ";
try {
    $budget = $user->budgets()->create([
        'category_id' => $category->id,
        'amount' => 500.00,
        'month' => date('m'),
        'year' => date('Y')
    ]);
    echo ($budget->id) ? "✅ WORKING (Created ID: {$budget->id})" : "❌ FAILED";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
echo "\n";

// TEST 4: Category → Expenses
echo "4. Category → Expenses: ";
try {
    $catExpenses = $category->expenses()->count();
    echo ($catExpenses > 0) ? "✅ WORKING ({$catExpenses} expenses)" : "❌ FAILED (0 expenses)";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
echo "\n";

// TEST 5: Expense → Category
echo "5. Expense → Category: ";
try {
    $expCategory = $expense->category->name;
    echo ($expCategory) ? "✅ WORKING (Category: {$expCategory})" : "❌ FAILED";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
echo "\n";

// SUMMARY
echo "\n=== SUMMARY ===\n";
$allTests = [
    'User created' => isset($user->id),
    'Category created' => isset($category->id),
    'Expense created' => isset($expense->id),
    'Budget created' => isset($budget->id),
    'Category has expenses' => $category->expenses()->count() > 0,
    'Expense has category' => isset($expense->category),
];

foreach ($allTests as $test => $result) {
    echo $result ? "✅ $test\n" : "❌ $test\n";
}