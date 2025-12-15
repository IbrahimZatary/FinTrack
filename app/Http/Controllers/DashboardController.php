<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Allow year/month selection via request, default to current
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);
        
        $currentDate = Carbon::create($year, $month, 1);
        
        // 1. Current selected month expenses
        $monthlyExpenses = $user->expenses()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');
        
        // 2. Current selected month budget
        $monthlyBudget = $user->budgets()
            ->where('year', $year)
            ->where('month', $month)
            ->sum('amount');
        
        // 3. Recent expenses (last 10, all time)
        $recentExpenses = $user->expenses()
            ->with('category')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($expense) {
                $categoryName = $expense->category ? $expense->category->name : 'Uncategorized';
                $categoryColor = $expense->category ? $expense->category->color : '#CCCCCC';
                
                return [
                    'id' => $expense->id,
                    'amount' => $expense->amount,
                    'date' => $expense->date,
                    'description' => $expense->description,
                    'category' => $categoryName,
                    'color' => $categoryColor,
                    'created_at' => $expense->created_at->format('Y-m-d H:i:s'),
                ];
            });
        
        // 4. Category-wise spending for selected month
        $categorySpending = $user->expenses()
            ->selectRaw('category_id, SUM(amount) as total')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($item) {
                $categoryName = $item->category ? $item->category->name : 'Uncategorized';
                $categoryColor = $item->category ? $item->category->color : '#CCCCCC';
                
                return [
                    'category' => $categoryName,
                    'amount' => $item->total,
                    'color' => $categoryColor
                ];
            });
        
        // 5. Budget vs Actual for selected month
        $budgetStatus = $user->budgets()
            ->where('year', $year)
            ->where('month', $month)
            ->with('category')
            ->get()
            ->map(function ($budget) use ($user, $year, $month) {
                $categoryName = $budget->category ? $budget->category->name : 'Uncategorized';
                
                $spent = $user->expenses()
                    ->where('category_id', $budget->category_id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->sum('amount');
                
                $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;
                
                return [
                    'category' => $categoryName,
                    'budget' => $budget->amount,
                    'spent' => $spent,
                    'remaining' => $budget->amount - $spent,
                    'percentage' => round($percentage, 1),
                    'status' => $spent > $budget->amount ? 'over' : ($percentage > 80 ? 'warning' : 'good')
                ];
            });
        
        // 6. Daily average calculations
        $daysInMonth = $currentDate->daysInMonth;
        $currentDay = ($year == Carbon::now()->year && $month == Carbon::now()->month) 
            ? Carbon::now()->day 
            : $daysInMonth;
        
        $dailyAverage = $currentDay > 0 ? $monthlyExpenses / $currentDay : 0;
        $projectedMonthly = $dailyAverage * $daysInMonth;
        
        // 7. Compare with previous month
        $previousMonth = $currentDate->copy()->subMonth();
        $previousMonthExpenses = $user->expenses()
            ->whereYear('date', $previousMonth->year)
            ->whereMonth('date', $previousMonth->month)
            ->sum('amount');
        
        $monthOverMonth = $previousMonthExpenses > 0 
            ? (($monthlyExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100 
            : 0;
        
        // 8. Overall totals (all time)
        $totalExpenses = $user->expenses()->sum('amount');
        $averageExpense = $user->expenses()->count() > 0 ? $user->expenses()->avg('amount') : 0;
        $largestExpense = $user->expenses()->count() > 0 ? $user->expenses()->max('amount') : 0;
        
        $overallTotals = [
            'total_expenses' => $totalExpenses,
            'average_expense' => $averageExpense,
            'largest_expense' => $largestExpense,
        ];
        
        return view('dashboard.index', [
        'period' => [
            'month' => $month,
            'year' => $year
        ]
    ]);
    }
    
    /**
     * Get dashboard summary for the current user
     */
    public function summary()
    {
        $user = Auth::user();
        
        // Get last expense with null check
        $lastExpense = $user->expenses()->latest()->first();
        $lastExpenseText = $lastExpense ? $lastExpense->created_at->diffForHumans() : 'No expenses';
        
        // Get last category with null check
        $lastCategory = $user->categories()->latest()->first();
        $lastCategoryText = $lastCategory ? $lastCategory->created_at->diffForHumans() : 'No categories';
        
        // Get last budget with null check
        $lastBudget = $user->budgets()->latest()->first();
        $lastBudgetText = $lastBudget ? $lastBudget->created_at->diffForHumans() : 'No budgets';
        
        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'member_since' => $user->created_at->format('M Y')
            ],
            'counts' => [
                'categories' => $user->categories()->count(),
                'expenses' => $user->expenses()->count(),
                'budgets' => $user->budgets()->count()
            ],
            'recent_activity' => [
                'last_expense' => $lastExpenseText,
                'last_category' => $lastCategoryText,
                'last_budget' => $lastBudgetText
            ]
        ]);
    }
}