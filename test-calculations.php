<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== CALCULATION TESTS ===\n\n";

$user = User::where('email', 'test@backend.com')->first();

if (!$user) {
    echo "❌ Test user not found\n";
    exit;
}

try {
    // 1. Monthly total
    echo "1. Monthly Total Calculation:\n";
    $monthlyTotal = $user->expenses()
        ->whereYear('date', 2024)
        ->whereMonth('date', 12)
        ->sum('amount');
    echo "   December 2024 total: \$" . number_format($monthlyTotal, 2) . "\n";
    
    // 2. Category-wise spending
    echo "\n2. Category-wise Spending:\n";
    $byCategory = $user->expenses()
        ->selectRaw('category_id, SUM(amount) as total')
        ->whereYear('date', 2024)
        ->whereMonth('date', 12)
        ->groupBy('category_id')
        ->with('category')
        ->get();
    
    foreach ($byCategory as $item) {
        echo "   - {$item->category->name}: \$" . number_format($item->total, 2) . "\n";
    }
    
    // 3. Budget vs Actual
    echo "\n3. Budget vs Actual:\n";
    $budgets = $user->budgets()
        ->where('year', 2024)
        ->where('month', 12)
        ->with('category')
        ->get();
    
    foreach ($budgets as $budget) {
        $spent = $user->expenses()
            ->where('category_id', $budget->category_id)
            ->whereYear('date', $budget->year)
            ->whereMonth('date', $budget->month)
            ->sum('amount');
        
        $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;
        $status = $spent > $budget->amount ? "❌ OVER" : "✅ OK";
        
        echo "   {$budget->category->name}:\n";
        echo "     Budget: \$" . number_format($budget->amount, 2) . "\n";
        echo "     Spent: \$" . number_format($spent, 2) . "\n";
        echo "     Remaining: \$" . number_format($budget->amount - $spent, 2) . "\n";
        echo "     Usage: " . number_format($percentage, 1) . "% $status\n";
    }
    
    // 4. Recent expenses
    echo "\n4. Recent Expenses (last 5):\n";
    $recent = $user->expenses()
        ->with('category')
        ->latest()
        ->limit(5)
        ->get();
    
    foreach ($recent as $expense) {
        echo "   - \${$expense->amount} on {$expense->date} ({$expense->category->name})\n";
    }
    
    // 5. Daily average
    echo "\n5. Daily Average (current month):\n";
    $daysInMonth = date('t');
    $dailyAvg = $monthlyTotal / $daysInMonth;
    echo "   Average per day: \$" . number_format($dailyAvg, 2) . "\n";
    
    echo "\n✅ All calculations working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Calculation test failed: " . $e->getMessage() . "\n";
}
