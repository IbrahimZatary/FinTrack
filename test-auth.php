<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== AUTHENTICATION TEST ===\n\n";

// Get CSRF token first
$ch = curl_init('http://localhost:8001/sanctum/csrf-cookie');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
$response = curl_exec($ch);
curl_close($ch);

// Login
$user = User::where('email', 'test@backend.com')->first();

if (!$user) {
    echo "❌ Test user not found\n";
    exit;
}

$loginData = [
    'email' => 'test@backend.com',
    'password' => 'password123'
];

$ch = curl_init('http://localhost:8001/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Status: ";
if ($httpCode == 302 || $httpCode == 200) {
    echo "✅ Success\n";
} else {
    echo "❌ Failed (HTTP $httpCode)\n";
    echo "Response: $response\n";
}
