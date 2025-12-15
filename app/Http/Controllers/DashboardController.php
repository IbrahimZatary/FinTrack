<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
{
    $user = auth()->user();
    $now = now();
    
    // Current month calculations
    $monthlyExpenses = $user->expenses()
        ->whereYear('date', $now->year)
        ->whereMonth('date', $now->month)
        ->sum('amount');
    
    $monthlyBudget = $user->budgets()
        ->where('year', $now->year)
        ->where('month', $now->month)
        ->sum('amount');
    
    // Recent expenses
    $recentExpenses = $user->expenses()
        ->with('category')
        ->latest()
        ->limit(10)
        ->get();
    
    return response()->json([
        'monthly_spent' => $monthlyExpenses,
        'monthly_budget' => $monthlyBudget,
        'remaining' => $monthlyBudget - $monthlyExpenses,
        'recent_expenses' => $recentExpenses
    ]);
}
}
