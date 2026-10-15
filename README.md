phpadmin must be running when using aritsan:test becuase it uses mysql to use full text search

no rate limiting so performance tests could be run with no issues

server must be running

composer run dev

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
