<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // the logs 
        \Log::info('BudgetController - User ID: ' . $user->id);
        \Log::info('BudgetController - Categories count: ' . $user->categories()->count());
        
        $query = $user->budgets()->with('category');
        
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
        
        // get the  categories need
        $categories = $user->categories()->get();
        
        \Log::info('BudgetController - Categories fetched: ' . $categories->count());
        foreach ($categories as $cat) {
            \Log::info('Category: ' . $cat->id . ' - ' . $cat->name);
        }
        
        return view('budgets.index', [
            'budgets' => $budgets,
            'categories' => $categories, // Passthe categories into vew
            'months' => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ],
            'current_year' => date('Y')
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100'
        ]);
        
        // Check 
        $existing = Budget::where('user_id', Auth::id())
            ->where('category_id', $request->category_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();
        
        if ($existing) {
            return back()->withErrors(['error' => 'Budget already exists for this category and period']);
        }
        
        Budget::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'month' => $request->month,
            'year' => $request->year
        ]);
        
        return redirect()->route('budgets.index')->with('success', 'Budget created successfully');
    }
    
    public function destroy(Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $budget->delete();
        
        return redirect()->route('budgets.index')->with('success', 'Budget deleted successfully');
    }
}
