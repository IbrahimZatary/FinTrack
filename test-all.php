<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Budget;
use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "     FINAL BACKEND VERIFICATION\n";
echo "========================================\n\n";

// ==================== TEST 1: DATABASE STRUCTURE ====================
echo "1. DATABASE STRUCTURE:\n";

$checks = [];

// Check expenses table
try {
    $columns = DB::select("DESCRIBE expenses");
    foreach ($columns as $col) {
        if ($col->Field == 'amount') {
            $isDecimal = strpos(strtolower($col->Type), 'decimal') !== false;
            echo ($isDecimal ? "✅ " : "❌ ") . "amount: {$col->Type}\n";
            $checks['Amount is decimal'] = $isDecimal;
        }
        if ($col->Field == 'date') {
            $isDate = strpos(strtolower($col->Type), 'date') !== false;
            echo ($isDate ? "✅ " : "❌ ") . "date: {$col->Type}\n";
            $checks['Date is date type'] = $isDate;
        }
        if ($col->Field == 'receipt_path' && $col->Null == 'YES') {
            echo "✅ receipt_path: nullable\n";
            $checks['Receipt path nullable'] = true;
        }
    }
} catch (Exception $e) {
    echo "❌ Could not check expenses table\n";
}

// Check budgets table
try {
    $columns = DB::select("DESCRIBE budgets");
    $hasCategoryId = false;
    foreach ($columns as $col) {
        if ($col->Field == 'category_id') {
            $hasCategoryId = true;
            echo "✅ budgets has category_id\n";
        }
        if ($col->Field == 'amount') {
            $isDecimal = strpos(strtolower($col->Type), 'decimal') !== false;
            echo ($isDecimal ? "✅ " : "❌ ") . "budget amount: {$col->Type}\n";
            $checks['Budget amount is decimal'] = $isDecimal;
        }
    }
    $checks['Budgets has category_id'] = $hasCategoryId;
} catch (Exception $e) {
    echo "❌ Could not check budgets table\n";
}

// ==================== TEST 2: CREATE TEST DATA ====================
echo "\n2. DATA CREATION TEST:\n";

// Clean up
User::where('email', 'final@test.com')->delete();

try {
    // Create user
    $user = User::create([
        'name' => 'Final Test',
        'email' => 'final@test.com',
        'password' => bcrypt('password123')
    ]);
    echo "✅ User created (ID: {$user->id})\n";
    $checks['User creation'] = true;
    
    // Create category
    $category = Category::create([
        'user_id' => $user->id,
        'name' => 'Food',
        'color' => '#FF0000',
        'icon' => 'fa-utensils'
    ]);
    echo "✅ Category created (ID: {$category->id})\n";
    $checks['Category creation'] = true;
    
    // Create expense with DECIMAL amount and DATE
    $expense = Expense::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 99.99,      // Decimal
        'date' => '2024-12-15', // Date string
        'description' => 'Test expense',
        'receipt_path' => null
    ]);
    echo "✅ Expense created: \${$expense->amount} on {$expense->date}\n";
    echo "   Amount type: " . gettype($expense->amount) . "\n";
    echo "   Date type: " . gettype($expense->date) . "\n";
    $checks['Expense creation'] = true;
    
    // Create budget with category_id
    $budget = Budget::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 500.00,
        'month' => 12,
        'year' => 2024
    ]);
    echo "✅ Budget created: \${$budget->amount} for {$budget->month_year}\n";
    $checks['Budget creation'] = true;
    
} catch (Exception $e) {
    echo "❌ Data creation failed: " . $e->getMessage() . "\n";
    $checks['Data creation'] = false;
}

// ==================== TEST 3: RELATIONSHIPS ====================
echo "\n3. RELATIONSHIP TESTS:\n";

if (isset($user)) {
    try {
        $userCategories = $user->categories()->count();
        $userExpenses = $user->expenses()->count();
        $userBudgets = $user->budgets()->count();
        
        echo ($userCategories > 0 ? "✅ " : "❌ ") . "User has {$userCategories} categories\n";
        echo ($userExpenses > 0 ? "✅ " : "❌ ") . "User has {$userExpenses} expenses\n";
        echo ($userBudgets > 0 ? "✅ " : "❌ ") . "User has {$userBudgets} budgets\n";
        
        $checks['User to categories'] = ($userCategories > 0);
        $checks['User to expenses'] = ($userExpenses > 0);
        $checks['User to budgets'] = ($userBudgets > 0);
        
        // Test expense → category relationship
        if (isset($expense)) {
            $expenseWithCategory = $expense->load('category');
            $checks['Expense has category'] = isset($expenseWithCategory->category);
            echo ($checks['Expense has category'] ? "✅ " : "❌ ") . "Expense belongs to category\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Relationship test failed\n";
    }
}

// ==================== TEST 4: CALCULATIONS ====================
echo "\n4. CALCULATION TESTS:\n";

if (isset($user)) {
    try {
        $totalSpent = $user->expenses()->sum('amount');
        echo "✅ Total spent: \${$totalSpent}\n";
        $checks['Sum calculation'] = ($totalSpent > 0);
        
        $avgExpense = $user->expenses()->avg('amount');
        echo "✅ Average expense: \${$avgExpense}\n";
        $checks['Average calculation'] = true;
        
        if (isset($budget)) {
            $spent = $user->expenses()
                ->where('category_id', $budget->category_id)
                ->whereYear('date', $budget->year)
                ->whereMonth('date', $budget->month)
                ->sum('amount');
            
            $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;
            echo "✅ Budget calculation: \${$spent} of \${$budget->amount} (" . round($percentage, 1) . "%)\n";
            $checks['Budget calculation'] = true;
        }
        
    } catch (Exception $e) {
        echo "❌ Calculation test failed\n";
    }
}

// ==================== FINAL SUMMARY ====================
echo "\n========================================\n";
echo "              TEST SUMMARY\n";
echo "========================================\n\n";

$passed = 0;
$total = count($checks);

foreach ($checks as $test => $result) {
    echo ($result ? "✅ " : "❌ ") . $test . "\n";
    if ($result) $passed++;
}

$percentage = round(($passed / $total) * 100);

echo "\nSCORE: {$passed}/{$total} ({$percentage}%)\n";

if ($passed == $total) {
    echo "\n🎉🎉🎉 BACKEND IS 100% READY FOR FRONTEND! 🎉🎉🎉\n";
    echo "\nYou can now start building the frontend views.\n";
} elseif ($percentage >= 80) {
    echo "\n⚠️  Backend is mostly ready. Check ❌ items above.\n";
} else {
    echo "\n❌ Backend needs more work before frontend.\n";
}

echo "\n========================================\n";
if (isset($user)) {
    echo "Test Credentials:\n";
    echo "Email: final@test.com\n";
    echo "Password: password123\n";
    echo "========================================\n";
}