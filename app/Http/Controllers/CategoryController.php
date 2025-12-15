<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Auth::user()->categories()->withCount('expenses')->get();
        
        return view('categories.index', [
            'categories' => $categories
        ]);
    }
    
    public function store(Request $request)
    {
        Log::info('Category store called', ['request' => $request->all()]);
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:50|unique:categories,name,NULL,id,user_id,' . Auth::id(),
                'color' => 'required|string|max:7'
            ]);
            
            Log::info('Validation passed', ['validated' => $validated]);
            
            $category = Category::create([
                'user_id' => Auth::id(),
                'name' => $validated['name'],
                'color' => $validated['color']
            ]);
            
            Log::info('Category created', ['category' => $category]);
            
            return redirect()->route('categories.index')
                ->with('success', 'Category created successfully!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            Log::error('Category creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Error adding category: ' . $e->getMessage()])
                        ->withInput();
        }
    }
    
    public function destroy(Category $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $category->delete();
        
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }
}
