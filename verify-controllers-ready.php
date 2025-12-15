<?php
echo "=== VERIFYING CONTROLLERS ARE READY ===\n\n";

$controllers = [
    'DashboardController' => 'app/Http/Controllers/DashboardController.php',
    'ExpenseController' => 'app/Http/Controllers/ExpenseController.php',
    'CategoryController' => 'app/Http/Controllers/CategoryController.php',
    'BudgetController' => 'app/Http/Controllers/BudgetController.php'
];

$allReady = true;

foreach ($controllers as $name => $file) {
    if (!file_exists($file)) {
        echo "❌ {$name}: File not found\n";
        $allReady = false;
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Check if it returns a view in index method
    if (strpos($content, "view('") !== false) {
        echo "✅ {$name}: Returns a view\n";
        
        // Show which view it returns
        preg_match("/view\('([^']+)'/", $content, $matches);
        if (isset($matches[1])) {
            echo "   View: {$matches[1]}.blade.php\n";
        }
    } elseif (strpos($content, 'response()->json') !== false) {
        echo "❌ {$name}: Still returns JSON (needs update)\n";
        $allReady = false;
    } else {
        echo "⚠️  {$name}: Unknown return type\n";
        $allReady = false;
    }
}

// Check routes
echo "\n=== CHECKING ROUTES ===\n";
$routesFile = 'routes/web.php';
if (file_exists($routesFile)) {
    $routes = file_get_contents($routesFile);
    
    $routeChecks = [
        'GET /dashboard' => strpos($routes, "Route::get('/dashboard'") !== false,
        'GET /expenses' => strpos($routes, "Route::get('/expenses'") !== false,
        'GET /categories' => strpos($routes, "Route::get('/categories'") !== false,
        'GET /budgets' => strpos($routes, "Route::get('/budgets'") !== false,
        'GET /analytics' => strpos($routes, "Route::get('/analytics'") !== false,
    ];
    
    foreach ($routeChecks as $route => $exists) {
        echo ($exists ? "✅ " : "❌ ") . $route . "\n";
        if (!$exists) $allReady = false;
    }
} else {
    echo "❌ routes/web.php not found\n";
    $allReady = false;
}

// Check view directories
echo "\n=== CHECKING VIEW DIRECTORIES ===\n";
$dirs = [
    'resources/views/layouts',
    'resources/views/dashboard',
    'resources/views/expenses',
    'resources/views/categories',
    'resources/views/budgets',
    'resources/views/analytics'
];

foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "✅ {$dir}\n";
    } else {
        echo "❌ {$dir} (will create)\n";
        mkdir($dir, 0755, true);
        echo "   ✅ Created\n";
    }
}

echo "\n=== FINAL STATUS ===\n";
if ($allReady) {
    echo "🎉🎉🎉 READY FOR FRONTEND! 🎉🎉🎉\n";
    echo "\nYou can now copy all the frontend files:\n";
    echo "1. Copy layouts/app.blade.php\n";
    echo "2. Copy dashboard/index.blade.php\n";
    echo "3. Copy expenses/index.blade.php\n";
    echo "4. Copy categories/index.blade.php\n";
    echo "5. Create budgets/index.blade.php\n";
    echo "6. Create analytics/index.blade.php\n";
    
    echo "\nRun this command to start:\n";
    echo "php artisan serve\n";
    echo "Then visit: http://localhost:8000/dashboard\n";
} else {
    echo "⚠️  Some issues need fixing before frontend.\n";
    echo "\nRun these fixes:\n";
    echo "1. Update controllers that still return JSON\n";
    echo "2. Add missing routes to routes/web.php\n";
    echo "3. Clear caches: php artisan cache:clear\n";
}
