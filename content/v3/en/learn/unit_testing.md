# Unit Testing

## Overview

Unit testing in Flight helps you ensure your application behaves as expected, catch bugs early, and make your codebase easier to maintain. Flight is designed to work smoothly with [PHPUnit](https://phpunit.de/), the most popular PHP testing framework.

## Understanding

Unit tests check the behavior of small pieces of your application (like controllers or services) in isolation. In Flight, this means testing how your routes, controllers, and logic respond to different inputs—without relying on global state or real external services.

Key principles:
- **Test behavior, not implementation:** Focus on what your code does, not how it does it.
- **Avoid global state:** Use dependency injection instead of `Flight::set()` or `Flight::get()`.
- **Mock external services:** Replace things like databases or mailers with test doubles.
- **Keep tests fast and focused:** Unit tests should not hit real databases or APIs.

## Basic Usage

### Setting Up PHPUnit

1. Install PHPUnit with Composer:
   ```bash
   composer require --dev phpunit/phpunit
   ```
2. Create a `tests` directory in your project root.
3. Add a test script to your `composer.json`:
   ```json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```
4. Create a `phpunit.xml` file:
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <phpunit bootstrap="vendor/autoload.php">
       <testsuites>
           <testsuite name="Flight Tests">
               <directory>tests</directory>
           </testsuite>
       </testsuites>
   </phpunit>
   ```

Now you can run your tests with `composer test`.

### Testing a Simple Route Handler

Suppose you have a route that validates an email:

```php
// index.php
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php
class UserController {
    protected $app;
    public function __construct(flight\Engine $app) {
        $this->app = $app;
    }
    public function register() {
        $email = $this->app->request()->data->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->app->json(['status' => 'error', 'message' => 'Invalid email']);
        }
        return $this->app->json(['status' => 'success', 'message' => 'Valid email']);
    }
}
```

A simple test for this controller:

```php
use PHPUnit\Framework\TestCase;
use flight\Engine;

class UserControllerTest extends TestCase {
    public function testValidEmailReturnsSuccess() {
        $app = new Engine();
        $app->request()->data->email = 'test@example.com';
        $controller = new UserController($app);
        $controller->register();
        $response = $app->response()->getBody();
        $output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
        $app = new Engine();
        $app->request()->data->email = 'invalid-email';
        $controller = new UserController($app);
        $controller->register();
        $response = $app->response()->getBody();
        $output = json_decode($response, true);
        $this->assertEquals('error', $output['status']);
        $this->assertEquals('Invalid email', $output['message']);
    }
}
```

**Tips:**
- Simulate POST data using `$app->request()->data`.
- Avoid using `Flight::` statics in your tests—use the `$app` instance.

### Using Dependency Injection for Testable Controllers

Inject dependencies (like the database or mailer) into your controllers to make them easy to mock in tests:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;
    public function __construct($app, $db, $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }
    public function register() {
        $email = $this->app->request()->data->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->app->json(['status' => 'error', 'message' => 'Invalid email']);
        }
        $this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
        $this->mailer->sendWelcome($email);
        return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

And a test with mocks:

```php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
        $mockDb = $this->createMock(flight\database\PdoWrapper::class);
        $mockDb->method('runQuery')->willReturn(true);
        $mockMailer = new class {
            public $sentEmail = null;
            public function sendWelcome($email) { $this->sentEmail = $email; return true; }
        };
        $app = new flight\Engine();
        $app->request()->data->email = 'test@example.com';
        $controller = new UserController($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }
}
```

## Advanced Usage

- **Mocking:** Use PHPUnit's built-in mocks or anonymous classes to replace dependencies.
- **Testing controllers directly:** Instantiate controllers with a new `Engine` and mock dependencies.
- **Avoid over-mocking:** Let real logic run where possible; only mock external services.

## See Also

- [Unit Testing Guide](/guides/unit-testing) - A comprehensive guide on unit testing best practices.
- [Dependency Injection Container](/learn/dependency-injection-container) - How to use DICs to manage dependencies and improve testability.
- [Extending](/learn/extending) - How to add your own helpers or override core classes.
- [PDO Wrapper](/learn/pdo-wrapper) - Simplifies database interactions and is easier to mock in tests.
- [Requests](/learn/requests) - Handling HTTP requests in Flight.
- [Responses](/learn/responses) - Sending responses to users.
- [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) - Learn how SOLID principles can improve your unit tests.

## Troubleshooting

- Avoid using global state (`Flight::set()`, `$_SESSION`, etc.) in your code and tests.
- If your tests are slow, you may be writing integration tests—mock external services to keep unit tests fast.
- If test setup is complex, consider refactoring your code to use dependency injection.

## Changelog

- v3.15.0 - Added examples for dependency injection and mocking.