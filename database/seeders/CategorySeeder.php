<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $categories = [
            'Electronics', 'Laptops & Computers', 'Smartphones', 'Home Appliances',
            'Men\'s Fashion', 'Women\'s Fashion', 'Kids & Toys', 'Sports & Outdoors',
            'Books & Stationery', 'Health & Beauty', 'Automotive', 'Furniture',
            'Jewelry & Watches', 'Shoes', 'Pet Supplies', 'Groceries',
            'Musical Instruments', 'Video Games', 'Office Supplies', 'Tools & Home Improvement'
        ];

        $data = [];
        $now = now()->format('Y-m-d H:i:s');

        foreach ($categories as $category) {
            $data[] = [
                'name' => $category,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('categories')->insert($data);

        $this->command->info(count($categories) . ' Categories inserted successfully!');
    }
}
