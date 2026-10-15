# Product Search API

A **Laravel 13** based backend service that provides powerful product search capabilities with filtering, sorting, pagination, and full‑text search. The API is designed for two user roles – **admin** and **customer** – and uses JWT authentication via the `api` guard.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Authentication & Roles](#authentication--roles)
- [API Endpoints](#api-endpoints)
- [Testing](#testing)
- [Performance Testing](#performance-testing)
- [Postman Collection](#postman-collection)
- [Known Limitations](#known-limitations)
- [License](#license)

---

## Features

- **Product search** with optional query term (`q`) that performs a full‑text search on `name` and `description`.
- **Filtering** by price range (`min_price`, `max_price`), stock availability (`is_available`), and category (`category_id`).
- **Sorting** by any column (`sort_by`) with direction (`sort_order`).
- **Pagination** with `per_page` (max 50) and `page` parameters. Results include standard Laravel pagination meta data (`current_page`, `per_page`, `total`, `last_page`).
- **Validation** for all request parameters – invalid values return `422 Unprocessable Entity` with detailed error messages.
- **JWT authentication** (`Auth::guard('api')`) and role‑based middleware (`role:admin|customer`).
- **Cache** of query results (default 10 minutes) to speed up repeated requests.
- **Performance benchmark** command (`php artisan performance:test`) with built‑in reporting.

---

## Tech Stack

- PHP 8.3+
- Laravel 13
- MySQL / MariaDB
- JWT authentication (auth:api guard)
- PHPUnit / Laravel Testing
- Laravel Boost (development utilities)

---

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/Eyad-Dawood/products-api
cd products-api

# 2. Install PHP dependencies
composer install 

# 3. Copy environment file and generate app key
copy .env.example .env

# 4. Generate Secrete Key
php artisan key:generate

# 5. Generate jwt Key
php artisan jwt:secret
```

### Requirements

- PHP 8.3+ and Composer
- MySQL or MariaDB server
- Git

---

## Configuration

1. Edit the `.env` file and set your database credentials.
2. Run the migrations and seed the database (this creates the default admin user):
    ```bash
    php artisan migrate:fresh --seed
    ```
3. Start the development server:
    ```bash
    php artisan serve
    ```
    By default the API is available at `http://127.0.0.1:8000`.

---

## Authentication & Roles

The API uses the **`api` guard** (JWT). Two roles exist:

- **admin** – full access to all endpoints.
- **customer** – can perform product searches.

### Default credentials

```
email:    admin@admin.com
password: password
```

You can obtain a JWT token via the login endpoint:

```
POST /api/auth/login
{
  "email": "admin@admin.com",
  "password": "password"
}
```

The response contains `access_token`. Include it in subsequent requests:

```
Authorization: Bearer <access_token>
```

---

## API Endpoints

| Method   | URI                           | Description                            |
| -------- | ----------------------------- | -------------------------------------- |
| **POST** | `/api/auth/register/customer` | Register a new customer.               |
| **POST** | `/api/auth/register/admin`    | Register a new admin (admin only).     |
| **POST** | `/api/auth/login`             | Returns JWT token for a user.          |
| **POST** | `/api/auth/refresh`           | Refresh JWT token.                     |
| **GET**  | `/api/auth/me`                | Get authenticated user details.        |
| **POST** | `/api/auth/logout`            | Logout (revoke token).                 |
| **GET**  | `/api/products/search`        | Search products with optional filters. |

### Search Parameters

| Parameter                 | Type              | Description                                               |
| ------------------------- | ----------------- | --------------------------------------------------------- |
| `q`                       | string            | Full‑text search term (matches `name` and `description`). |
| `min_price` / `max_price` | numeric           | Price range filter.                                       |
| `category_id`             | integer           | Filter by category.                                       |
| `is_available`            | boolean (`1`/`0`) | Return only items with `is_available = true`.             |
| `sort_by`                 | string            | Column to sort (`price`, `created_at`, …).                |
| `sort_order`              | `asc` / `desc`    | Sort direction.                                           |
| `per_page`                | integer (1‑50)    | Number of items per page.                                 |
| `page`                    | integer (≥ 1)     | Page number.                                              |

All parameters are validated; invalid values result in a `422` response with a JSON payload containing `errors`.

---

## Testing

The project ships with a comprehensive feature test suite covering:

- Filtering, sorting, pagination, validation errors
- Full‑text search
- Role‑based authentication

Run the tests with:

```bash
php artisan test --testsuite=Feature
```

The tests use the `api` guard (`Auth::guard('api')`) and log in the default admin user before each request.

---

## Performance Testing

A custom Artisan command benchmarks the search endpoint under realistic load.

```bash
php artisan performance:test \
    --email=admin@admin.com \
    --password=password \
    --base-url=http://127.0.0.1:8000 \
    --iterations=30
```

The command outputs a table similar to the one below (real numbers):

```
+--------------------+---------+--------+--------+-----------+--------+--------+--------+
| Scenario           | Success | Errors | Avg ms | Median ms | Min ms | P95 ms | Max ms |
+--------------------+---------+--------+--------+-----------+--------+--------+--------+
| Category + price   | 200     | 0      | 104.41 | 99.6      | 78.57  | 130.13 | 204.47 |
| Available products | 200     | 0      | 104.82 | 99.19     | 78.9   | 138.04 | 218.37 |
| Price range        | 200     | 0      | 114.95 | 105.48    | 72.72  | 191.09 | 350.18 |
| Combined filters   | 200     | 0      | 125.25 | 112.56    | 69.31  | 206.67 | 403.01 |
+--------------------+---------+--------+--------+-----------+--------+--------+--------+
```

> **Note**: Results depend on local machine resources and database configuration. For more stable numbers, enable query caching and appropriate indexes (see the project’s migration files).

---

## Postman Collection

A ready‑to‑import Postman collection is available for manual API exploration. Download it from the repository's `docs/` folder or use the [link]([https://github.com/Eyad-Dawood/products-api/docs/products-api.postman_collection.json](https://github.com/Eyad-Dawood/products-api/blob/main/docs/products-api.postman_collection.json)).

---

## Known Limitations

- No rate limiting – useful for performance tests but may need to be added for production.
- Full‑text search requires MySQL's `FULLTEXT` indexes on `name` and `description`. Make sure the MySQL server is running when running migrations or performance tests.

---

## License

MIT © Eyad Dawood 2026
