<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixDateColumnInExpensesTable extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Change from integer to date
            $table->date('date')->change();
        });
        // Also add this to the up() method:
        $table->decimal('amount', 10, 2)->change(); // 10 digits total, 2 decimal places
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Revert back to integer (if needed)
            $table->integer('date')->change();
        });
    }
    
}