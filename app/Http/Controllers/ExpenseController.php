<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    //
    public function index(Request $request)
{
    // Start with current user's expenses
    $query = auth()->user()->expenses()->with('category');
    
    // Apply filters if provided
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }
    
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('date', [$request->start_date, $request->end_date]);
    }
    
    if ($request->filled('search')) {
        $query->where('description', 'like', '%' . $request->search . '%');
    }
    
    // Get paginated results
    $expenses = $query->latest()->paginate(20);
    
    // Return JSON for now (we'll add views later)
    return response()->json([
        'expenses' => $expenses,
        'total' => $expenses->total(),
        'categories' => auth()->user()->categories()->get()
    ]);
}    // GET /expenses
public function create() {}   // GET /expenses/create  
public function store(StoreExpenseRequest $request)
{
    // Request is automatically validated here!
    // If validation fails, user gets redirected back with errors
    
    // Get validated data
    $validated = $request->validated();
    
    // Add user_id to the data
    $validated['user_id'] = auth()->id();
    
    // Create expense
    $expense = Expense::create($validated);
    
    // Return success response
    return response()->json([
        'success' => true,
        'message' => 'Expense created successfully',
        'data' => $expense
    ]);
}
public function edit() {}     // GET /expenses/{id}/edit
public function update(UpdateExpenseRequest $request, Expense $expense)
{
    // Check if user owns this expense
    if ($expense->user_id !== auth()->id()) {
        return response()->json(['error' => 'Not authorized'], 403);
    }
    
    // Update with validated data
    $expense->update($request->validated());
    
    return response()->json([
        'success' => true,
        'message' => 'Expense updated',
        'data' => $expense->load('category')
    ]);
}
public function destroy(Expense $expense)
{
    if ($expense->user_id !== auth()->id()) {
        return response()->json(['error' => 'Not authorized'], 403);
    }
    
    $expense->delete(); // Soft delete
    
    return response()->json([
        'success' => true,
        'message' => 'Expense deleted'
    ]);
}
}
