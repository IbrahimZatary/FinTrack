<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = auth()->user()->categories();
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $categories = $query->orderBy('name')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        
        $category = Category::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Category created',
            'data' => $category
        ], 201);
    }
    
    public function show(Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }
    
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }
        
        $category->update($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Category updated',
            'data' => $category
        ]);
    }
    
    public function destroy(Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }
        
        if ($category->expenses()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with expenses'
            ], 422);
        }
        
        $category->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Category deleted'
        ]);
    }
}
