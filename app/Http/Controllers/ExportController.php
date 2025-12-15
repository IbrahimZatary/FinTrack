
<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\ExpensesExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Carbon\Carbon;

class ExportController extends Controller
{
    
    public function exportExpensesPDF(Request $request)
    {
        try {
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
            
            $expenses = $query->orderBy('date', 'desc')->get();
            $totalAmount = $expenses->sum('amount');
            
        
            $categoryName = 'All Categories';
            if ($request->filled('category_id')) {
                $category = Category::find($request->category_id);
                $categoryName = $category ? $category->name : 'Selected Category';
            }
            
            $data = [
                'expenses' => $expenses,
                'totalAmount' => $totalAmount,
                'filters' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'category' => $categoryName
                ],
                'user' => $user,
                'exportDate' => now()->format('Y-m-d H:i:s'),
                'title' => 'Expenses Report'
            ];
            
            // generate PDF
            $pdf = PDF::loadView('exports.expenses-pdf', $data)
                    ->setPaper('a4', 'landscape')
                    ->setOption('defaultFont', 'Arial');
            
            return $pdf->download('expenses-report-' . date('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            return back()->with('error', 'PDF export failed: ' . $e->getMessage());
        }
    }
    
    
    public function exportExpensesExcel(Request $request)
    {
        try {
            return Excel::download(new ExpensesExport($request), 
                'expenses-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Excel export failed: ' . $e->getMessage());
        }
    }
    
    
    public function exportExpensesCSV(Request $request)
    {
        try {
            return Excel::download(new ExpensesExport($request), 
                'expenses-' . date('Y-m-d') . '.csv', 
                \Maatwebsite\Excel\Excel::CSV, [
                    'Content-Type' => 'text/csv',
                ]);
        } catch (\Exception $e) {
            return back()->with('error', 'CSV export failed: ' . $e->getMessage());
        }
    }
    
   
    public function exportBudgetsPDF(Request $request)
    {
        try {
            $user = Auth::user();
            
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
                             ->get();
            
            $totalBudget = $budgets->sum('amount');
            
            $months = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ];
            
            $data = [
                'budgets' => $budgets,
                'totalBudget' => $totalBudget,
                'months' => $months,
                'user' => $user,
                'exportDate' => now()->format('Y-m-d H:i:s'),
                'title' => 'Budgets Report',
                'filters' => [
                    'month' => $request->month ? $months[$request->month] ?? $request->month : 'All',
                    'year' => $request->year ?: 'All'
                ]
            ];
            
            $pdf = PDF::loadView('exports.budgets-pdf', $data)
                     ->setPaper('a4', 'portrait')
                     ->setOption('defaultFont', 'Arial');
            
            return $pdf->download('budgets-report-' . date('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            return back()->with('error', 'PDF export failed: ' . $e->getMessage());
        }
    }
    
    
    public function exportAll(Request $request)
    {
        try {
            $user = Auth::user();
            
            $data = [
                'expenses' => $user->expenses()->with('category')->get(),
                'budgets' => $user->budgets()->with('category')->get(),
                'categories' => $user->categories()->get(),
                'user' => $user,
                'exportDate' => now()->format('Y-m-d H:i:s')
            ];
            
            $pdf = PDF::loadView('exports.all-data-pdf', $data)
                     ->setPaper('a4', 'landscape');
            
            return $pdf->download('all-data-' . date('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
}
EOF