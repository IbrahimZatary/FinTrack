<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->budgets()->with('category');
        
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $budgets = $query->orderBy('year', 'desc')
                         ->orderBy('month', 'desc')
                         ->paginate(20);
        
        return view('budgets.index', [
            'budgets' => $budgets,
            'categories' => auth()->user()->categories()->get(),
            'months' => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ],
            'current_year' => date('Y')
        ]);
    }
    
    public function store(StoreBudgetRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        
        $budget = Budget::create($validated);
        
        return view('budgets.index', [
            'budgets' => $budgets,
            'categories' => auth()->user()->categories()->get(),
            'months' => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ],
            'current_year' => date('Y')
        ]);
    }
    
    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        if ($budget->user_id !== auth()->id()) {
            return view('budgets.index', [
            'budgets' => $budgets,
            'categories' => auth()->user()->categories()->get(),
            'months' => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ],
            'current_year' => date('Y')
        ]);
    }
    
    public function destroy(Budget $budget)
    {
        if ($budget->user_id !== auth()->id()) {
            return view('budgets.index', [
            'budgets' => $budgets,
            'categories' => auth()->user()->categories()->get(),
            'months' => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ],
            'current_year' => date('Y')
        ]);
    }
}
