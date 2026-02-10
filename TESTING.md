# Testing Guide

## Overview

This project includes comprehensive test coverage using PHPUnit with Feature and Unit tests.

## Test Structure

```
tests/
├── Feature/              # API endpoint tests
│   ├── AuthTest.php     # Authentication endpoints
│   ├── ProductTest.php  # Product CRUD and listing
│   ├── CartTest.php     # Shopping cart operations
│   ├── OrderTest.php    # Checkout and orders
│   └── CommentTest.php  # Product comments
├── Unit/                 # Service and logic tests
│   └── PaymentServiceTest.php
└── TestCase.php         # Base test class
```

## Running Tests

### All Tests
```bash
php artisan test
```

### Feature Tests Only
```bash
php artisan test --testsuite=Feature
```

### Unit Tests Only
```bash
php artisan test --testsuite=Unit
```

### Specific Test File
```bash
php artisan test tests/Feature/AuthTest.php
```

### Specific Test Method
```bash
php artisan test --filter test_user_can_login
```

### With Coverage (requires Xdebug)
```bash
php artisan test --coverage
```

### Parallel Testing
```bash
php artisan test --parallel
```

## Test Database

Tests use an in-memory SQLite database configured in `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Each test runs in a transaction that is rolled back, ensuring isolation.

## Writing Tests

### Feature Test Example

```php
public function test_user_can_view_products(): void
{
    $products = Product::factory()->count(5)->create();

    $response = $this->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
}
```

### Authenticated Request Example

```php
public function test_authenticated_user_can_add_to_cart(): void
{
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

    $response->assertStatus(200);
}
```

### Unit Test Example

```php
public function test_can_calculate_cart_total(): void
{
    $service = app(PaymentService::class);

    $items = [
        ['price' => 10.00, 'quantity' => 2],
        ['price' => 15.50, 'quantity' => 1],
    ];

    $total = $service->calculateTotal($items);

    $this->assertEquals(35.50, $total);
}
```

## Factories

Factories are used to generate test data:

```php
// Create single model
$user = User::factory()->create();

// Create multiple models
$products = Product::factory()->count(10)->create();

// Create with specific attributes
$admin = User::factory()->admin()->create();

// Create without persisting
$user = User::factory()->make();
```

### Available Factories

- `UserFactory` - Regular users and admins
- `ProductFactory` - Products with categories
- `CategoryFactory` - Product categories
- `CommentFactory` - Product comments

### Factory States

```php
// User states
User::factory()->admin()->create();
User::factory()->unverified()->create();

// Product states
Product::factory()->featured()->create();
Product::factory()->inactive()->create();

// Comment states
Comment::factory()->approved()->create();
Comment::factory()->pending()->create();
```

## Test Coverage

### Authentication Tests
- ✅ User registration
- ✅ User login
- ✅ User logout
- ✅ Get authenticated user profile
- ✅ Invalid credentials handling

### Product Tests
- ✅ List all products
- ✅ View single product
- ✅ Filter by category
- ✅ List featured products
- ✅ Admin can create product
- ✅ Admin can update product
- ✅ Admin can delete product
- ✅ Non-admin cannot manage products

### Cart Tests
- ✅ View cart
- ✅ Add product to cart
- ✅ Remove product from cart
- ✅ Clear cart
- ✅ Guest access restrictions

### Order Tests
- ✅ Checkout process
- ✅ Empty cart validation
- ✅ Admin can view all orders
- ✅ Non-admin cannot view all orders

### Comment Tests
- ✅ List product comments
- ✅ Only approved comments visible
- ✅ Authenticated user can comment
- ✅ Guest cannot comment
- ✅ Admin can approve comments
- ✅ Admin can delete comments

### Unit Tests
- ✅ Payment amount calculation
- ✅ Stripe amount formatting
- ✅ Product availability check

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, cURL, sqlite

      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Run Tests
        run: php artisan test
```

## Mocking External Services

### Stripe Mocking

```php
use Stripe\StripeClient;

public function test_checkout_with_mocked_stripe(): void
{
    $this->mock(StripeClient::class, function ($mock) {
        $mock->shouldReceive('checkout->sessions->create')
            ->andReturn((object) ['id' => 'session_123']);
    });

    // Test checkout...
}
```

## Best Practices

1. **Test Isolation**: Each test should be independent
2. **Clear Names**: Test names should describe what they test
3. **AAA Pattern**: Arrange, Act, Assert
4. **Use Factories**: Don't manually create test data
5. **Test Edge Cases**: Include error scenarios
6. **Keep Tests Fast**: Use in-memory database
7. **Don't Test Framework**: Test your code, not Laravel

## Debugging Tests

### Dump Response
```php
$response->dump(); // Show response
$response->dd();   // Show and die
```

### Print Database Queries
```php
DB::enableQueryLog();
// ... test code ...
dd(DB::getQueryLog());
```

### Debug Specific Test
```php
php artisan test --filter test_name --stop-on-failure
```

## Common Assertions

```php
// Status codes
$response->assertStatus(200);
$response->assertOk();
$response->assertCreated();
$response->assertUnauthorized();
$response->assertForbidden();

// JSON structure
$response->assertJsonStructure(['data' => ['*' => ['id', 'name']]]);

// JSON content
$response->assertJson(['name' => 'Test']);
$response->assertJsonPath('user.email', 'test@example.com');

// Database
$this->assertDatabaseHas('users', ['email' => 'test@example.com']);
$this->assertDatabaseMissing('users', ['email' => 'deleted@example.com']);
$this->assertDatabaseCount('products', 5);
```

## Troubleshooting

### Tests Failing Locally

1. Clear config cache: `php artisan config:clear`
2. Clear test database: `php artisan test --recreate-databases`
3. Check `.env.testing` file
4. Ensure all migrations run

### Memory Issues

Increase memory limit in `phpunit.xml`:

```xml
<php>
    <ini name="memory_limit" value="512M"/>
</php>
```

### Slow Tests

- Use `RefreshDatabase` trait instead of migrations
- Reduce factory relationships
- Use `--parallel` flag
- Profile with `--profile`

---

For more information, see [Laravel Testing Documentation](https://laravel.com/docs/testing).
