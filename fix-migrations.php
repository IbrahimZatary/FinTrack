<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== MIGRATION FIXER ===\n\n";

// 1. Create users table if it doesn't exist
echo "1. Checking users table...\n";
if (!Schema::hasTable('users')) {
    echo "   Creating users table...\n";
    
    DB::statement("
        CREATE TABLE users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            email_verified_at TIMESTAMP NULL,
            password VARCHAR(255) NOT NULL,
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "   ✅ Users table created\n";
} else {
    echo "   ✅ Users table already exists\n";
}

// 2. Create categories table
echo "\n2. Checking categories table...\n";
if (!Schema::hasTable('categories')) {
    echo "   Creating categories table...\n";
    
    DB::statement("
        CREATE TABLE categories (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            color VARCHAR(7) NOT NULL,
            icon VARCHAR(255) NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY user_category_unique (user_id, name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "   ✅ Categories table created\n";
} else {
    echo "   ✅ Categories table already exists\n";
}

// 3. Create expenses table (WITH CORRECT TYPES)
echo "\n3. Checking expenses table...\n";
if (!Schema::hasTable('expenses')) {
    echo "   Creating expenses table...\n";
    
    DB::statement("
        CREATE TABLE expenses (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            category_id BIGINT UNSIGNED NOT NULL,
            amount DECIMAL(10,2) NOT NULL, -- DECIMAL for money
            date DATE NOT NULL, -- DATE type for dates
            description TEXT NULL,
            receipt_path VARCHAR(255) NULL,
            deleted_at TIMESTAMP NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
            INDEX user_date_index (user_id, date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "   ✅ Expenses table created (with DECIMAL amount and DATE)\n";
} else {
    echo "   ✅ Expenses table already exists\n";
    
    // Check if columns are correct
    $columns = DB::select("DESCRIBE expenses");
    foreach ($columns as $col) {
        if ($col->Field == 'amount' && strpos($col->Type, 'decimal') === false) {
            echo "   ⚠️  amount column is {$col->Type}, should be DECIMAL\n";
            DB::statement("ALTER TABLE expenses MODIFY COLUMN amount DECIMAL(10,2) NOT NULL");
            echo "   ✅ Fixed amount column to DECIMAL\n";
        }
        if ($col->Field == 'date' && strpos($col->Type, 'date') === false) {
            echo "   ⚠️  date column is {$col->Type}, should be DATE\n";
            DB::statement("ALTER TABLE expenses MODIFY COLUMN date DATE NOT NULL");
            echo "   ✅ Fixed date column to DATE\n";
        }
    }
}

// 4. Create budgets table
echo "\n4. Checking budgets table...\n";
if (!Schema::hasTable('budgets')) {
    echo "   Creating budgets table...\n";
    
    DB::statement("
        CREATE TABLE budgets (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            category_id BIGINT UNSIGNED NOT NULL, -- MUST HAVE THIS
            amount DECIMAL(10,2) NOT NULL,
            month INT NOT NULL,
            year INT NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
            UNIQUE KEY user_category_month_year_unique (user_id, category_id, month, year)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "   ✅ Budgets table created (with category_id)\n";
} else {
    echo "   ✅ Budgets table already exists\n";
    
    // Check if has category_id
    $columns = DB::select("DESCRIBE budgets");
    $hasCategoryId = false;
    foreach ($columns as $col) {
        if ($col->Field == 'category_id') {
            $hasCategoryId = true;
        }
    }
    
    if (!$hasCategoryId) {
        echo "   ⚠️  Adding category_id to budgets table...\n";
        DB::statement("ALTER TABLE budgets ADD COLUMN category_id BIGINT UNSIGNED NOT NULL AFTER user_id");
        DB::statement("ALTER TABLE budgets ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE");
        echo "   ✅ Added category_id column\n";
    }
}

echo "\n=================================\n";
echo "DATABASE TABLES CREATED/UPDATED\n";
echo "=================================\n";
echo "Now run the verification test again!\n";