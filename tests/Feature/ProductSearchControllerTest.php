<?php

use App\Http\Controllers\ProductSearchController;
use App\Http\Requests\ProductSearchRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Carbon;
function searchProducts(array $query = []): array
{
    $request = ProductSearchRequest::create(
        '/api/products/search',
        'GET',
        $query,
    );

    return app(ProductSearchController::class)
        ->index($request)
        ->getData(true);
}

it('returns products with their category', function () {
    $category = Category::create(['name' => 'Electronics']);

    $olderProduct = Product::create([
    'name' => 'Keyboard',
    'description' => 'Wireless keyboard',
    'price' => 49.99,
    'category_id' => $category->id,
    'is_available' => true,
]);

$olderProduct->created_at = Carbon::parse('2026-01-01 10:00:00');
$olderProduct->save();

$newerProduct = Product::create([
    'name' => 'Monitor',
    'description' => '4K monitor',
    'price' => 299.99,
    'category_id' => $category->id,
    'is_available' => true,
]);

$newerProduct->created_at = Carbon::parse('2026-01-02 10:00:00');
$newerProduct->save();
    

    $response = searchProducts(['per_page' => 1]);



    expect($response['data'])->toHaveCount(1)
        ->and($response['data'][0])->toMatchArray([
            'id' => $newerProduct->id,
            'name' => 'Monitor',
            'description' => '4K monitor',
            'price' => 299.99,
            'category_id' => $category->id,
            'category_name' => 'Electronics',
            'is_available' => true,
        ])
        ->and($response['meta']['per_page'])->toBe(1)
        ->and($response['meta']['current_page'])->toBe(1)
        ->and($olderProduct->id)->not->toBe($newerProduct->id);
});

it('filters products by price, category, availability, and sorts the results', function () {
    $electronics = Category::create(['name' => 'Electronics']);
    $books = Category::create(['name' => 'Books']);

    Product::create([
        'name' => 'Budget monitor',
        'description' => 'Affordable display',
        'price' => 100,
        'category_id' => $electronics->id,
        'is_available' => true,
    ]);

    $matchingProduct = Product::create([
        'name' => 'Premium monitor',
        'description' => 'High-end display',
        'price' => 250,
        'category_id' => $electronics->id,
        'is_available' => true,
    ]);

    Product::create([
        'name' => 'Unavailable monitor',
        'description' => 'Display currently unavailable',
        'price' => 300,
        'category_id' => $electronics->id,
        'is_available' => false,
    ]);

    Product::create([
        'name' => 'Premium book',
        'description' => 'A technology book',
        'price' => 250,
        'category_id' => $books->id,
        'is_available' => true,
    ]);

    $response = searchProducts([
        'min_price' => 200,
        'max_price' => 275,
        'category_id' => $electronics->id,
        'is_available' => true,
        'sort_by' => 'price',
        'sort_dir' => 'asc',
    ]);

    expect($response['data'])->toHaveCount(1)
        ->and($response['data'][0]['id'])->toBe($matchingProduct->id)
        ->and($response['data'][0]['price'])->toEqual(250);
});

it('searches product names and descriptions', function () {
    $category = Category::create(['name' => 'Electronics']);

    $matchingProduct = Product::create([
        'name' => 'Travel adapter',
        'description' => 'Compact charger for international travel',
        'price' => 35,
        'category_id' => $category->id,
        'is_available' => true,
    ]);

    Product::create([
        'name' => 'Desk lamp',
        'description' => 'Warm light for a home office',
        'price' => 25,
        'category_id' => $category->id,
        'is_available' => true,
    ]);

    $response = searchProducts(['q' => 'travel']);

    expect($response['data'])->toHaveCount(1)
        ->and($response['data'][0]['id'])->toBe($matchingProduct->id);
});
