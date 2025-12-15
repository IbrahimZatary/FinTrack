<?php
echo "=== UPDATING ALL CONTROLLERS ===\n\n";

// 1. Update DashboardController
$dashboardFile = 'app/Http/Controllers/DashboardController.php';
if (file_exists($dashboardFile)) {
    $content = file_get_contents($dashboardFile);
    
    // Find the return statement at the end of index method
    $lines = explode("\n", $content);
    $inIndexMethod = false;
    $braceCount = 0;
    $returnFound = false;
    
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], 'public function index') !== false) {
            $inIndexMethod = true;
        }
        
        if ($inIndexMethod) {
            if (strpos($lines[$i], '{') !== false) $braceCount++;
            if (strpos($lines[$i], '}') !== false) $braceCount--;
            
            if (strpos($lines[$i], 'return response()->json') !== false) {
                // Replace with view return
                $lines[$i] = '        return view(\'dashboard.index\', [';
                $lines[$i+1] = '            \'period\' => [';
                $lines[$i+2] = '                \'month\' => $month,';
                $lines[$i+3] = '                \'year\' => $year';
                $lines[$i+4] = '            ]';
                $lines[$i+5] = '        ]);';
                $returnFound = true;
                $i += 5; // Skip the replaced lines
            }
            
            if ($braceCount === 0 && $inIndexMethod) {
                $inIndexMethod = false;
            }
        }
    }
    
    if ($returnFound) {
        file_put_contents($dashboardFile, implode("\n", $lines));
        echo "✅ Updated DashboardController to return view\n";
    } else {
        echo "⚠️ Could not find return statement in DashboardController\n";
    }
}

// 2. Simple update for ExpenseController
$expenseFile = 'app/Http/Controllers/ExpenseController.php';
if (file_exists($expenseFile)) {
    $content = file_get_contents($expenseFile);
    
    // Simple replace: find the JSON return and replace with view
    if (strpos($content, 'response()->json') !== false) {
        $pattern = '/return response\(\)->json\(\[.*?\]\);/s';
        $replacement = 'return view(\'expenses.index\', [
            \'expenses\' => $expenses,
            \'categories\' => auth()->user()->categories()->get()
        ]);';
        
        $content = preg_replace($pattern, $replacement, $content);
        file_put_contents($expenseFile, $content);
        echo "✅ Updated ExpenseController to return view\n";
    } else {
        echo "⚠️ ExpenseController might already return view\n";
    }
}

// 3. Simple update for CategoryController
$categoryFile = 'app/Http/Controllers/CategoryController.php';
if (file_exists($categoryFile)) {
    $content = file_get_contents($categoryFile);
    
    if (strpos($content, 'response()->json') !== false) {
        $pattern = '/return response\(\)->json\(\[.*?\]\);/s';
        $replacement = 'return view(\'categories.index\', [
            \'categories\' => $categories
        ]);';
        
        $content = preg_replace($pattern, $replacement, $content);
        file_put_contents($categoryFile, $content);
        echo "✅ Updated CategoryController to return view\n";
    } else {
        echo "⚠️ CategoryController might already return view\n";
    }
}

// 4. Simple update for BudgetController
$budgetFile = 'app/Http/Controllers/BudgetController.php';
if (file_exists($budgetFile)) {
    $content = file_get_contents($budgetFile);
    
    if (strpos($content, 'response()->json') !== false) {
        $pattern = '/return response\(\)->json\(\[.*?\]\);/s';
        $replacement = 'return view(\'budgets.index\', [
            \'budgets\' => $budgets,
            \'categories\' => auth()->user()->categories()->get(),
            \'months\' => [
                1 => \'January\', 2 => \'February\', 3 => \'March\', 4 => \'April\',
                5 => \'May\', 6 => \'June\', 7 => \'July\', 8 => \'August\',
                9 => \'September\', 10 => \'October\', 11 => \'November\', 12 => \'December\'
            ],
            \'current_year\' => date(\'Y\')
        ]);';
        
        $content = preg_replace($pattern, $replacement, $content);
        file_put_contents($budgetFile, $content);
        echo "✅ Updated BudgetController to return view\n";
    } else {
        echo "⚠️ BudgetController might already return view\n";
    }
}

echo "\n=== CONTROLLERS UPDATED ===\n";
