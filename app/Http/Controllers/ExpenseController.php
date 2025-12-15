<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->expenses()->with('category');
        
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $expenses = $query->orderBy('date', 'desc')->paginate(20);
        
        $categories = $user->categories()->orderBy('name')->get();
        
        return view('expenses.index', [
            'expenses' => $expenses,
            'categories' => $categories
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255'
        ]);
        
        $expense = Expense::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'category_id' => $request->category_id,
            'date' => $request->date,
            'description' => $request->description
        ]);
        
        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense added successfully!',
                'expense' => $expense->load('category')
            ]);
        }
        
        return redirect()->route('expenses.index')->with('success', 'Expense added successfully!');
    }
    
    public function destroy(Expense $expense)
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $expense->delete();
        
        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully!');
    }
}
