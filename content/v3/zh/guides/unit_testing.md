# 在 Flight PHP 中使用 PHPUnit 进行单元测试

本指南介绍在 Flight PHP 中使用 [PHPUnit](https://phpunit.de/) 进行单元测试，针对初学者，帮助他们理解为什么单元测试很重要，以及如何实际应用它。我们将专注于测试 *行为*——确保您的应用程序按照预期运行，例如发送电子邮件或保存记录，而不是琐碎的计算。我们将从一个简单的 [route handler](/learn/routing) 开始，然后逐步过渡到一个更复杂的 [controller](/learn/routing)，并结合 [dependency injection](/learn/dependency-injection-container) (DI) 和模拟第三方服务。

## 为什么进行单元测试？

单元测试确保您的代码行为符合预期，在错误进入生产环境前捕获它们。在 Flight 中，这一点特别有价值，因为其轻量级路由和灵活性可能导致复杂的交互。对于独立开发者或团队，单元测试充当安全网，记录预期行为，并在您稍后重新审视代码时防止回归。它们还改善设计：难以测试的代码通常表示类过于复杂或紧密耦合。

与简单示例不同（如测试 `x * y = z`），我们将专注于真实世界的行为，例如验证输入、保存数据或触发操作如发送电子邮件。我们的目标是让测试变得易于接近且有意义。

## 一般指导原则

1. **测试行为，而非实现**：关注结果（如“电子邮件已发送”或“记录已保存”），而不是内部细节。这使测试在重构时更稳健。
2. **停止使用 `Flight::`**： Flight 的静态方法非常方便，但会使测试变得困难。您应该习惯使用 `$app` 变量，从 `$app = Flight::app();` 获取。`$app` 拥有与 `Flight::` 相同的全部方法。您仍然可以使用 `$app->route()` 或 `$this->app->json()` 在控制器中。此外，您应该使用真实的 Flight 路由器，通过 `$router = $app->router()`，然后使用 `$router->get()`、` $router->post()`、` $router->group()` 等。参见 [Routing](/learn/routing)。
3. **保持测试快速**：快速测试鼓励频繁执行。避免在单元测试中使用缓慢操作，如数据库调用。如果您有一个缓慢的测试，这表明您正在编写集成测试，而不是单元测试。集成测试涉及真实数据库、真实 HTTP 调用或真实电子邮件发送等，它们有其位置，但它们缓慢且可能不稳定，意思是它们有时会因未知原因失败。
4. **使用描述性名称**：测试名称应清楚描述被测试的行为。这改善了可读性和可维护性。
5. **避免使用全局变量**：尽量减少 `$app->set()` 和 `$app->get()` 的使用，因为它们像全局状态一样，在每个测试中都需要模拟。首选 DI 或 DI 容器（参见 [Dependency Injection Container](/learn/dependency-injection-container)）。即使使用 `$app->map()` 方法也是“全局”的，应避免使用。使用像 [flightphp/session](https://github.com/flightphp/session) 这样的会话库，以便在测试中模拟会话对象。**不要** 直接调用 [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php)，因为这会将全局变量注入您的代码，使其难以测试。
6. **使用依赖注入**：将依赖项（如 [`PDO`](https://www.php.net/manual/en/class.pdo.php)、邮件发送器）注入控制器，以隔离逻辑并简化模拟。如果一个类有太多依赖项，请考虑将其重构为更小的类，每个类都有单一责任，遵循 [SOLID principles](https://en.wikipedia.org/wiki/SOLID)。
7. **模拟第三方服务**：模拟数据库、HTTP 客户端（cURL）或电子邮件服务，以避免外部调用。只测试一到两层深，但让核心逻辑运行。例如，如果您的应用程序发送短信，您 **不** 希望每次运行测试时真正发送短信，因为这会产生费用（并且会更慢）。相反，模拟短信服务，只验证您的代码是否使用正确参数调用了短信服务。
8. **追求高覆盖率，而非完美**：100% 行覆盖率很好，但它并不意味着代码中的所有内容都按应有的方式进行了测试（请研究 [branch/path coverage in PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)）。优先考虑关键行为（如用户注册、API 响应和捕获失败响应）。
9. **为路由使用控制器**：在路由定义中，使用控制器而非闭包。默认情况下，`flight\Engine $app` 会通过构造函数注入到每个控制器。在测试中，使用 `$app = new Flight\Engine()` 在测试中实例化 Flight，将其注入控制器，然后直接调用方法（如 `$controller->register()`）。参见 [Extending Flight](/learn/extending) 和 [Routing](/learn/routing)。
10. **选择一种模拟风格并坚持使用**：PHPUnit 支持多种模拟风格（如 prophecy、内置模拟），或者您可以使用匿名类，它们有自己的优势，如代码补全，如果您更改方法定义，它们会中断。只需在测试中保持一致。参见 [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles)。
11. **为希望在子类中测试的方法/属性使用 `protected` 可见性**：这允许您在测试子类中覆盖它们，而不需将其设为 public，这在匿名类模拟中特别有用。

## 设置 PHPUnit

首先，在您的 Flight PHP 项目中使用 Composer 设置 [PHPUnit](https://phpunit.de/) 以便轻松测试。参见 [PHPUnit Getting Started guide](https://phpunit.readthedocs.io/en/12.3/installation.html) 获取更多细节。

1. 在您的项目目录中运行：
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
			   $responseArray = ['status' => 'error', 'message' => '无效电子邮件'];
		   } else {
			   $responseArray = ['status' => 'success', 'message' => '有效电子邮件'];
		   }

		   $this->app->json($responseArray);
	   }
   }
   ```

2. 创建一个 `tests` 目录，在项目根目录中用于测试文件。

3. 在 `composer.json` 中添加一个测试脚本以方便使用：
   ```json
   // other composer.json content
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. 在根目录创建一个 `phpunit.xml` 文件：
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

现在，当您的测试构建完成后，您可以运行 `composer test` 来执行测试。

## 测试一个简单的路由处理程序

让我们从一个基本的 [route](/learn/routing) 开始，它验证用户的电子邮件输入。我们将测试其行为：对于有效电子邮件返回成功消息，对于无效电子邮件返回错误。对于电子邮件验证，我们使用 [`filter_var`](https://www.php.net/manual/en/function.filter-var.php)。

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // 模拟 POST 数据
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
		$request->data->email = 'invalid-email'; // 模拟 POST 数据
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
- 我们使用请求类模拟 POST 数据。不要使用全局变量如 `$_POST`、`$_GET` 等，因为这会使测试更复杂（您必须始终重置这些值，否则其他测试可能会失败）。
- 所有控制器默认会注入 `flight\Engine` 实例，即使没有设置 DI 容器。这使直接测试控制器更容易。
- 完全没有使用 `Flight::`，使代码更容易测试。
- 测试验证行为：对于有效/无效电子邮件，正确状态和消息。

运行 `composer test` 来验证路由行为符合预期。有关 Flight 中的 [requests](/learn/requests) 和 [responses](/learn/responses)，参见相关文档。

## 使用依赖注入进行可测试控制器

对于更复杂的场景，使用 [dependency injection](/learn/dependency-injection-container) (DI) 来使控制器可测试。避免 Flight 的全局变量（如 `Flight::set()`、`Flight::map()`、`Flight::register()`），因为它们像全局状态一样，在每个测试中都需要模拟。相反，使用 Flight 的 DI 容器，如 [DICE](https://github.com/Level-2/Dice)、[PHP-DI](https://php-di.org/) 或手动 DI。

让我们使用 [`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper) 而不是原始 PDO。这个包装器更容易模拟和单元测试！

这是一个保存用户到数据库并发送欢迎电子邮件的控制器：

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
			// 添加 return 这里有助于单元测试停止执行
			return $this->app->jsonHalt(['status' => 'error', 'message' => '无效电子邮件']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => '用户已注册']);
    }
}
```

**关键点**：
- 控制器依赖于 [`PdoWrapper`](/awesome-plugins/pdo-wrapper) 实例和 `MailerInterface`（一个模拟的第三方电子邮件服务）。
- 依赖项通过构造函数注入，避免使用全局变量。

### 使用模拟测试控制器

现在，让我们测试 `UserController` 的行为：验证电子邮件、保存到数据库并发送电子邮件。我们将模拟数据库和邮件发送器以隔离控制器。

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// 有时混合模拟风格是必要的
		// 这里我们使用 PHPUnit 的内置模拟为 PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// 使用匿名类模拟 PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// 当我们这样模拟时，我们不会真正进行数据库调用。
			// 我们可以进一步设置这个来模拟失败等。
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
        $this->assertEquals('用户已注册', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }

    public function testInvalidEmailSkipsSaveAndEmail() {
		 $mockDb = new class() extends PdoWrapper {
			// 一个空构造函数绕过父构造函数
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('不应被调用');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('不应被调用');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// 需要映射 jsonHalt 以避免退出
		$app->map('jsonHalt', function($data) use ($app) {
			$app->json($data, 400);
		});
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('无效电子邮件', $result['message']);
    }
}
```

**关键点**：
- 我们模拟 `PdoWrapper` 和 `MailerInterface` 以避免真实数据库或电子邮件调用。
- 测试验证行为：有效电子邮件触发数据库插入和电子邮件发送；无效电子邮件跳过两者。
- 模拟第三方依赖项（如 `PdoWrapper`、`MailerInterface`），让控制器的逻辑运行。

### 模拟过多

小心不要模拟太多您的代码。让我用我们的 `UserController` 举一个例子来说明为什么这可能是个坏主意。我们将把那个检查改为一个名为 `isEmailValid` 的方法（使用 `filter_var`），并将其他新添加的部分改为一个名为 `registerUser` 的单独方法。

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
			// 添加 return 这里有助于单元测试停止执行
			return $this->app->jsonHalt(['status' => 'error', 'message' => '无效电子邮件']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => '用户已注册']);
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

现在是一个过度模拟的单元测试，它实际上什么都不测试：

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// 我们在这里跳过额外的依赖注入，因为它“容易”
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// 绕过构造中的依赖
			public function __construct($app) {
				$this->app = $app;
			}

			// 我们将强制这个为有效。
			protected function isEmailValid($email) {
				return true; // 始终返回 true，绕过真实验证
			}

			// 绕过实际的 DB 和邮件发送器调用
			protected function registerUser($email) {
				return false;
			}
		};
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('用户已注册', $result['message']);
    }
}
```

万岁，我们有单元测试，它们通过了！但是等待，如果我实际更改 `isEmailValid` 或 `registerUser` 的内部工作？我的测试仍然会通过，因为我已经模拟了所有功能。让我展示一下我的意思。

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... 其他方法 ...

	protected function isEmailValid($email) {
		// 更改逻辑
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// 现在它应该只针对特定域名
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

如果我运行上面的单元测试，它们仍然通过！但因为我没有测试行为（让部分代码实际运行），我可能在生产环境中潜藏了一个错误。测试应该修改以适应新行为，并测试行为不符合预期的情况。

## 完整示例

您可以在 GitHub 上找到一个完整的 Flight PHP 项目示例，其中包含单元测试：[n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide)。
对于更多指南，参见 [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) 和 [Troubleshooting](/learn/troubleshooting)。

## 常见陷阱

- **过度模拟**：不要模拟每个依赖项；让一些逻辑（如控制器验证）运行以测试真实行为。参见 [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles)。
- **全局状态**：大量使用全局 PHP 变量（如 [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php)、[`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)）会使测试脆弱。同理，使用 `Flight::` 也是如此。将其重构为显式传递依赖项。
- **复杂设置**：如果测试设置很繁琐，您的类可能有太多依赖项或责任，违反了 [SOLID principles](https://en.wikipedia.org/wiki/SOLID)。

## 通过单元测试扩展

单元测试在大型项目或几个月后重新审视代码时大放异彩。它们记录行为并捕获回归，节省您重新学习应用程序的时间。对于独立开发者，测试关键路径（如用户注册、支付处理）。对于团队，测试确保贡献行为一致。参见 [Why Frameworks?](/learn/why-frameworks) 了解使用框架和测试的好处。

将您自己的测试技巧贡献到 Flight PHP 文档仓库！

_由 [n0nag0n](https://github.com/n0nag0n) 撰写 2025_