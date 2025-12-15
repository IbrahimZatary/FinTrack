<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;

echo "🔍 VERIFYING THE FIX\n";
echo "===================\n\n";

// Login as test user
$user = \App\Models\User::where('email', 'test@backend.com')->first();
if (!$user) {
    die("❌ User test@backend.com not found\n");
}

Auth::login($user);
echo "✅ Logged in as: {$user->email} (ID: {$user->id})\n\n";

// Test ExpenseController
echo "1. Testing ExpenseController output:\n";
$controller = new App\Http\Controllers\ExpenseController();
$request = new Illuminate\Http\Request();

try {
    $response = $controller->index($request);
    
    if ($response instanceof \Illuminate\View\View) {
        $viewData = $response->getData();
        echo "   ✅ View returned successfully\n";
        echo "   Categories in view data: " . (isset($viewData['categories']) ? $viewData['categories']->count() : 'NOT SET') . "\n";
        
        if (isset($viewData['categories']) && $viewData['categories']->count() > 0) {
            echo "   Categories list:\n";
            foreach ($viewData['categories'] as $cat) {
                echo "     - {$cat->name}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Check view file
echo "\n2. Checking view files:\n";
$expensesView = file_get_contents('resources/views/expenses/index.blade.php');
$budgetsView = file_get_contents('resources/views/budgets/index.blade.php');

if (strpos($expensesView, '@foreach($categories') !== false) {
    echo "   ✅ Expenses view has @foreach(\$categories)\n";
} else {
    echo "   ❌ Expenses view missing @foreach(\$categories)\n";
}

if (strpos($budgetsView, '@foreach($categories') !== false) {
    echo "   ✅ Budgets view has @foreach(\$categories)\n";
} else {
    echo "   ❌ Budgets view missing @foreach(\$categories)\n";
}

echo "\n🎯 FINAL CHECK:\n";
echo "The issue was that your view files were not using the \$categories variable.\n";
echo "Now they should display: 'Food & Dining' and 'In the university- Balqaa'\n";
