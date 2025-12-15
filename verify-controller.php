<?php
require __DIR__.'/vendor/autoload.php';

echo "=== VERIFYING AnalyticsController ===\n\n";

// Method 1: Direct file check
echo "1. File exists: ";
echo file_exists('app/Http/Controllers/AnalyticsController.php') ? "✅\n" : "❌\n";

// Method 2: Check class with different approaches
echo "\n2. Class loading tests:\n";

// Test 1: Standard namespace
if (class_exists('App\Http\Controllers\AnalyticsController')) {
    echo "   ✅ Standard namespace works\n";
    
    $reflection = new ReflectionClass('App\Http\Controllers\AnalyticsController');
    echo "   ✅ File: " . $reflection->getFileName() . "\n";
    
    // Check method exists
    if ($reflection->hasMethod('spendingByCategory')) {
        echo "   ✅ Method spendingByCategory() exists\n";
    } else {
        echo "   ❌ Method spendingByCategory() NOT found\n";
    }
} else {
    echo "   ❌ Standard namespace failed\n";
}

// Test 2: Try to require the file directly
echo "\n3. Direct file require:\n";
try {
    require_once 'app/Http/Controllers/AnalyticsController.php';
    echo "   ✅ File can be required\n";
    
    // Check if class exists after require
    if (class_exists('App\Http\Controllers\AnalyticsController')) {
        echo "   ✅ Class exists after require\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error requiring file: " . $e->getMessage() . "\n";
}

// Check the actual content
echo "\n4. File content analysis:\n";
$content = file_get_contents('app/Http/Controllers/AnalyticsController.php');

if (strpos($content, 'namespace App\Http\Controllers;') !== false) {
    echo "   ✅ Correct namespace declaration\n";
} else {
    echo "   ❌ Wrong namespace. Found:\n";
    preg_match('/namespace\s+.*;/', $content, $matches);
    echo "      " . ($matches[0] ?? 'Not found') . "\n";
}

if (strpos($content, 'class AnalyticsController extends Controller') !== false) {
    echo "   ✅ Correct class declaration\n";
} else {
    echo "   ❌ Wrong class declaration\n";
}

if (strpos($content, 'public function spendingByCategory()') !== false) {
    echo "   ✅ Method spendingByCategory found\n";
} else {
    echo "   ❌ Method spendingByCategory not found\n";
}

// Test instantiation
echo "\n5. Instantiation test:\n";
try {
    $controller = new App\Http\Controllers\AnalyticsController();
    echo "   ✅ Can instantiate AnalyticsController\n";
} catch (Exception $e) {
    echo "   ❌ Cannot instantiate: " . $e->getMessage() . "\n";
}
