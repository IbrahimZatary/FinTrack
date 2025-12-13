<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_category_and_expense()
    {
        // Create user
        $user = User::factory()->create();
        
        // Create category
        $category = Category::factory()->create(['user_id' => $user->id]);
        
        // Create expense
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        // Test relationships
        $this->assertEquals(1, $user->categories()->count());
        $this->assertEquals(1, $user->expenses()->count());
        $this->assertEquals(1, $category->expenses()->count());
        $this->assertEquals($user->id, $expense->user_id);
    }
}