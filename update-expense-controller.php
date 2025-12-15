<?php
$file = 'app/Http/Controllers/ExpenseController.php';
if (!file_exists($file)) {
    echo "❌ ExpenseController not found\n";
    exit;
}

$content = file_get_contents($file);

// Find and replace the index method
$newIndexMethod = 'public function index(Request $request)
{
    $query = auth()->user()->expenses()->with(\'category\');
    
    // Apply filters if provided
    if ($request->filled(\'category_id\')) {
        $query->where(\'category_id\', $request->category_id);
    }
    
    if ($request->filled(\'start_date\') && $request->filled(\'end_date\')) {
        $query->whereBetween(\'date\', [$request->start_date, $request->end_date]);
    }
    
    if ($request->filled(\'search\')) {
        $query->where(\'description\', \'like\', \'%\' . $request->search . \'%\');
    }
    
    // Get paginated results
    $expenses = $query->latest()->paginate(20);
    
    // Return VIEW instead of JSON
    return view(\'expenses.index\', [
        \'expenses\' => $expenses,
        \'categories\' => auth()->user()->categories()->get()
    ]);
}';

// Replace the index method
$pattern = '/public function index\(Request \$request\).*?\{.*?\}/s';
if (preg_match($pattern, $content)) {
    $content = preg_replace($pattern, $newIndexMethod, $content);
    file_put_contents($file, $content);
    echo "✅ Updated ExpenseController to return view\n";
} else {
    echo "⚠️ Could not find index method pattern\n";
    
    // Try simpler pattern
    $pattern2 = '/public function index.*?\{.*?\}/s';
    if (preg_match($pattern2, $content, $match)) {
        $content = str_replace($match[0], $newIndexMethod, $content);
        file_put_contents($file, $content);
        echo "✅ Updated ExpenseController (alternative method)\n";
    }
}
