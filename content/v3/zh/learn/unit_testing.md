# 单元测试

## 概述

Flight 中的单元测试帮助您确保应用程序按预期运行，早发现错误，并使您的代码库更容易维护。Flight 设计为与 [PHPUnit](https://phpunit.de/) 无缝协作，这是最受欢迎的 PHP 测试框架。

## 理解

单元测试检查应用程序的小部分行为（如控制器或服务）在隔离状态下。在 Flight 中，这意味着测试您的路由、控制器和逻辑如何响应不同的输入——而不依赖全局状态或真实的外部服务。

关键原则：
- **测试行为，而不是实现：** 关注您的代码做什么，而不是如何做。
- **避免全局状态：** 使用依赖注入而不是 `Flight::set()` 或 `Flight::get()`。
- **模拟外部服务：** 用测试替身替换数据库或邮件程序等内容。
- **保持测试快速且专注：** 单元测试不应访问真实数据库或 API。

## 基本用法

### 设置 PHPUnit

1. 使用 Composer 安装 PHPUnit：
   ```bash
   composer require --dev phpunit/phpunit
   ```
2. 在项目根目录中创建 `tests` 目录。
3. 在您的 `composer.json` 中添加测试脚本：
   ```json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```
4. 创建 `phpunit.xml` 文件：
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

现在您可以使用 `composer test` 运行测试。

### 测试简单的路由处理程序

假设您有一个验证电子邮件的路由：

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

为此控制器的简单测试：

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

**提示：**
- 使用 `$app->request()->data` 模拟 POST 数据。
- 在测试中避免使用 `Flight::` 静态方法——使用 `$app` 实例。

### 为可测试控制器使用依赖注入

将依赖项（如数据库或邮件程序）注入到您的控制器中，使其在测试中易于模拟：

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

以及带有模拟的测试：

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

## 高级用法

- **模拟：** 使用 PHPUnit 内置的模拟或匿名类来替换依赖项。
- **直接测试控制器：** 使用新的 `Engine` 实例化控制器并模拟依赖项。
- **避免过度模拟：** 在可能的情况下让真实逻辑运行；仅模拟外部服务。

## 另请参阅

- [Unit Testing Guide](/guides/unit-testing) - 单元测试最佳实践的全面指南。
- [Dependency Injection Container](/learn/dependency-injection-container) - 如何使用 DIC 来管理依赖项并提高可测试性。
- [Extending](/learn/extending) - 如何添加自己的助手或覆盖核心类。
- [PDO Wrapper](/learn/pdo-wrapper) - 简化数据库交互，并在测试中更容易模拟。
- [Requests](/learn/requests) - 在 Flight 中处理 HTTP 请求。
- [Responses](/learn/responses) - 向用户发送响应。
- [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) - 学习 SOLID 原则如何改善您的单元测试。

## 故障排除

- 在您的代码和测试中避免使用全局状态（`Flight::set()`、`$_SESSION` 等）。
- 如果您的测试很慢，您可能在编写集成测试——模拟外部服务以保持单元测试快速。
- 如果测试设置复杂，请考虑重构您的代码以使用依赖注入。

## 更新日志

- v3.15.0 - 添加了依赖注入和模拟的示例。