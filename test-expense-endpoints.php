<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Category;

echo "=== EXPENSE ENDPOINT TESTS ===\n\n";

$user = User::where('email', 'test@backend.com')->first();
$category = $user->categories()->first();

// 1. GET /expenses
echo "1. GET /expenses (List expenses)\n";
$ch = curl_init('http://localhost:8001/expenses');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status: $httpCode\n";
if ($httpCode == 200) {
    $data = json_decode($response, true);
    echo "   ✅ Success\n";
    echo "   Found " . count($data['expenses']['data'] ?? []) . " expenses\n";
} else {
    echo "   ❌ Failed\n";
    echo "   Response: $response\n";
}

// 2. POST /expenses (Create new)
echo "\n2. POST /expenses (Create new expense)\n";
$expenseData = [
    'amount' => 150.75,
    'category_id' => $category->id,
    'date' => '2024-12-16',
    'description' => 'Test expense via API'
];

$ch = curl_init('http://localhost:8001/expenses');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $expenseData);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status: $httpCode\n";
if ($httpCode == 200 || $httpCode == 201) {
    $data = json_decode($response, true);
    echo "   ✅ Success\n";
    echo "   Created expense ID: " . ($data['data']['id'] ?? 'unknown') . "\n";
    $newExpenseId = $data['data']['id'] ?? null;
} else {
    echo "   ❌ Failed\n";
    echo "   Response: $response\n";
}

// 3. GET /expenses/{id} (Show specific)
if (isset($newExpenseId)) {
    echo "\n3. GET /expenses/{$newExpenseId} (Show expense)\n";
    $ch = curl_init("http://localhost:8001/expenses/{$newExpenseId}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Status: $httpCode\n";
    echo ($httpCode == 200 ? "   ✅ Success\n" : "   ❌ Failed\n");
}

// 4. PUT /expenses/{id} (Update)
if (isset($newExpenseId)) {
    echo "\n4. PUT /expenses/{$newExpenseId} (Update expense)\n";
    $updateData = [
        'amount' => 175.50,
        'description' => 'Updated description'
    ];
    
    $ch = curl_init("http://localhost:8001/expenses/{$newExpenseId}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $updateData);
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Status: $httpCode\n";
    echo ($httpCode == 200 ? "   ✅ Success\n" : "   ❌ Failed\n");
}

// 5. DELETE /expenses/{id}
if (isset($newExpenseId)) {
    echo "\n5. DELETE /expenses/{$newExpenseId}\n";
    $ch = curl_init("http://localhost:8001/expenses/{$newExpenseId}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Status: $httpCode\n";
    echo ($httpCode == 200 ? "   ✅ Success\n" : "   ❌ Failed\n");
}

echo "\n=== SUMMARY ===\n";
echo "All expense CRUD operations tested.\n";
