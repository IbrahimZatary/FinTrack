<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BudgetController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect to dashboard
Route::get('/', function () {
    return redirect('/dashboard');
});

// Authentication routes (Laravel Breeze/Jetstream)
require __DIR__.'/auth.php';

// Protected routes (require login)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    
    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    // BUDGETS ROUTES - ADDED HERE
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::get('/budgets/{budget}', [BudgetController::class, 'show'])->name('budgets.show');
    Route::put('/budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');
    
    // Analytics (if you have it)
    Route::get('/analytics', function() {
        return view('analytics.index');
    })->name('analytics');
});

// Debug route
Route::get('/budgets/debug', function() {
    $user = Auth::user();
    return view('budgets.debug', [
        'categories' => $user->categories()->get()
    ]);
});

// Dashboard API
Route::get('/dashboard/data', [DashboardController::class, 'apiData'])->name('dashboard.data');
Route::get('/test-categories', function() {
    $user = \App\Models\User::where('email', 'test@backend.com')->first();
    if ($user) {
        Auth::login($user);
        $categories = $user->categories()->get();
        return view('expenses.test-categories', ['categories' => $categories]);
    }
    return 'No user found';
});
