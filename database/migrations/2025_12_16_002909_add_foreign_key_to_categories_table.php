<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add foreign key constraint if it doesn't exist
        Schema::table('categories', function (Blueprint $table) {
            // Check if foreign key already exists
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $tableDetails = $sm->listTableDetails('categories');
            
            if (!$tableDetails->hasForeignKey('categories_user_id_foreign')) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
