<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();

        $now = date('Y-m-d H:i:s');

        $prefixes = ['Pro', 'Ultra', 'Smart', 'Classic', 'Prime', 'Elite', 'Essential', 'Urban', 'Advanced', 'Compact'];
        $bodies = [
            ['name' => 'Phone', 'category_id' => 3],
            ['name' => 'Laptop', 'category_id' => 2],
            ['name' => 'Television', 'category_id' => 1],
            ['name' => 'Vacuum Cleaner', 'category_id' => 4],
            ['name' => 'Running Shoe', 'category_id' => 14],
            ['name' => 'Smartwatch', 'category_id' => 13],
            ['name' => 'Coffee Maker', 'category_id' => 4],
            ['name' => 'Game Console', 'category_id' => 18],
            ['name' => 'Backpack', 'category_id' => 5],
            ['name' => 'Desk Lamp', 'category_id' => 19],
        ];
        $suffixes = ['Red', 'Blue', 'Black', 'Silver', 'Gold', 'Max', 'Plus', 'Mini', 'Big', 'Portable'];

        $semiRealProducts = [];
        $productNumber = 0;

        foreach ($prefixes as $prefix) {
            foreach ($bodies as $body) {
                $suffix = $suffixes[$productNumber % count($suffixes)];

                $semiRealProducts[] = [
                    'name' => "{$prefix} {$body['name']} {$suffix}",
                    'description' => "A {$prefix} {$body['name']} designed for reliable everyday use.",
                    'price' => random_int(2999, 499999) / 100,
                    'category_id' => $body['category_id'],
                    'is_available' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $productNumber++;
            }
        }

        DB::table('products')->insert($semiRealProducts);
        $this->command->info('100 semi-real products inserted.');

        $totalProducts = 1000000;
        $chunkSize = 5000; // حجم آمن جداً
        $chunkCount = intdiv($totalProducts, $chunkSize);

        // مصفوفات لسرعة توليد الأسماء بدلاً من Faker البطيء
        $items = ['Laptop', 'Phone', 'Monitor', 'Headphones', 'Camera', 'Stand', 'Desk', 'Chair', 'Watch', 'Charger'];
        $words = ['powerful', 'durable', 'sleek', 'fast', 'high quality', 'ergonomic', 'aluminum', 'premium'];

        $baseTime = time();

        $this->command->info("Starting to insert {$totalProducts} fast random products...");

        for ($chunk = 0; $chunk < $chunkCount; $chunk++) {
            $products = [];

            for ($product = 0; $product < $chunkSize; $product++) {
                $days = random_int(1, 730);
                $safeDate = date('Y-m-d 12:00:00', strtotime("-{$days} days", $baseTime));

                $name = $prefixes[array_rand($prefixes)] . ' ' . $items[array_rand($items)] . ' ' . Str::random(5);
                $desc = 'A ' . $words[array_rand($words)] . ' device designed for gaming, office work, and daily efficiency.';

                $products[] = [
                    'name' => $name,
                    'description' => $desc,
                    'price' => random_int(1000, 500000) / 100,
                    'category_id' => random_int(1, 20),
                    'is_available' => random_int(1, 100) <= 85 ? 1 : 0,
                    'created_at' => $safeDate,
                    'updated_at' => $safeDate,
                ];
            }

            DB::table('products')->insert($products);
            $this->command->info('Inserted chunk ' . ($chunk + 1) . " of {$chunkCount}.");
        }

        $this->command->info('Finished inserting all products.');
    }
}