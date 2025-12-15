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
        
        return response()->json([
            'success' => true,
            'data' => $budgets,
            'categories' => auth()->user()->categories()->get()
        ]);
    }
    
    public function store(StoreBudgetRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        
        $budget = Budget::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Budget created',
            'data' => $budget->load('category')
        ], 201);
    }
    
    public function show(Budget $budget)
    {
        if ($budget->user_id !== auth()->id()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $budget->load('category')
        ]);
    }
    
    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        if ($budget->user_id !== auth()->id()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }
        
        $budget->update($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Budget updated',
            'data' => $budget->load('category')
        ]);
    }
    
    public function destroy(Budget $budget)
    {
        if ($budget->user_id !== auth()->id()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }
        
        $budget->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Budget deleted'
        ]);
    }
}
