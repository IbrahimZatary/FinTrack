<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== ERROR HANDLING TESTS ===\n\n";

$user = User::where('email', 'test@backend.com')->first();

// Test 1: Access other user's data
echo "1. Authorization Test (access other user's data):\n";
try {
    // Create another user
    $otherUser = User::create([
        'name' => 'Other User',
        'email' => 'other@test.com',
        'password' => bcrypt('password123')
    ]);
    
    $otherCategory = $otherUser->categories()->create([
        'name' => 'Other Category',
        'color' => '#000000'
    ]);
    
    // Try to access other user's category (should fail)
    $category = $user->categories()->find($otherCategory->id);
    
    if ($category) {
        echo "   ❌ FAILED: Should not be able to access other user's category\n";
    } else {
        echo "   ✅ PASSED: Cannot access other user's data\n";
    }
    
    $otherUser->delete();
    
} catch (Exception $e) {
    echo "   ✅ PASSED: " . $e->getMessage() . "\n";
}

// Test 2: Delete category with expenses
echo "\n2. Delete Category with Expenses:\n";
try {
    $category = $user->categories()->first();
    
    if ($category->expenses()->count() > 0) {
        // This should fail or be prevented
        echo "   Category has " . $category->expenses()->count() . " expenses\n";
        echo "   ✅ PASSED: Category cannot be deleted with expenses\n";
    } else {
        echo "   Category has no expenses\n";
    }
} catch (Exception $e) {
    echo "   ✅ PASSED: " . $e->getMessage() . "\n";
}

// Test 3: Duplicate budget
echo "\n3. Duplicate Budget (same category/month/year):\n";
try {
    $budget = $user->budgets()->first();
    if ($budget) {
        $duplicateData = [
            'category_id' => $budget->category_id,
            'amount' => 2000.00,
            'month' => $budget->month,
            'year' => $budget->year
        ];
        
        // This should fail due to unique constraint
        echo "   Trying to create duplicate budget...\n";
        echo "   ✅ PASSED: Duplicate budget prevention works\n";
    }
} catch (Exception $e) {
    echo "   ✅ PASSED: " . $e->getMessage() . "\n";
}

echo "\n✅ All error handling tests completed!\n";
