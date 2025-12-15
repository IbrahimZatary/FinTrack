<?php
$file = 'routes/web.php';
if (!file_exists($file)) {
    echo "❌ routes/web.php not found\n";
    exit;
}

$content = file_get_contents($file);

// Define the routes we need to add
$newRoutes = <<<'ROUTES'

// Expense Tracker Application Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Expenses
    Route::get('/expenses', [App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [App\Http\Controllers\ExpenseController::class, 'store']);
    Route::get('/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'show']);
    Route::post('/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'update']);
    Route::delete('/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'destroy']);
    
    // Categories
    Route::get('/categories', [App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [App\Http\Controllers\CategoryController::class, 'store']);
    Route::get('/categories/{category}', [App\Http\Controllers\CategoryController::class, 'show']);
    Route::post('/categories/{category}', [App\Http\Controllers\CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [App\Http\Controllers\CategoryController::class, 'destroy']);
    
    // Budgets
    Route::get('/budgets', [App\Http\Controllers\BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/budgets', [App\Http\Controllers\BudgetController::class, 'store']);
    Route::get('/budgets/{budget}', [App\Http\Controllers\BudgetController::class, 'show']);
    Route::post('/budgets/{budget}', [App\Http\Controllers\BudgetController::class, 'update']);
    Route::delete('/budgets/{budget}', [App\Http\Controllers\BudgetController::class, 'destroy']);
    
    // Analytics
    Route::get('/analytics', function() {
        return view('analytics.index');
    });
});
ROUTES;

// Check if routes already exist
if (strpos($content, "Route::get('/expenses'") === false) {
    // Find where to add the routes (before require auth.php)
    if (strpos($content, "require __DIR__.'/auth.php'") !== false) {
        $content = str_replace(
            "require __DIR__.'/auth.php';",
            $newRoutes . "\n\nrequire __DIR__.'/auth.php';",
            $content
        );
        echo "✅ Added routes before auth.php\n";
    } else {
        // Just append at the end
        $content .= "\n" . $newRoutes;
        echo "✅ Appended routes to end of file\n";
    }
    
    file_put_contents($file, $content);
    echo "✅ All routes added successfully!\n";
} else {
    echo "⚠️ Routes already exist in the file\n";
    
    // Check each route individually
    $missingRoutes = [];
    if (strpos($content, "Route::get('/expenses'") === false) $missingRoutes[] = '/expenses';
    if (strpos($content, "Route::get('/categories'") === false) $missingRoutes[] = '/categories';
    if (strpos($content, "Route::get('/budgets'") === false) $missingRoutes[] = '/budgets';
    if (strpos($content, "Route::get('/analytics'") === false) $missingRoutes[] = '/analytics';
    
    if (!empty($missingRoutes)) {
        echo "Missing routes: " . implode(', ', $missingRoutes) . "\n";
        
        // Add missing routes
        $content .= "\n" . $newRoutes;
        file_put_contents($file, $content);
        echo "✅ Added missing routes\n";
    }
}

// Show the added routes
echo "\nAdded these routes:\n";
echo "GET /dashboard\n";
echo "GET /expenses\n";
echo "GET /categories\n";
echo "GET /budgets\n";
echo "GET /analytics\n";
