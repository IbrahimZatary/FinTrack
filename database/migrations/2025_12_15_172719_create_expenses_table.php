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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            // FIXED: DECIMAL for money (10 digits total, 2 decimal places)
            $table->decimal('amount', 10, 2);
            
            // FIXED: DATE type for dates
            $table->date('date');
            
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->softDeletes(); // For trash functionality
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};