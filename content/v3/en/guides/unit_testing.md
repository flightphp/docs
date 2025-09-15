# Unit Testing in Flight PHP with PHPUnit

This guide introduces unit testing in Flight PHP using [PHPUnit](https://phpunit.de/), aimed at beginners who want to understand *why* unit testing matters and how to apply it practically. We'll focus on testing *behavior*—ensuring your application does what you expect, like sending an email or saving a record—rather than trivial calculations. We'll start with a simple [route handler](/learn/routing) and progress to a more complex [controller](/learn/routing), incorporating [dependency injection](/learn/dependency-injection-container) (DI) and mocking third-party services.

## Why Unit Test?

Unit testing ensures your code behaves as expected, catching bugs before they reach production. It’s especially valuable in Flight, where lightweight routing and flexibility can lead to complex interactions. For solo developers or teams, unit tests act as a safety net, documenting expected behavior and preventing regressions when you revisit code later. They also improve design: hard-to-test code often signals overly complex or tightly coupled classes.

Unlike simplistic examples (e.g., testing `x * y = z`), we’ll focus on real-world behaviors, such as validating input, saving data, or triggering actions like emails. Our goal is to make testing approachable and meaningful.

## General Guiding Principles

1. **Test Behavior, Not Implementation**: Focus on outcomes (e.g., “email sent” or “record saved”) rather than internal details. This makes tests robust against refactoring.
2. **Stop using `Flight::`**: Flight’s static methods are terribly convenient, but make testing hard. You should get used to using the `$app` variable from `$app = Flight::app();`. `$app` has all the same methods that `Flight::` does. You'll still be able to use `$app->route()` or `$this->app->json()` in your controller etc. You also should use the real Flight router with `$router = $app->router()` and then you can use `$router->get()`, `$router->post()`, `$router->group()` etc. See [Routing](/learn/routing).
3. **Keep Tests Fast**: Fast tests encourage frequent execution. Avoid slow operations like database calls in unit tests. If you have a slow test, it's a sign you are writing an integration test, not a unit test. Integration tests are when you actually involve real databases, real HTTP calls, real email sending etc. They have their place, but they are slow and can be flaky, meaning they sometimes fail for an unknown reason. 
4. **Use Descriptive Names**: Test names should clearly describe the behavior being tested. This improves readability and maintainability.
5. **Avoid Globals Like the Plague**: Minimize `$app->set()` and `$app->get()` usage, as they act like global state, requiring mocks in every test. Prefer DI or a DI container (see [Dependency Injection Container](/learn/dependency-injection-container)). Even using the `$app->map()` method is technically a "global" and should be avoided in favor of DI. Use a session library such as [flightphp/session](https://github.com/flightphp/session) so that you can mock the session object in your tests. **Do not** call [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) directly in your code as that is injecting a global variable into your code, making it hard to test.
6. **Use Dependency Injection**: Inject dependencies (e.g., [`PDO`](https://www.php.net/manual/en/class.pdo.php), mailers) into controllers to isolate logic and simplify mocking. If you have a class with too many dependencies, consider refactoring it into smaller classes that each have a single responsibility following [SOLID principles](https://en.wikipedia.org/wiki/SOLID).
7. **Mock Third-Party Services**: Mock databases, HTTP clients (cURL), or email services to avoid external calls. Test one or two layers deep, but let your core logic run. For example, if your app sends a text message, you do **NOT** want to really send a text message every time you run your tests cause those charges will add up (and it'll be slower). Instead, mock the text message service and just verify that your code called the text message service with the right parameters.
8. **Aim for High Coverage, Not Perfection**: 100% line coverage is good, but it doesn't actually mean that everything in your code is tested the way it should be (go ahead and research [branch/path coverage in PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Prioritize critical behaviors (e.g., user registration, API responses and capturing failed responses).
9. **Use Controllers for Routes**: In your route definitions, use controllers not closures. The `flight\Engine $app` is injected into every controller via the constructor by default. In tests, use `$app = new Flight\Engine()` to instantiate Flight within a test, inject it into your controller, and call methods directly (e.g., `$controller->register()`). See [Extending Flight](/learn/extending) and [Routing](/learn/routing).
10. **Pick a mocking style and stick with it**: PHPUnit supports several mocking styles (e.g., prophecy, built-in mocks), or you can use anonymous classes which have their own benefits like code completion, breaking if you change the method definition, etc. Just be consistent across your tests. See [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Use `protected` visibility for methods/properties you want to test in subclasses**: This allows you to override them in test subclasses without making them public, this is especially useful for anonymous class mocks.

## Setting Up PHPUnit

First, set up [PHPUnit](https://phpunit.de/) in your Flight PHP project using Composer for easy testing. See the [PHPUnit Getting Started guide](https://phpunit.readthedocs.io/en/12.3/installation.html) for more details.

1. In your project directory, run:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   This installs the latest PHPUnit as a development dependency.

2. Create a `tests` directory in your project root for test files.

3. Add a test script to `composer.json` for convenience:
   ```json
   // other composer.json content
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. Create a `phpunit.xml` file in the root:
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

Now when your tests are built, you can run `composer test` to execute tests.

## Testing a Simple Route Handler

Let’s start with a basic [route](/learn/routing) that validates a user’s email input. We’ll test its behavior: returning a success message for valid emails and an error for invalid ones. For email validation, we use [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

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
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => 'Invalid email'];
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Valid email'];
		}

		$this->app->json($responseArray);
	}
}
```

To test this, create a test file. See [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) for more on structuring tests:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // Simulate POST data
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email'; // Simulate POST data
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);
	}
}
```

**Key Points**:
- We simulate POST data using the request class. Do not use globals like `$_POST`, `$_GET`, etc as it makes testing more complicated (you have to always reset those values or other tests might blow up).
- All controllers by default will have the `flight\Engine` instance injected into them even without a DIC container being set up. This makes it much easier to test controllers directly.
- There's no `Flight::` usage at all, making the code easier to test.
- Tests verify behavior: correct status and message for valid/invalid emails.

Run `composer test` to verify the route behaves as expected. For more on [requests](/learn/requests) and [responses](/learn/responses) in Flight, see the relevant docs.

## Using Dependency Injection for Testable Controllers

For more complex scenarios, use [dependency injection](/learn/dependency-injection-container) (DI) to make controllers testable. Avoid Flight’s globals (e.g., `Flight::set()`, `Flight::map()`, `Flight::register()`) as they act like global state, requiring mocks for every test. Instead, use Flight’s DI container, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) or manual DI.

Let’s use [`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper) instead of raw PDO. This wrapper is much easier to mock and unit test!

Here’s a controller that saves a user to a database and sends a welcome email:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**Key Points**:
- The controller depends on a [`PdoWrapper`](/awesome-plugins/pdo-wrapper) instance and a `MailerInterface` (a pretend third-party email service).
- Dependencies are injected via the constructor, avoiding globals.

### Testing the Controller with Mocks

Now, let’s test the `UserController`’s behavior: validating emails, saving to the database, and sending emails. We’ll mock the database and mailer to isolate the controller.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Sometimes mixing mocking styles is necessary
		// Here we use PHPUnit's built-in mock for PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Using an anonymous class to mock PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// When we mock it this way, we are not really making a database call.
			// We can further setup this to alter the PDOStatement mock to simulate failures, etc.
            public function runQuery(string $sql, array $params = []): PDOStatement {
                return $this->statementMock;
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                $this->sentEmail = $email;
                return true;	
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }

    public function testInvalidEmailSkipsSaveAndEmail() {
		 $mockDb = new class() extends PdoWrapper {
			// An empty constructor bypasses the parent constructor
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Should not be called');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Should not be called');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Need to map jsonHalt to avoid exiting
		$app->map('jsonHalt', function($data) use ($app) {
			$app->json($data, 400);
		});
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid email', $result['message']);
    }
}
```

**Key Points**:
- We mock `PdoWrapper` and `MailerInterface` to avoid real database or email calls.
- Tests verify behavior: valid emails trigger database inserts and email sends; invalid emails skip both.
- Mock third-party dependencies (e.g., `PdoWrapper`, `MailerInterface`), letting the controller’s logic run.

### Mocking too much

Be careful not to mock too much of your code. Let me give you an example below about why this might be a bad thing using our `UserController`. We'll change that check into a method called `isEmailValid` (using `filter_var`) and the other new additions into a separate method called `registerUser`.

```php
use flight\database\PdoWrapper;
use flight\Engine;

// UserControllerDICV2.php
class UserControllerDICV2 {
	protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!$this->isEmailValid($email)) {
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'User registered']);
    }

	protected function isEmailValid($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	protected function registerUser($email) {
		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);
	}
}
```

And now the overmocked unit test that doesn't actually test anything:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// we are skipping the extra dependency injection here cause it's "easy"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Bypass the deps in the construct
			public function __construct($app) {
				$this->app = $app;
			}

			// We'll just force this to be valid.
			protected function isEmailValid($email) {
				return true; // Always return true, bypassing real validation
			}

			// Bypass the actual DB and mailer calls
			protected function registerUser($email) {
				return false;
			}
		};
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
    }
}
```

Hooray we have unit tests and they are passing! But wait, what if I actually change the internal workings of `isEmailValid` or `registerUser`? My tests will still pass because I've mocked out all the functionality. Let me show you what I mean.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... other methods ...

	protected function isEmailValid($email) {
		// Changed logic
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Now it should only have a specific domain
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

If I ran my above unit tests, they still pass! But because I wasn't testing for behavior (actually letting some of the code run), I have potentially coded a bug waiting to happen in production. The test should be modified to account for the new behavior, and also the opposite of when the behavior is not what we expect.

## Full Example

You can find a full example of a Flight PHP project with unit tests on GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
For more guides, see [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) and [Troubleshooting](/learn/troubleshooting).

## Common Pitfalls

- **Over-Mocking**: Don’t mock every dependency; let some logic (e.g., controller validation) run to test real behavior. See [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Global State**: Using global PHP variables (e.g., [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) heavily makes tests brittle. Same goes with `Flight::`. Refactor to pass dependencies explicitly.
- **Complex Setup**: If test setup is cumbersome, your class may have too many dependencies or responsibilities violating the [SOLID principles](https://en.wikipedia.org/wiki/SOLID).

## Scaling with Unit Tests

Unit tests shine in larger projects or when revisiting code after months. They document behavior and catch regressions, saving you from re-learning your app. For solo devs, test critical paths (e.g., user signup, payment processing). For teams, tests ensure consistent behavior across contributions. See [Why Frameworks?](/learn/why-frameworks) for more on the benefits of using frameworks and tests.

Contribute your own testing tips to the Flight PHP documentation repository!

_Written by [n0nag0n](https://github.com/n0nag0n) 2025_