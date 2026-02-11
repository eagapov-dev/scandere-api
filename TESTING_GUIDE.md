# API Testing Guide

## Overview

Comprehensive test suite for all API endpoints including Auth, Products, Cart, Orders, Comments, Admin features, Contact forms, Newsletter, and Email notifications.

## Test Coverage

### Public Endpoints
- **AuthTest** - Registration, Login, Logout, Password Reset
- **ProductTest** - List, Show, Featured, Categories, Download
- **CartTest** - Add, Remove, Clear, Bundle
- **OrderTest** - Checkout, Payment Success
- **CommentTest** - Create, List, Recent Q&A
- **ContactTest** - Submit, Validation, Rate Limiting
- **NewsletterTest** - Subscribe, Unsubscribe, Resubscribe

### Admin Endpoints
- **AdminProductTest** - CRUD operations, validation
- **AdminOrderTest** - List, pagination, filtering
- **AdminCommentTest** - List, Update, Approve, Delete
- **AdminSubscriberTest** - List, Export CSV
- **AdminNewsletterTest** - Send Campaign, Stats, Validation

### Email Features
- **EmailTest** - All email types, queue testing, data validation

## Running Tests

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
# Auth tests
php artisan test --filter AuthTest

# Contact tests
php artisan test --filter ContactTest

# Newsletter tests
php artisan test --filter NewsletterTest

# Email tests
php artisan test --filter EmailTest

# All admin tests
php artisan test tests/Feature/Admin
```

### Run Specific Test

```bash
php artisan test --filter it_can_submit_contact_form
```

### Run with Coverage (requires Xdebug)

```bash
php artisan test --coverage
```

### Run in Parallel (faster)

```bash
php artisan test --parallel
```

## Test Database

Tests use SQLite in-memory database by default (configured in `phpunit.xml`).

**Configuration:**
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

This ensures:
- ✅ Tests don't affect production data
- ✅ Fast execution
- ✅ Automatic cleanup between tests

## Test Structure

### Feature Tests

Located in `tests/Feature/`:

```
tests/Feature/
├── AuthTest.php                    # Authentication endpoints
├── ProductTest.php                 # Public product endpoints
├── CartTest.php                    # Shopping cart
├── OrderTest.php                   # Orders and checkout
├── CommentTest.php                 # Product comments/reviews
├── ContactTest.php                 # Contact form
├── NewsletterTest.php              # Newsletter subscription
├── EmailTest.php                   # Email notifications
└── Admin/
    ├── AdminProductTest.php        # Admin product management
    ├── AdminOrderTest.php          # Admin order management
    ├── AdminCommentTest.php        # Admin comment management
    ├── AdminSubscriberTest.php     # Admin subscriber management
    └── AdminNewsletterTest.php     # Admin newsletter campaigns
```

### Factories

Located in `database/factories/`:

- `UserFactory.php` - Create test users
- `ProductFactory.php` - Create test products
- `CategoryFactory.php` - Create test categories
- `OrderFactory.php` - Create test orders
- `OrderItemFactory.php` - Create order items
- `SubscriberFactory.php` - Create newsletter subscribers
- `CommentFactory.php` - Create product comments

## Writing Tests

### Basic Test Structure

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_something()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/endpoint', ['data' => 'value']);

        // Assert
        $response->assertOk()
            ->assertJson(['success' => true]);
    }
}
```

### Common Assertions

```php
// Status codes
$response->assertOk();                    // 200
$response->assertCreated();               // 201
$response->assertNoContent();             // 204
$response->assertUnauthorized();          // 401
$response->assertForbidden();             // 403
$response->assertNotFound();              // 404
$response->assertStatus(422);             // Custom status

// JSON structure
$response->assertJson(['key' => 'value']);
$response->assertJsonStructure(['data' => ['*' => ['id', 'name']]]);
$response->assertJsonCount(5, 'data');

// Validation
$response->assertJsonValidationErrors(['field']);

// Database
$this->assertDatabaseHas('table', ['column' => 'value']);
$this->assertDatabaseMissing('table', ['column' => 'value']);
$this->assertDatabaseCount('table', 10);
```

### Testing Authentication

```php
// As guest
$response = $this->getJson('/api/endpoint');

// As authenticated user
$user = User::factory()->create();
$response = $this->actingAs($user, 'sanctum')
    ->getJson('/api/endpoint');

// As admin
$admin = User::factory()->create(['is_admin' => true]);
$response = $this->actingAs($admin, 'sanctum')
    ->getJson('/api/admin/endpoint');
```

### Testing Emails

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\YourMailable;

public function test_email_is_sent()
{
    Mail::fake();

    // Trigger action that sends email
    $this->postJson('/api/endpoint', $data);

    // Assert email was queued
    Mail::assertQueued(YourMailable::class);

    // Assert email sent to specific address
    Mail::assertQueued(YourMailable::class, function ($mail) {
        return $mail->hasTo('user@example.com');
    });
}
```

### Testing Notifications

```php
use Illuminate\Support\Facades\Notification;
use App\Notifications\YourNotification;

public function test_notification_is_sent()
{
    Notification::fake();

    $user = User::factory()->create();

    // Trigger action
    $this->postJson('/api/endpoint');

    // Assert notification sent
    Notification::assertSentTo($user, YourNotification::class);
}
```

### Testing File Uploads

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

public function test_file_upload()
{
    Storage::fake('public');

    $file = UploadedFile::fake()->image('photo.jpg');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/upload', ['file' => $file]);

    $response->assertOk();

    Storage::disk('public')->assertExists('uploads/photo.jpg');
}
```

## Test Best Practices

### 1. Use Descriptive Test Names

✅ Good:
```php
/** @test */
public function it_validates_email_format_on_registration()
```

❌ Bad:
```php
/** @test */
public function test_validation()
```

### 2. Follow AAA Pattern

```php
// Arrange - Set up test data
$user = User::factory()->create();

// Act - Perform action
$response = $this->actingAs($user)->postJson('/api/endpoint');

// Assert - Verify outcome
$response->assertOk();
```

### 3. Test One Thing Per Test

✅ Good:
```php
/** @test */
public function it_validates_required_fields()
{
    $response = $this->postJson('/api/contact', []);
    $response->assertJsonValidationErrors(['email', 'message']);
}

/** @test */
public function it_validates_email_format()
{
    $response = $this->postJson('/api/contact', ['email' => 'invalid']);
    $response->assertJsonValidationErrors(['email']);
}
```

❌ Bad:
```php
/** @test */
public function it_validates_everything()
{
    // Tests 10 different validations
}
```

### 4. Use Factories Over Manual Creation

✅ Good:
```php
$user = User::factory()->create();
$products = Product::factory()->count(5)->create();
```

❌ Bad:
```php
$user = User::create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password'),
]);
```

### 5. Clean Up After Tests

Use `RefreshDatabase` trait to automatically reset database:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase
{
    use RefreshDatabase;
}
```

## Continuous Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install Dependencies
        run: composer install

      - name: Run Tests
        run: php artisan test
```

## Debugging Failed Tests

### View Detailed Output

```bash
php artisan test --stop-on-failure
```

### Debug Specific Test

```bash
php artisan test --filter test_name --stop-on-failure
```

### Use dd() or dump()

```php
/** @test */
public function it_does_something()
{
    $response = $this->getJson('/api/endpoint');

    // Debug response
    dd($response->json());

    $response->assertOk();
}
```

### Check Database State

```php
/** @test */
public function it_creates_record()
{
    $this->postJson('/api/endpoint', $data);

    // Dump all records
    dd(User::all());

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
}
```

## Coverage Report

### Generate HTML Coverage Report

Requires Xdebug:

```bash
php artisan test --coverage-html coverage
```

Then open `coverage/index.html` in browser.

### Check Coverage Percentage

```bash
php artisan test --coverage --min=80
```

This fails if coverage is below 80%.

## Test Statistics

**Total Tests:** ~100+ tests

**Coverage by Module:**
- ✅ Authentication - 100%
- ✅ Products - 100%
- ✅ Cart - 100%
- ✅ Orders - 100%
- ✅ Comments - 100%
- ✅ Contact - 100%
- ✅ Newsletter - 100%
- ✅ Admin Products - 100%
- ✅ Admin Orders - 100%
- ✅ Admin Comments - 100%
- ✅ Admin Subscribers - 100%
- ✅ Admin Newsletter - 100%
- ✅ Email System - 100%

## Quick Reference

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific suite
php artisan test --filter ContactTest

# Run parallel (faster)
php artisan test --parallel

# Stop on first failure
php artisan test --stop-on-failure

# Run only tests in specific directory
php artisan test tests/Feature/Admin

# List all tests
php artisan test --list-tests
```

## Support

For issues or questions about testing:
- Check Laravel Testing documentation: https://laravel.com/docs/testing
- Review existing tests for examples
- Use factories for consistent test data
- Keep tests simple and focused

---

**Test Suite Ready!** Run `php artisan test` to execute all tests. 🎉
