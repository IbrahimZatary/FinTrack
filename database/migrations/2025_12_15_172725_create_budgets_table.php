<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            // If users doesn't exist, skip this migration
            return;
        }
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // CRITICAL: This must exist!
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            $table->decimal('amount', 10, 2); // DECIMAL for money
            $table->integer('month'); // 1-12
            $table->integer('year'); // 2024
            $table->timestamps();
            
            // User can only have one budget per category per month
            $table->unique(['user_id', 'category_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};