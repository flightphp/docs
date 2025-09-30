# 使用 PHPUnit 在 Flight PHP 中进行单元测试

本指南介绍了使用 [PHPUnit](https://phpunit.de/) 在 Flight PHP 中进行单元测试，针对希望了解*为什么*单元测试重要以及如何实际应用它的初学者。我们将重点关注测试*行为*——确保您的应用程序按预期执行，例如发送电子邮件或保存记录——而不是琐碎的计算。我们将从一个简单的 [route handler](/learn/routing) 开始，逐步推进到一个更复杂的 [controller](/learn/routing)，并结合 [dependency injection](/learn/dependency-injection-container) (DI) 和模拟第三方服务。

## 为什么进行单元测试？

单元测试确保您的代码按预期行为，在问题到达生产环境之前捕获 bug。在 Flight 中，这尤其有价值，因为轻量级路由和灵活性可能导致复杂的交互。对于独行开发者或团队，单元测试充当安全网，记录预期行为，并在您稍后重访代码时防止回归。它们还能改善设计：难以测试的代码通常表明类过于复杂或耦合紧密。

与简单示例（例如，测试 `x * y = z`）不同，我们将重点关注现实世界的行为，例如验证输入、保存数据或触发电子邮件等操作。我们的目标是使测试易于接近且有意义。

## 一般指导原则

1. **测试行为，而不是实现**：关注结果（例如，“电子邮件已发送”或“记录已保存”），而不是内部细节。这使测试在重构时更健壮。
2. **停止使用 `Flight::`**：Flight 的静态方法非常方便，但会使测试变得困难。您应该习惯使用 `$app = Flight::app();` 中的 `$app` 变量。`$app` 具有与 `Flight::` 相同的全部方法。您仍然可以在控制器中使用 `$app->route()` 或 `$this->app->json()` 等。您还应该使用真实的 Flight 路由器 `$router = $app->router()`，然后可以使用 `$router->get()`、`$router->post()`、`$router->group()` 等。请参阅 [Routing](/learn/routing)。
3. **保持测试快速**：快速测试鼓励频繁执行。避免在单元测试中使用慢速操作，如数据库调用。如果您有一个慢速测试，这表明您正在编写集成测试，而不是单元测试。集成测试涉及实际的数据库、实际的 HTTP 调用、实际的电子邮件发送等。它们有其位置，但它们缓慢且可能不稳定，意味着它们有时会因未知原因失败。
4. **使用描述性名称**：测试名称应清楚描述被测试的行为。这提高了可读性和可维护性。
5. **像瘟疫一样避免全局变量**：最小化 `$app->set()` 和 `$app->get()` 的使用，因为它们像全局状态一样，需要在每个测试中模拟。优先使用 DI 或 DI 容器（请参阅 [Dependency Injection Container](/learn/dependency-injection-container)）。即使使用 `$app->map()` 方法在技术上也是“全局”的，应避免使用 DI 替代。使用会话库如 [flightphp/session](https://github.com/flightphp/session)，以便在测试中模拟会话对象。**不要**在您的代码中直接调用 [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php)，因为这会将全局变量注入您的代码，使其难以测试。
6. **使用依赖注入**：将依赖项（例如，[`PDO`](https://www.php.net/manual/en/class.pdo.php)、邮件程序）注入控制器，以隔离逻辑并简化模拟。如果您的类有太多依赖项，请考虑将其重构为更小的类，每个类都有单一职责，遵循 [SOLID 原则](https://en.wikipedia.org/wiki/SOLID)。
7. **模拟第三方服务**：模拟数据库、HTTP 客户端（cURL）或电子邮件服务，以避免外部调用。测试一到两层深度，但让您的核心逻辑运行。例如，如果您的应用发送短信，您**不**希望每次运行测试时都真正发送短信，因为那些费用会累积（而且会更慢）。相反，模拟短信服务，并仅验证您的代码以正确的参数调用了短信服务。
8. **追求高覆盖率，而不是完美**：100% 行覆盖率很好，但它并不意味着您的代码中的一切都按应有的方式进行了测试（请继续研究 [PHPUnit 中的分支/路径覆盖率](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)）。优先考虑关键行为（例如，用户注册、API 响应和捕获失败响应）。
9. **为路由使用控制器**：在您的路由定义中，使用控制器而不是闭包。默认情况下，`flight\Engine $app` 通过构造函数注入到每个控制器中。在测试中，使用 `$app = new Flight\Engine()` 在测试中实例化 Flight，将其注入到您的控制器中，并直接调用方法（例如，`$controller->register()`）。请参阅 [Extending Flight](/learn/extending) 和 [Routing](/learn/routing)。
10. **选择一种模拟风格并坚持使用**：PHPUnit 支持几种模拟风格（例如，prophecy、内置模拟），或者您可以使用匿名类，它们有自己的好处，如代码补全、如果您更改方法定义则中断等。只需在您的测试中保持一致。请参阅 [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles)。
11. **为要在子类中测试的方法/属性使用 `protected` 可见性**：这允许您在测试子类中覆盖它们，而无需将它们设为 public，这对于匿名类模拟特别有用。

## 设置 PHPUnit

首先，在您的 Flight PHP 项目中使用 Composer 设置 [PHPUnit](https://phpunit.de/) 以进行轻松测试。请参阅 [PHPUnit 入门指南](https://phpunit.readthedocs.io/en/12.3/installation.html) 以获取更多细节。

1. 在您的项目目录中运行：
   ```bash
   composer require --dev phpunit/phpunit
   ```
   这将安装最新的 PHPUnit 作为开发依赖项。

2. 在您的项目根目录中创建一个 `tests` 目录，用于测试文件。

3. 在 `composer.json` 中添加一个测试脚本以方便使用：
   ```json
   // other composer.json content
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. 在根目录中创建一个 `phpunit.xml` 文件：
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

现在，当您的测试构建时，您可以运行 `composer test` 来执行测试。

## 测试简单的路由处理器

让我们从一个基本的 [route](/learn/routing) 开始，它验证用户的电子邮件输入。我们将测试其行为：对于有效电子邮件返回成功消息，对于无效电子邮件返回错误。对于电子邮件验证，我们使用 [`filter_var`](https://www.php.net/manual/en/function.filter-var.php)。

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

要测试此内容，请创建一个测试文件。请参阅 [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) 以获取更多关于测试结构的信息：

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

**关键点**：
- 我们使用请求类模拟 POST 数据。不要使用全局变量如 `$_POST`、`$_GET` 等，因为这会使测试更复杂（您必须始终重置这些值，否则其他测试可能会崩溃）。
- 所有控制器默认都会注入 `flight\Engine` 实例，即使没有设置 DIC 容器。这使直接测试控制器变得更容易。
- 完全没有使用 `Flight::`，使代码更容易测试。
- 测试验证行为：有效/无效电子邮件的正确状态和消息。

运行 `composer test` 以验证路由按预期行为。对于 Flight 中的 [requests](/learn/requests) 和 [responses](/learn/responses)，请参阅相关文档。

## 使用依赖注入创建可测试的控制器

对于更复杂的场景，使用 [dependency injection](/learn/dependency-injection-container) (DI) 来使控制器可测试。避免 Flight 的全局变量（例如，`Flight::set()`、`Flight::map()`、`Flight::register()`），因为它们像全局状态一样，需要为每个测试模拟。相反，使用 Flight 的 DI 容器、[DICE](https://github.com/Level-2/Dice)、[PHP-DI](https://php-di.org/) 或手动 DI。

让我们使用 [`flight\database\PdoWrapper`](/learn/pdo-wrapper) 而不是原始 PDO。这个包装器更容易模拟和单元测试！

这是一个将用户保存到数据库并发送欢迎电子邮件的控制器：

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

**关键点**：
- 控制器依赖于 [`PdoWrapper`](/learn/pdo-wrapper) 实例和 `MailerInterface`（一个假想的第三方电子邮件服务）。
- 依赖项通过构造函数注入，避免全局变量。

### 使用模拟测试控制器

现在，让我们测试 `UserController` 的行为：验证电子邮件、保存到数据库并发送电子邮件。我们将模拟数据库和邮件程序以隔离控制器。

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

**关键点**：
- 我们模拟 `PdoWrapper` 和 `MailerInterface` 以避免真实的数据库或电子邮件调用。
- 测试验证行为：有效电子邮件触发数据库插入和电子邮件发送；无效电子邮件跳过两者。
- 模拟第三方依赖项（例如，`PdoWrapper`、`MailerInterface`），让控制器的逻辑运行。

### 模拟过多

小心不要模拟太多您的代码。下面让我给您一个例子，说明为什么这可能是坏事，使用我们的 `UserController`。我们将那个检查改为一个名为 `isEmailValid` 的方法（使用 `filter_var`），其他新添加的内容改为一个名为 `registerUser` 的单独方法。

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

现在是一个过度模拟的单元测试，它实际上没有测试任何东西：

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

太好了，我们有单元测试，它们通过了！但是等等，如果我实际更改 `isEmailValid` 或 `registerUser` 的内部工作方式呢？我的测试仍然会通过，因为我模拟了所有功能。让我向您展示我的意思。

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

如果我运行上面的单元测试，它们仍然会通过！但因为我没有针对行为进行测试（实际让一些代码运行），我可能在生产环境中编码了一个等待发生的 bug。测试应该修改以考虑新行为，以及当行为不是我们预期的相反情况。

## 完整示例

您可以在 GitHub 上找到一个带有单元测试的完整 Flight PHP 项目示例：[n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide)。
对于更深入的理解，请参阅 [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles)。

## 常见陷阱

- **过度模拟**：不要模拟每个依赖项；让一些逻辑（例如，控制器验证）运行以测试真实行为。请参阅 [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles)。
- **全局状态**：大量使用全局 PHP 变量（例如，[`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php)、[`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)）会使测试脆弱。`Flight::` 也是如此。重构以显式传递依赖项。
- **复杂设置**：如果测试设置繁琐，您的类可能有太多依赖项或职责，违反 [SOLID 原则](/learn/unit-testing-and-solid-principles)。

## 使用单元测试扩展

单元测试在大项目中或在数月后重访代码时大放异彩。它们记录行为并捕获回归，从而节省您重新学习应用程序的时间。对于独行开发者，测试关键路径（例如，用户注册、支付处理）。对于团队，测试确保贡献行为一致。请参阅 [Why Frameworks?](/learn/why-frameworks) 以获取更多关于使用框架和测试的好处的信息。

向 Flight PHP 文档仓库贡献您自己的测试提示！

_由 [n0nag0n](https://github.com/n0nag0n) 撰写 2025_