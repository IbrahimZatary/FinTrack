<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $user = Auth::user();
        
        // determine the date
        $dateRange = $this->getDateRange($period);



        
        
        // get data for theanalytics
        $data = [
            'total_spent' => $this->getTotalSpent($user, $dateRange),
            'total_categories' => $user->categories()->count(),
            'monthly_average' => $this->getMonthlyAverage($user),
            'total_expenses' => $user->expenses()->count(),
            'monthly_trend' => $this->getMonthlyTrend($user),
            'top_categories' => $this->getTopCategories($user, $dateRange),
            'daily_pattern' => $this->getDailyPattern($user, $dateRange),
            'budget_vs_actual' => $this->getBudgetVsActual($user, $dateRange)
        ];
        
        return view('analytics.index', $data);
    }
    
    public function apiData(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $user = Auth::user();
        
        $dateRange = $this->getDateRange($period);
        
        $data = [
            'totalSpent' => $this->getTotalSpent($user, $dateRange),
            'totalCategories' => $user->categories()->count(),
            'monthlyAverage' => $this->getMonthlyAverage($user),
            'totalExpenses' => $user->expenses()->count(),
            'monthlyTrend' => $this->getMonthlyTrend($user),
            'topCategories' => $this->getTopCategories($user, $dateRange),
            'dailyPattern' => $this->getDailyPattern($user, $dateRange),
            'budgetVsActual' => $this->getBudgetVsActual($user, $dateRange)
        ];
        
        return response()->json($data);
    }
    
    private function getDateRange($period)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'last_month':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth()
                ];
            case 'quarterly':
                return [
                    'start' => $now->copy()->startOfQuarter(),
                    'end' => $now->copy()->endOfQuarter()
                ];
            case 'yearly':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear()
                ];
            case 'all':
                return [
                    'start' => Carbon::create(2000, 1, 1), 
                    'end' => Carbon::now()->addYears(10) 
                ];
            default: // monthly
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
        }
    }
    
    private function getTotalSpent($user, $dateRange)
    {
        return $user->expenses()
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->sum('amount');
    }
    
    private function getMonthlyAverage($user)
    {
        $firstExpense = $user->expenses()->orderBy('date')->first();
        if (!$firstExpense) return 0;
        
        $startDate = Carbon::parse($firstExpense->date);
        $monthsDiff = $startDate->diffInMonths(Carbon::now()) + 1;
        
        $totalSpent = $user->expenses()->sum('amount');
        
        return $monthsDiff > 0 ? $totalSpent / $monthsDiff : 0;
    }
    
    private function getMonthlyTrend($user)
    {
        $months = [];
        $amounts = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            
            $amount = $user->expenses()
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');
            
            $amounts[] = $amount;
        }
        
        return [
            'labels' => $months,
            'amounts' => $amounts
        ];
    }
    
    private function getTopCategories($user, $dateRange)
    {
        $categories = $user->categories()->with(['expenses' => function($query) use ($dateRange) {
            $query->whereBetween('date', [$dateRange['start'], $dateRange['end']]);
        }])->get();
        
        $data = $categories->map(function($category) {
            return [
                'name' => $category->name,
                'amount' => $category->expenses->sum('amount'),
                'color' => $category->color
            ];
        })->sortByDesc('amount')->take(6);
        
        return [
            'labels' => $data->pluck('name')->toArray(),
            'amounts' => $data->pluck('amount')->toArray(),
            'colors' => $data->pluck('color')->toArray()
        ];
    }
    
    private function getDailyPattern($user, $dateRange)
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $pattern = array_fill(0, 7, 0);
        $count = array_fill(0, 7, 0);
        
        $expenses = $user->expenses()
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->get();
        // convert 
        foreach ($expenses as $expense) {
            $dayOfWeek = Carbon::parse($expense->date)->dayOfWeek;
            $dayIndex = $dayOfWeek == 0 ? 6 : $dayOfWeek - 1; 
            
            $pattern[$dayIndex] += $expense->amount;
            $count[$dayIndex]++;
        }
        
        //  here just calculate averages
        $averages = [];
        foreach ($pattern as $index => $total) {
            $averages[$index] = $count[$index] > 0 ? $total / $count[$index] : 0;
        }
        
        return [
            'labels' => $dayNames,
            'amounts' => $averages
        ];
    }
    
    private function getBudgetVsActual($user, $dateRange)
    {
        $budgets = $user->budgets()
            ->where('year', $dateRange['start']->year)
            ->where('month', $dateRange['start']->month)
            ->with('category')
            ->get()
            ->take(4);
        
        return [
            'labels' => $budgets->pluck('category.name')->toArray(),
            'budgets' => $budgets->pluck('amount')->toArray(),
            'actuals' => $budgets->map(function($budget) use ($dateRange, $user) {
                return $user->expenses()
                    ->where('category_id', $budget->category_id)
                    ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
                    ->sum('amount');
            })->toArray()
        ];
    }
}
