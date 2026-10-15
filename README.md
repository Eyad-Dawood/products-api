phpadmin must be running when using aritsan:test becuase it uses mysql to use full text search

no rate limiting so performance tests could be run with no issues

steps :
1 open XAMPP open Mysql , Apache
2- php artisan migrate:fresh --seed (takes time)
3- php artisan serve
4- php artisan performance:test --email=admin@admin.com --password=password --base-url=http://127.0.0.1:8000 --iterations=30

results can be affected from computer resources

server must be running

when using the
php artisan performance:test --email=admin@admin.com --password=password --base-url=http://localhost:8000

journy :

started :
+--------------------+---------+--------+--------+-----------+--------+--------+---------+
| Scenario | Success | Errors | Avg ms | Median ms | Min ms | P95 ms | Max ms |
+--------------------+---------+--------+--------+-----------+--------+--------+---------+
| Category + price | 30 | 0 | 908.73 | 910.08 | 819.21 | 1036 | 1038.15 |
| Available products | 30 | 0 | 505.6 | 506.15 | 453.96 | 547.78 | 548.07 |
| Price range | 30 | 0 | 264.86 | 262.27 | 248.82 | 296.51 | 297.91 |
| Combined filters | 30 | 0 | 904.75 | 909.96 | 800.09 | 968.76 | 991.9 |
+--------------------+---------+--------+--------+-----------+--------+--------+---------+

after adding compsit indexes

+--------------------+---------+--------+--------+-----------+--------+--------+--------+
| Scenario | Success | Errors | Avg ms | Median ms | Min ms | P95 ms | Max ms |
+--------------------+---------+--------+--------+-----------+--------+--------+--------+
| Category + price | 30 | 0 | 247.51 | 244.61 | 226.17 | 266.91 | 335.33 |
| Available products | 30 | 0 | 526.2 | 524.64 | 458.6 | 583.05 | 583.91 |
| Price range | 30 | 0 | 266.7 | 265.21 | 242.11 | 290.69 | 324.61 |
| Combined filters | 30 | 0 | 247.14 | 242.02 | 229.48 | 268.82 | 304.48 |
+--------------------+---------+--------+--------+-----------+--------+--------+--------+

after reviewing and polishing database
+--------------------+---------+--------+--------+-----------+--------+--------+--------+
| Scenario | Success | Errors | Avg ms | Median ms | Min ms | P95 ms | Max ms |
+--------------------+---------+--------+--------+-----------+--------+--------+--------+
| Category + price | 30 | 0 | 93.59 | 78.6 | 56.71 | 169.83 | 175.04 |
| Available products | 30 | 0 | 268.75 | 268.75 | 202.76 | 333.01 | 375.73 |
| Price range | 30 | 0 | 98.27 | 97.8 | 75.59 | 128.57 | 137.25 |
| Combined filters | 30 | 0 | 95.9 | 86.98 | 57.21 | 159.93 | 205.83 |
+--------------------+---------+--------+--------+-----------+--------+--------+--------+

now the only remaning issue is at Available products 268.75

after fixing the in_stock to is_available typo
+--------------------+---------+--------+---------+-----------+---------+---------+---------+
| Scenario | Success | Errors | Avg ms | Median ms | Min ms | P95 ms | Max ms |
+--------------------+---------+--------+---------+-----------+---------+---------+---------+
| Category + price | 30 | 0 | 98.73 | 84.62 | 58.04 | 186.27 | 200.1 |
| Available products | 30 | 0 | 105.19 | 105.53 | 78.23 | 127.43 | 166.63 |
| Price range | 30 | 0 | 100.15 | 91.76 | 76.42 | 155.43 | 165.09 |
| Combined filters | 30 | 0 | 1504.07 | 1500.13 | 1276.76 | 1632.97 | 1921.47 |
+--------------------+---------+--------+---------+-----------+---------+---------+---------+

now the issue is in cobined filters

after logging every query using
`DB::listen(function ($q) {
        Log::info($q->time . 'ms | ' . $q->sql, $q->bindings);
        });
       `

        inside ServiceProvider boot()
        temproraly

and tracing excecution plan and slow queries , then modefing the indexes
final result

+--------------------+---------+--------+--------+-----------+--------+--------+--------+
| Scenario | Success | Errors | Avg ms | Median ms | Min ms | P95 ms | Max ms |
+--------------------+---------+--------+--------+-----------+--------+--------+--------+
| Category + price | 30 | 0 | 187.76 | 186.39 | 169 | 221.12 | 235.09 |
| Available products | 30 | 0 | 726.2 | 717.29 | 624.3 | 861.67 | 912.85 |
| Price range | 30 | 0 | 108.68 | 105.41 | 93.77 | 133.11 | 133.94 |
| Combined filters | 30 | 0 | 181.4 | 176.12 | 161.91 | 207.96 | 212.41 |
+--------------------+---------+--------+--------+-----------+--------+--------+--------+

problem now is Available products

after adding the remaining indexes :
final result :

+--------------------+---------+--------+--------+-----------+--------+--------+--------+
| Scenario | Success | Errors | Avg ms | Median ms | Min ms | P95 ms | Max ms |
+--------------------+---------+--------+--------+-----------+--------+--------+--------+
| Category + price | 30 | 0 | 137.02 | 137.8 | 114.03 | 166.71 | 173.52 |
| Available products | 30 | 0 | 109.11 | 107.39 | 85.6 | 141.03 | 164.75 |
| Price range | 30 | 0 | 105.55 | 99.83 | 79.64 | 146.53 | 153.17 |
| Combined filters | 30 | 0 | 130.44 | 131.23 | 109.32 | 155.46 | 159.35 |
+--------------------+---------+--------+--------+-----------+--------+--------+--------+
