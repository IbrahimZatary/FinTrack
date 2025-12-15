<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Validator;
use App\Models\User;

echo "=== EXPENSE VALIDATION TESTS ===\n\n";

$user = User::where('email', 'test@backend.com')->first();
if (!$user) {
    echo "❌ Test user not found\n";
    exit;
}

$category = $user->categories()->first();

$testCases = [
    [
        'name' => 'Valid expense',
        'data' => [
            'amount' => 100.50,
            'category_id' => $category->id,
            'date' => '2024-12-15',
            'description' => 'Valid test'
        ],
        'should_pass' => true
    ],
    [
        'name' => 'Negative amount',
        'data' => [
            'amount' => -50,
            'category_id' => $category->id,
            'date' => '2024-12-15'
        ],
        'should_pass' => false
    ],
    [
        'name' => 'Zero amount',
        'data' => [
            'amount' => 0,
            'category_id' => $category->id,
            'date' => '2024-12-15'
        ],
        'should_pass' => false
    ],
    [
        'name' => 'Future date',
        'data' => [
            'amount' => 100,
            'category_id' => $category->id,
            'date' => '2025-12-15'
        ],
        'should_pass' => false
    ],
    [
        'name' => 'Invalid category',
        'data' => [
            'amount' => 100,
            'category_id' => 99999,
            'date' => '2024-12-15'
        ],
        'should_pass' => false
    ],
    [
        'name' => 'Missing amount',
        'data' => [
            'category_id' => $category->id,
            'date' => '2024-12-15'
        ],
        'should_pass' => false
    ]
];

$rules = [
    'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
    'category_id' => ['required', 'exists:categories,id'],
    'date' => ['required', 'date', 'before_or_equal:today'],
    'description' => ['nullable', 'string', 'max:500'],
];

foreach ($testCases as $test) {
    echo "Test: {$test['name']}\n";
    echo "Data: " . json_encode($test['data']) . "\n";
    
    $validator = Validator::make($test['data'], $rules);
    
    if ($validator->fails()) {
        if ($test['should_pass']) {
            echo "❌ FAILED (should have passed)\n";
            foreach ($validator->errors()->all() as $error) {
                echo "   - $error\n";
            }
        } else {
            echo "✅ PASSED (correctly failed validation)\n";
        }
    } else {
        if ($test['should_pass']) {
            echo "✅ PASSED (validation successful)\n";
        } else {
            echo "❌ FAILED (should have failed validation)\n";
        }
    }
    echo "\n";
}

echo "=== CATEGORY VALIDATION TESTS ===\n\n";

$categoryRules = [
    'name' => ['required', 'string', 'max:100', 'unique:categories,name,NULL,id,user_id,' . $user->id],
    'color' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
];

$categoryTests = [
    [
        'name' => 'Valid category',
        'data' => ['name' => 'Transportation', 'color' => '#3366FF'],
        'should_pass' => true
    ],
    [
        'name' => 'Duplicate name',
        'data' => ['name' => 'Food & Dining', 'color' => '#FF5733'],
        'should_pass' => false
    ],
    [
        'name' => 'Invalid color format',
        'data' => ['name' => 'Test', 'color' => 'red'],
        'should_pass' => false
    ],
    [
        'name' => 'Missing color',
        'data' => ['name' => 'Test'],
        'should_pass' => false
    ]
];

foreach ($categoryTests as $test) {
    echo "Test: {$test['name']}\n";
    
    $validator = Validator::make($test['data'], $categoryRules);
    
    if ($validator->fails()) {
        if ($test['should_pass']) {
            echo "❌ FAILED (should have passed)\n";
        } else {
            echo "✅ PASSED (correctly failed)\n";
        }
    } else {
        if ($test['should_pass']) {
            echo "✅ PASSED\n";
        } else {
            echo "❌ FAILED (should have failed)\n";
        }
    }
    echo "\n";
}
