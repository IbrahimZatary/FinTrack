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
        
        return view('categories.index', [
            'categories' => $categories
        ]);
    }
    
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        
        $category = Category::create($validated);
        
        return view('categories.index', [
            'categories' => $categories
        ]);
    }
    
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            return view('categories.index', [
            'categories' => $categories
        ]);
    }
    
    public function destroy(Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            return view('categories.index', [
            'categories' => $categories
        ]);
    }
}
