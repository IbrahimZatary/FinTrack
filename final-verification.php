<?php
echo "=== FINAL VERIFICATION ===\n\n";

// 1. Check DashboardController
$dashboardFile = 'app/Http/Controllers/DashboardController.php';
if (file_exists($dashboardFile)) {
    $content = file_get_contents($dashboardFile);
    
    if (strpos($content, "view('dashboard.index'") !== false) {
        echo "✅ DashboardController: Returns view\n";
    } elseif (strpos($content, 'response()->json') !== false) {
        echo "❌ DashboardController: Still returns JSON\n";
        
        // Show the return statement
        preg_match('/return.*?;/s', $content, $match);
        if (isset($match[0])) {
            echo "   Current return: " . substr($match[0], 0, 100) . "...\n";
        }
    } else {
        echo "⚠️  DashboardController: Unknown return\n";
    }
} else {
    echo "❌ DashboardController not found\n";
}

// 2. Check other controllers quickly
$controllers = [
    'ExpenseController',
    'CategoryController',
    'BudgetController'
];

foreach ($controllers as $controller) {
    $file = "app/Http/Controllers/{$controller}.php";
    if (file_exists($file)) {
        $content = file_get_contents($file);
        echo (strpos($content, "view('") !== false ? "✅ " : "❌ ") . "{$controller}: Returns view\n";
    }
}

// 3. Check routes
echo "\n=== CHECKING ROUTES ===\n";
$routesFile = 'routes/web.php';
if (file_exists($routesFile)) {
    $routes = file_get_contents($routesFile);
    
    $checks = [
        "Route::get('/dashboard'" => 'GET /dashboard',
        "Route::get('/expenses'" => 'GET /expenses',
        "Route::get('/categories'" => 'GET /categories',
        "Route::get('/budgets'" => 'GET /budgets',
        "Route::get('/analytics'" => 'GET /analytics',
    ];
    
    foreach ($checks as $search => $label) {
        echo (strpos($routes, $search) !== false ? "✅ " : "❌ ") . $label . "\n";
    }
    
    // Also show the route definitions
    echo "\nRoute definitions found:\n";
    $lines = explode("\n", $routes);
    foreach ($lines as $line) {
        if (strpos($line, 'Route::') !== false) {
            echo "  " . trim($line) . "\n";
        }
    }
}

// 4. Quick test - try to access dashboard
echo "\n=== QUICK TEST ===\n";
echo "To test, run:\n";
echo "php artisan serve --port=8000\n";
echo "Then visit: http://localhost:8000/dashboard\n";
echo "\nIf you see a page (even with errors), it means views are working!\n";
