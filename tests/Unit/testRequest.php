<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreExpenseRequest;

echo "Testing Expense Validation:\n\n";

// Test data
$testData = [
    'amount' => -5,          // Invalid: negative amount
    'category_id' => 999,    // Invalid: doesn't exist
    'date' => '2050-01-01',  // Invalid: future date
    'description' => 'Test',
];

$request = new StoreExpenseRequest();
$validator = Validator::make($testData, $request->rules(), $request->messages());

if ($validator->fails()) {
    echo "✅ Validation correctly failed!\n";
    echo "Errors:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "- $error\n";
    }
} else {
    echo "❌ Validation should have failed but didn't!\n";
}