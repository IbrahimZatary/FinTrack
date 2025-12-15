<?php
require __DIR__.'/vendor/autoload.php';

echo "=== CHECKING IF CONTROLLERS ARE READY FOR FRONTEND ===\n\n";

$controllers = [
    'DashboardController' => 'index',
    'ExpenseController' => 'index',
    'CategoryController' => 'index',
    'BudgetController' => 'index',
];

foreach ($controllers as $controller => $method) {
    $file = "app/Http/Controllers/{$controller}.php";
    
    if (!file_exists($file)) {
        echo "❌ {$controller} NOT FOUND!\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Check if method exists
    if (strpos($content, "public function {$method}(") === false) {
        echo "❌ {$controller}->{$method}() method NOT FOUND!\n";
        continue;
    }
    
    // Check what it returns
    echo "✅ {$controller}->{$method}() exists\n";
    
    // Extract the method content
    preg_match("/public function {$method}\(.*?\)\s*\{([^}]+(?:\{[^{}]*\}[^{}]*)*)\}/s", $content, $matches);
    
    if (isset($matches[1])) {
        $methodContent = $matches[1];
        
        if (strpos($methodContent, 'return view(') !== false) {
            echo "   📄 Returns a VIEW (Good for frontend!)\n";
        } elseif (strpos($methodContent, 'response()->json') !== false) {
            echo "   📊 Returns JSON (Needs update to return view)\n";
            
            // Show what JSON it returns
            preg_match('/response\(\)->json\((.*?)\)/s', $methodContent, $jsonMatch);
            if (isset($jsonMatch[1])) {
                echo "   JSON data: " . substr($jsonMatch[1], 0, 100) . "...\n";
            }
        } else {
            echo "   ⚠️  Unknown return type\n";
        }
    }
    
    echo "\n";
}

// Check routes
echo "=== CHECKING ROUTES ===\n";
$webRoutes = file_get_contents('routes/web.php');

$routeChecks = [
    'GET /dashboard' => strpos($webRoutes, "Route::get('/dashboard'") !== false,
    'GET /expenses' => strpos($webRoutes, "Route::get('/expenses'") !== false,
    'GET /categories' => strpos($webRoutes, "Route::get('/categories'") !== false,
    'GET /budgets' => strpos($webRoutes, "Route::get('/budgets'") !== false,
    'GET /analytics' => strpos($webRoutes, "Route::get('/analytics'") !== false,
];

foreach ($routeChecks as $route => $exists) {
    echo ($exists ? "✅ " : "❌ ") . $route . "\n";
}

// Check for view files
echo "\n=== CHECKING VIEW FILES ===\n";
$viewFiles = [
    'layouts/app.blade.php',
    'dashboard/index.blade.php',
    'expenses/index.blade.php',
    'categories/index.blade.php',
    'budgets/index.blade.php',
    'analytics/index.blade.php'
];

foreach ($viewFiles as $view) {
    $path = "resources/views/{$view}";
    echo (file_exists($path) ? "✅ " : "❌ ") . $view . "\n";
}
