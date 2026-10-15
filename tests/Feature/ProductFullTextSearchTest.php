<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\TestCase;
use App\Enums\UserRole;

class ProductFullTextSearchTest extends TestCase
{
    use DatabaseTruncation;

    private User $user;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN->value,
        ]);

        $this->actingAs($this->user, 'api');
    }

    private function createProduct(array $attributes = []): Product
    {
        $category = Category::firstOrCreate(['name' => 'Test Category']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 100,
            'is_available' => true,
        ], $attributes));
    }

    public function test_it_searches_product_name_and_description(): void
    {
        $this->createProduct([
            'name' => 'Wireless Device',
            'description' => 'A smartphone for daily use',
        ]);

        $this->getJson('/api/products/search?q=Wireless')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Wireless Device']);

        $this->getJson('/api/products/search?q=smartphone')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Wireless Device']);
    }
}
?>
