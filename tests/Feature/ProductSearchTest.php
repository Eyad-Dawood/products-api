<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\TestCase;
use App\Enums\UserRole;


class ProductSearchTest extends TestCase
{
    use DatabaseTruncation;
    private Category $category;
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

        $this->category = Category::create([
            'name' => 'Electronics',
        ]);
    }

    private function createProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $this->category->id,
            'name' => 'Wireless Smartphone',
            'description' => 'A wireless smartphone device',
            'price' => 1000,
            'is_available' => true,
        ], $attributes));
    }

    

    public function test_it_rejects_invalid_price_ranges(): void
    {
        $this->getJson('/api/products/search?min_price=5000&max_price=1000')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['max_price']);
    }

    public function test_it_rejects_page_sizes_above_fifty(): void
    {
        $this->getJson('/api/products/search?per_page=51')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_it_sorts_products_by_price(): void
    {
        $this->createProduct([
            'name' => 'Expensive Phone',
            'price' => 2000,
        ]);

        $this->createProduct([
            'name' => 'Cheap Phone',
            'price' => 500,
        ]);

        $this->getJson('/api/products/search?sort_by=price&sort_dir=asc')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Cheap Phone')
            ->assertJsonPath('data.1.name', 'Expensive Phone');
    }

    public function test_it_returns_an_empty_page_when_no_products_match(): void
    {
        $this->getJson('/api/products/search?q=nonexistenttermxyz')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_it_filters_products_by_category(): void
    {
        $otherCategory = Category::create([
            'name' => 'Books',
        ]);

        $this->createProduct([
            'name' => 'Phone',
            'category_id' => $this->category->id,
        ]);

        $this->createProduct([
            'name' => 'Book',
            'category_id' => $otherCategory->id,
        ]);

        $this->getJson('/api/products/search?category_id=' . $otherCategory->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Book');
    }

    public function test_it_sorts_products_by_creation_date(): void
    {
        $older = $this->createProduct([
            'name' => 'Older Product',
        ]);

        $newer = $this->createProduct([
            'name' => 'Newer Product',
        ]);

        // Ensure different timestamps even on databases with limited precision.
        $older->forceFill([
            'created_at' => now()->subDay(),
        ])->save();

        $newer->forceFill([
            'created_at' => now(),
        ])->save();

        $this->getJson('/api/products/search?sort_by=created_at&sort_dir=desc')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Newer Product')
            ->assertJsonPath('data.1.name', 'Older Product');
    }

    public function test_it_paginates_results(): void
    {
        foreach (range(1, 25) as $number) {
            $this->createProduct([
                'name' => "Product {$number}",
            ]);
        }

        $this->getJson('/api/products/search?per_page=10&page=2')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_it_rejects_invalid_sorting_parameters(): void
    {
        $this->getJson('/api/products/search?sort_by=random_column')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort_by']);
    }

    public function test_it_rejects_nonexistent_categories(): void
    {
        $this->getJson('/api/products/search?category_id=999999')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_it_handles_an_empty_search_term(): void
    {
        $this->createProduct([
            'name' => 'Wireless Phone',
        ]);

        $this->createProduct([
            'name' => 'Office Desk',
        ]);

        $this->getJson('/api/products/search?q=')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_it_sorts_products_by_price_descending(): void
    {
        $this->createProduct([
            'name' => 'Cheap Product',
            'price' => 100,
        ]);

        $this->createProduct([
            'name' => 'Expensive Product',
            'price' => 900,
        ]);

        $this->getJson('/api/products/search?sort_by=price&sort_dir=desc')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Expensive Product')
            ->assertJsonPath('data.1.name', 'Cheap Product');
    }
    

    public function test_it_rejects_zero_per_page(): void
    {
        $this->getJson('/api/products/search?per_page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_it_rejects_invalid_page_numbers(): void
    {
        $this->getJson('/api/products/search?page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['page']);
    }
}