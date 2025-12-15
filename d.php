<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Budget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "========================================\n";
echo "     COMPLETE BACKEND VERIFICATION\n";
echo "========================================\n\n";

$checks = [];

// ==================== TEST 1: MODELS EXIST ====================
echo "1. MODEL CLASSES:\n";
$models = [
    'User' => 'App\\Models\\User',
    'Category' => 'App\\Models\\Category',
    'Expense' => 'App\\Models\\Expense',
    'Budget' => 'App\\Models\\Budget'
];

foreach ($models as $name => $class) {
    $exists = class_exists($class);
    echo ($exists ? "✅ " : "❌ ") . "{$name} model\n";
    $checks["{$name} model exists"] = $exists;
}

// ==================== TEST 2: RELATIONSHIPS ====================
echo "\n2. MODEL RELATIONSHIPS:\n";

// Create test user
try {
    // Clean up first
    User::where('email', 'verify@test.com')->delete();
    
    $user = User::create([
        'name' => 'Verify Test',
        'email' => 'verify@test.com',
        'password' => bcrypt('password123')
    ]);
    echo "✅ Test user created (ID: {$user->id})\n";
    $checks['User creation'] = true;
    
    // Test relationships
    $category = $user->categories()->create([
        'name' => 'Test Category',
        'color' => '#FF0000',
        'icon' => 'test'
    ]);
    echo "✅ User->categories() relationship works\n";
    $checks['User->categories()'] = true;
    
    $expense = $user->expenses()->create([
        'category_id' => $category->id,
        'amount' => 100.50,
        'date' => '2024-12-15',
        'description' => 'Test expense'
    ]);
    echo "✅ User->expenses() relationship works\n";
    $checks['User->expenses()'] = true;
    
    $budget = $user->budgets()->create([
        'category_id' => $category->id,
        'amount' => 500.00,
        'month' => 12,
        'year' => 2024
    ]);
    echo "✅ User->budgets() relationship works\n";
    $checks['User->budgets()'] = true;
    
    // Test inverse relationships
    $expenseCategory = $expense->category;
    echo ($expenseCategory ? "✅ " : "❌ ") . "Expense->category() relationship works\n";
    $checks['Expense->category()'] = (bool)$expenseCategory;
    
    $budgetCategory = $budget->category;
    echo ($budgetCategory ? "✅ " : "❌ ") . "Budget->category() relationship works\n";
    $checks['Budget->category()'] = (bool)$budgetCategory;
    
} catch (Exception $e) {
    echo "❌ Relationship test failed: " . $e->getMessage() . "\n";
    $checks['Relationships'] = false;
}

// ==================== TEST 3: CONTROLLERS EXIST ====================
echo "\n3. CONTROLLERS:\n";
$controllers = [
    'ExpenseController',
    'CategoryController',
    'BudgetController',
    'DashboardController',
    'AnalyticsController'
];

foreach ($controllers as $controller) {
    $path = "app/Http/Controllers/{$controller}.php";
    $exists = file_exists($path);
    echo ($exists ? "✅ " : "❌ ") . "{$controller}\n";
    $checks["{$controller} exists"] = $exists;
}

// ==================== TEST 4: ROUTES ====================
echo "\n4. ROUTES:\n";

$webRoutes = file_get_contents('routes/web.php');
$apiRoutes = file_get_contents('routes/api.php');

$routeTests = [
    'Auth middleware' => strpos($webRoutes, "Route::middleware(['auth']") !== false,
    'Expense routes' => strpos($webRoutes, "Route::resource('expenses'") !== false || 
                       strpos($webRoutes, "Route::get('/expenses'") !== false,
    'Category routes' => strpos($webRoutes, "Route::resource('categories'") !== false,
    'Budget routes' => strpos($webRoutes, "Route::resource('budgets'") !== false,
    'Dashboard route' => strpos($webRoutes, "Route::get('/dashboard'") !== false,
    'API analytics' => strpos($apiRoutes, '/analytics/') !== false,
];

foreach ($routeTests as $test => $exists) {
    echo ($exists ? "✅ " : "❌ ") . "{$test}\n";
    $checks[$test] = $exists;
}

// ==================== TEST 5: VALIDATION REQUESTS ====================
echo "\n5. VALIDATION REQUESTS:\n";
$validationFiles = [
    'StoreExpenseRequest',
    'UpdateExpenseRequest',
    'StoreCategoryRequest'
];

foreach ($validationFiles as $request) {
    $path = "app/Http/Requests/{$request}.php";
    $exists = file_exists($path);
    echo ($exists ? "✅ " : "❌ ") . "{$request}\n";
    $checks["{$request} exists"] = $exists;
}

// ==================== TEST 6: CRITICAL FUNCTIONALITY ====================
echo "\n6. CRITICAL FUNCTIONALITY:\n";

if (isset($user)) {
    try {
        // Test calculations
        $totalExpenses = $user->expenses()->sum('amount');
        echo ($totalExpenses == 100.50 ? "✅ " : "❌ ") . "Sum calculation: \${$totalExpenses}\n";
        $checks['Sum calculation works'] = ($totalExpenses == 100.50);
        
        // Test category expenses
        $categoryExpenses = $category->expenses()->sum('amount');
        echo ($categoryExpenses == 100.50 ? "✅ " : "❌ ") . "Category expenses: \${$categoryExpenses}\n";
        $checks['Category expenses sum'] = ($categoryExpenses == 100.50);
        
        // Test soft delete
        $expense->delete();
        $trashed = Expense::withTrashed()->find($expense->id);
        echo ($trashed && $trashed->trashed() ? "✅ " : "❌ ") . "Soft delete works\n";
        $checks['Soft delete works'] = ($trashed && $trashed->trashed());
        
        // Restore for cleanup
        $expense->restore();
        
    } catch (Exception $e) {
        echo "❌ Functionality test failed: " . $e->getMessage() . "\n";
    }
}

// ==================== TEST 7: AUTHENTICATION ====================
echo "\n7. AUTHENTICATION:\n";

$authFiles = [
    'Login/Register views' => 'resources/views/auth',
    'Auth controllers' => 'app/Http/Controllers/Auth',
    'Breeze installed' => file_exists('vendor/laravel/breeze')
];

foreach ($authFiles as $test => $path) {
    if (is_string($path)) {
        $exists = file_exists($path) || file_exists($path . '.php');
    } else {
        $exists = $path;
    }
    echo ($exists ? "✅ " : "⚠️  ") . "{$test}\n";
}

// ==================== FINAL SUMMARY ====================
echo "\n========================================\n";
echo "              VERIFICATION SUMMARY\n";
echo "========================================\n\n";

$passed = 0;
$total = count($checks);

foreach ($checks as $test => $result) {
    echo ($result ? "✅ " : "❌ ") . $test . "\n";
    if ($result) $passed++;
}

$percentage = round(($passed / $total) * 100);

echo "\nSCORE: {$passed}/{$total} ({$percentage}%)\n\n";

if ($passed == $total) {
    echo "🎉🎉🎉 BACKEND IS 100% COMPLETE! 🎉🎉🎉\n\n";
    echo "You can now START FRONTEND DEVELOPMENT!\n";
    
    // Show next steps
    echo "\n========================================\n";
    echo "           NEXT STEPS - FRONTEND\n";
    echo "========================================\n";
    echo "1. Create Blade layouts\n";
    echo "2. Build dashboard view\n";
    echo "3. Create expense forms\n";
    echo "4. Add Chart.js for analytics\n";
    echo "5. Style with CSS/Tailwind\n";
    
} elseif ($percentage >= 80) {
    echo "⚠️  Backend is mostly ready. Check ❌ items above.\n";
    echo "Fix missing components before frontend.\n";
} else {
    echo "❌ Backend needs work. Complete phases 4-6 first.\n";
}

// Cleanup
if (isset($user)) {
    $user->delete();
}

echo "\nTest user deleted.\n";
echo "========================================\n";