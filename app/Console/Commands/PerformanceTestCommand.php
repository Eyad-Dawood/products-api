<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class PerformanceTestCommand extends Command
{
    protected $signature = 'performance:test
        {--iterations=30 : Requests per scenario}
        {--base-url=http://localhost:8000 : API base URL}
        {--email=admin@admin.com : Login email}
        {--password=password : Login password}';

    protected $description =
        'Benchmark product search API using an automatically acquired JWT';

    public function handle(): int
    {
        $iterations = (int) $this->option('iterations');
        $baseUrl = rtrim($this->option('base-url'), '/');
        $email = $this->option('email');
        $password = $this->option('password');

        if ($iterations < 1) {
            $this->error('Iterations must be at least 1.');

            return self::FAILURE;
        }

        // 1. Authenticate and acquire JWT.
        $this->info('Logging in...');

        try {
            $loginResponse = Http::acceptJson()
                ->timeout(30)
                ->post("{$baseUrl}/api/auth/login", [
                    'email' => $email,
                    'password' => $password,
                ]);
        } catch (ConnectionException $e) {
            $this->error('Cannot connect to the API. Is the server running?');

            return self::FAILURE;
        }

        if (! $loginResponse->successful()) {
            $this->error(
                "Login failed. HTTP status: {$loginResponse->status()}"
            );

            return self::FAILURE;
        }

        $token = $loginResponse->json('access_token');

        if (! is_string($token) || $token === '') {
            $this->error('Login response does not contain access_token.');

            return self::FAILURE;
        }

        $this->info('Login successful. JWT acquired.');
        $this->line("Target: {$baseUrl}");
        $this->line("Iterations per scenario: {$iterations}");
        $this->newLine();

        // 2. Define search scenarios.
        $scenarios = [
             'Category + price' => [
                 'category_id' => 19,
                 'min_price' => 0,
                 'max_price' => 1000,
             ],
             'Available products' => [
                 'is_available' => 0,
             ],
             'Price range' => [
                 'min_price' => 100,
                 'max_price' => 500,
             ],
            'Combined filters' => [
                'category_id' => 19,
                'min_price' => 0,
                'max_price' => 1000,
                'is_available' => 1,
            ],
        ];

        $results = [];

        // 3. Run authenticated search requests.
        foreach ($scenarios as $name => $params) {
            $this->line("Testing: {$name}");

            $times = [];
            $errors = 0;

            for ($i = 0; $i < $iterations; $i++) {
                $query = array_merge($params, [
                    'per_page' => 10,
                    'page' => 1,
                ]);

                $start = hrtime(true);

                try {
                    $response = Http::acceptJson()
                        ->withToken($token)
                        ->timeout(30)
                        ->get(
                            "{$baseUrl}/api/products/search",
                            $query
                        );

                    $elapsedMs =
                        (hrtime(true) - $start) / 1_000_000;

                    if ($response->successful()) {
                        $times[] = $elapsedMs;
                    } else {
                        $errors++;
                    }
                } catch (ConnectionException $e) {
                    $errors++;
                }
            }

            sort($times);

            $count = count($times);

            $median = null;
            $p95 = null;

            if ($count > 0) {
                $middle = intdiv($count, 2);

                $median = $count % 2 === 1
                    ? $times[$middle]
                    : ($times[$middle - 1] + $times[$middle]) / 2;

                $p95Index = max(
                    0,
                    (int) ceil(0.95 * $count) - 1
                );

                $p95 = $times[$p95Index];
            }

            $results[] = [
                $name,
                $count,
                $errors,
                $count ? round(array_sum($times) / $count, 2) : 'N/A',
                $median !== null ? round($median, 2) : 'N/A',
                $count ? round($times[0], 2) : 'N/A',
                $p95 !== null ? round($p95, 2) : 'N/A',
                $count ? round($times[$count - 1], 2) : 'N/A',
            ];
        }

        // 4. Display summary.
        $this->newLine();

        $this->table(
            [
                'Scenario',
                'Success',
                'Errors',
                'Avg ms',
                'Median ms',
                'Min ms',
                'P95 ms',
                'Max ms',
            ],
            $results
        );

        $this->info('Performance test completed.');

        return self::SUCCESS;
    }
}
