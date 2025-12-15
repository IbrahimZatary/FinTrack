<?php
$file = 'app/Http/Controllers/DashboardController.php';
if (!file_exists($file)) {
    echo "❌ DashboardController not found\n";
    exit;
}

$content = file_get_contents($file);

// Find the JSON return statement and replace it with view return
if (strpos($content, 'response()->json') !== false) {
    // Replace the entire return statement
    $pattern = '/return response\(\)->json\(\[(.*?)\]\);/s';
    
    $replacement = 'return view(\'dashboard.index\', [
        \'period\' => [
            \'month\' => $month,
            \'year\' => $year
        ]
    ]);';
    
    $content = preg_replace($pattern, $replacement, $content, 1);
    
    file_put_contents($file, $content);
    echo "✅ Updated DashboardController to return view\n";
    
    // Show the updated return statement
    echo "\nUpdated return statement:\n";
    preg_match('/return view\(.*?\);/s', $content, $match);
    if (isset($match[0])) {
        echo $match[0] . "\n";
    }
} else {
    echo "⚠️ Could not find JSON return statement\n";
    
    // Alternative: find the index method and add view return
    $lines = file($file);
    $newLines = [];
    $inIndexMethod = false;
    $braceCount = 0;
    
    foreach ($lines as $line) {
        if (strpos($line, 'public function index') !== false) {
            $inIndexMethod = true;
        }
        
        if ($inIndexMethod) {
            if (strpos($line, '{') !== false) $braceCount++;
            if (strpos($line, '}') !== false) $braceCount--;
            
            // Check if this is a return statement
            if (strpos($line, 'return') !== false && $braceCount > 0) {
                // Replace with view return
                $newLines[] = '        return view(\'dashboard.index\', [
            \'period\' => [
                \'month\' => $month,
                \'year\' => $year
            ]
        ]);' . "\n";
                continue;
            }
        }
        
        $newLines[] = $line;
    }
    
    file_put_contents($file, implode('', $newLines));
    echo "✅ Updated DashboardController (alternative method)\n";
}
