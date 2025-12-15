<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    //
    public function index() {}    // GET /expenses
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
public function update() {}   // PUT /expenses/{id}
public function destroy() {}  // DELETE /expenses/{id}
}
