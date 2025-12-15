<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DASHBOARD & ANALYTICS TESTS ===\n\n";

// 1. GET /dashboard
echo "1. GET /dashboard\n";
$ch = curl_init('http://localhost:8001/dashboard');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status: $httpCode\n";
if ($httpCode == 200) {
    $data = json_decode($response, true);
    echo "   ✅ Success\n";
    echo "   Monthly spent: \$" . ($data['monthly_spent'] ?? 0) . "\n";
    echo "   Monthly budget: \$" . ($data['monthly_budget'] ?? 0) . "\n";
    echo "   Remaining: \$" . ($data['remaining'] ?? 0) . "\n";
} else {
    echo "   ❌ Failed\n";
}

// 2. GET /api/analytics/spending-by-category
echo "\n2. GET /api/analytics/spending-by-category\n";
$ch = curl_init('http://localhost:8001/api/analytics/spending-by-category');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status: $httpCode\n";
if ($httpCode == 200) {
    $data = json_decode($response, true);
    echo "   ✅ Success\n";
    echo "   Data points: " . count($data) . "\n";
    if (count($data) > 0) {
        echo "   Sample data:\n";
        foreach (array_slice($data, 0, 3) as $item) {
            echo "     - {$item['category']}: \${$item['amount']}\n";
        }
    }
} else {
    echo "   ❌ Failed\n";
    echo "   Response: $response\n";
}
