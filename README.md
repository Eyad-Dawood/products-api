phpadmin must be running when using aritsan:test becuase it uses mysql to use full text search

no rate limiting so performance tests could be run with no issues

server must be running

composer run dev

when using the
php artisan performance:test --email=admin@admin.com --password=password --base-url=http://localhost:8000
