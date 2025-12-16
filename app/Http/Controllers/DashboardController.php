<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // get month and year from request or use current
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        // simply return the view
        return view('dashboard.index', [
            'period' => [
                'month' => $month,
                'year' => $year
            ]
        ]);
    }
    
  
    public function apiData(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $currentDate = Carbon::create($year, $month, 1);
        
        //  current selected month expenses
        $monthlyExpenses = $user->expenses()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');
        
        // current selected month budget
        $monthlyBudget = $user->budgets()
            ->where('year', $year)
            ->where('month', $month)
            ->sum('amount');


        
        //        recent expenses (last 10, 
        $recentExpenses = $user->expenses()
            ->with('category')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($expense) {
                $category = $expense->category;
                return [
                    'id' => $expense->id,
                    'amount' => $expense->amount,
                    'date' => $expense->date->format('Y-m-d'),
                    'description' => $expense->description,
                    'category' => $category ? $category->name : 'Uncategorized',
                    'color' => $category ? $category->color : '#CCCCCC',
                ];
            });
        
        //  Category wise spending for  month
        $categorySpending = $user->expenses()
            ->selectRaw('category_id, SUM(amount) as total')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($item) {
                $category = $item->category;
                return [
                    'category' => $category ? $category->name : 'Uncategorized',
                    'amount' => $item->total,
                    'color' => $category ? $category->color : '#CCCCCC'
                ];
            });
        
        //  budget  Actual for selected month
        $budgetStatus = $user->budgets()
            ->where('year', $year)
            ->where('month', $month)
            ->with('category')
            ->get()
            ->map(function ($budget) use ($user, $year, $month) {
                $category = $budget->category;
                $spent = $user->expenses()
                    ->where('category_id', $budget->category_id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->sum('amount');
                
                $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;
                
                return [
                    'category' => $category ? $category->name : 'Uncategorized',
                    'budget' => $budget->amount,
                    'spent' => $spent,
                    'remaining' => $budget->amount - $spent,
                    'percentage' => round($percentage, 1),
                    'status' => $spent > $budget->amount ? 'over' : ($percentage > 80 ? 'warning' : 'good')
                ];
            });
        
        //  daily avg calculations
        $daysInMonth = $currentDate->daysInMonth;
        $currentDay = ($year == Carbon::now()->year && $month == Carbon::now()->month) 
            ? Carbon::now()->day 
            : $daysInMonth;
        
        $dailyAverage = $currentDay > 0 ? $monthlyExpenses / $currentDay : 0;
        $projectedMonthly = $dailyAverage * $daysInMonth;
        
        //  compare    previous month
        $previousMonth = $currentDate->copy()->subMonth();
        $previousMonthExpenses = $user->expenses()
            ->whereYear('date', $previousMonth->year)
            ->whereMonth('date', $previousMonth->month)
            ->sum('amount');
        
        $monthOverMonth = $previousMonthExpenses > 0 
            ? (($monthlyExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100 
            : 0;
        
        return response()->json([
            'selected_period' => $currentDate->format('F Y'),
            'monthly_spent' => round($monthlyExpenses, 2),
            'monthly_budget' => round($monthlyBudget, 2),
            'remaining' => round($monthlyBudget - $monthlyExpenses, 2),
            'budget_utilization' => $monthlyBudget > 0 ? round(($monthlyExpenses / $monthlyBudget) * 100, 1) : 0,
            'daily_average' => round($dailyAverage, 2),
            'projected_monthly' => round($projectedMonthly, 2),
            'month_over_month' => round($monthOverMonth, 1),
            'recent_expenses' => $recentExpenses,
            'category_spending' => $categorySpending,
            'budget_status' => $budgetStatus,
            'summary' => [
                'total_categories' => $user->categories()->count(),
                'total_expenses' => $user->expenses()->count(),
                'total_budgets' => $user->budgets()->count(),
                'current_day' => $currentDay,
                'days_in_month' => $daysInMonth,
                'days_remaining' => $daysInMonth - $currentDay
            ]
        ]);
    }
}
