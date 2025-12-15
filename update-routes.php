<?php
$file = 'routes/web.php';
if (!file_exists($file)) {
    echo "❌ routes/web.php not found\n";
    exit;
}

$content = file_get_contents($file);

// Add the missing routes
$newRoutes = '

// Expense Tracker Routes
Route::middleware([\'auth\'])->group(function () {
    // Dashboard
    Route::get(\'/dashboard\', [App\\Http\\Controllers\\DashboardController::class, \'index\'])->name(\'dashboard\');
    
    // Expenses
    Route::get(\'/expenses\', [App\\Http\\Controllers\\ExpenseController::class, \'index\'])->name(\'expenses.index\');
    Route::post(\'/expenses\', [App\\Http\\Controllers\\ExpenseController::class, \'store\']);
    Route::get(\'/expenses/{expense}\', [App\\Http\\Controllers\\ExpenseController::class, \'show\']);
    Route::post(\'/expenses/{expense}\', [App\\Http\\Controllers\\ExpenseController::class, \'update\']);
    Route::delete(\'/expenses/{expense}\', [App\\Http\\Controllers\\ExpenseController::class, \'destroy\']);
    
    // Categories
    Route::get(\'/categories\', [App\\Http\\Controllers\\CategoryController::class, \'index\'])->name(\'categories.index\');
    Route::post(\'/categories\', [App\\Http\\Controllers\\CategoryController::class, \'store\']);
    Route::get(\'/categories/{category}\', [App\\Http\\Controllers\\CategoryController::class, \'show\']);
    Route::post(\'/categories/{category}\', [App\\Http\\Controllers\\CategoryController::class, \'update\']);
    Route::delete(\'/categories/{category}\', [App\\Http\\Controllers\\CategoryController::class, \'destroy\']);
    
    // Budgets
    Route::get(\'/budgets\', [App\\Http\\Controllers\\BudgetController::class, \'index\'])->name(\'budgets.index\');
    Route::post(\'/budgets\', [App\\Http\\Controllers\\BudgetController::class, \'store\']);
    Route::get(\'/budgets/{budget}\', [App\\Http\\Controllers\\BudgetController::class, \'show\']);
    Route::post(\'/budgets/{budget}\', [App\\Http\\Controllers\\BudgetController::class, \'update\']);
    Route::delete(\'/budgets/{budget}\', [App\\Http\\Controllers\\BudgetController::class, \'destroy\']);
    
    // Analytics
    Route::get(\'/analytics\', function() {
        return view(\'analytics.index\');
    });
});
';

// Check if routes already exist
if (strpos($content, 'Route::get(\'/expenses\'') === false) {
    // Add before the auth.php require
    if (strpos($content, "require __DIR__.'/auth.php'") !== false) {
        $content = str_replace(
            "require __DIR__.'/auth.php';",
            $newRoutes . "\nrequire __DIR__.'/auth.php';",
            $content
        );
        file_put_contents($file, $content);
        echo "✅ Added all routes to routes/web.php\n";
    } else {
        // Just append at the end
        file_put_contents($file, $content . $newRoutes);
        echo "✅ Appended routes to routes/web.php\n";
    }
} else {
    echo "⚠️ Routes already exist\n";
}
