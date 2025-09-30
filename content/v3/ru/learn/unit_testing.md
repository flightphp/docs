# Тестирование единиц

## Обзор

Тестирование единиц в Flight помогает убедиться, что ваше приложение ведет себя как ожидается, обнаруживать ошибки на ранних этапах и делать вашу кодовую базу проще в обслуживании. Flight разработан для беспроблемной работы с [PHPUnit](https://phpunit.de/), наиболее популярным фреймворком для тестирования PHP.

## Понимание

Тесты единиц проверяют поведение небольших частей вашего приложения (например, контроллеров или сервисов) в изоляции. В Flight это означает тестирование того, как ваши маршруты, контроллеры и логика реагируют на различные входные данные — без зависимости от глобального состояния или реальных внешних сервисов.

Ключевые принципы:
- **Тестируйте поведение, а не реализацию:** Сосредоточьтесь на том, что делает ваш код, а не на том, как он это делает.
- **Избегайте глобального состояния:** Используйте внедрение зависимостей вместо `Flight::set()` или `Flight::get()`.
- **Мокируйте внешние сервисы:** Заменяйте такие вещи, как базы данных или почтовые сервисы, тестами-заменителями.
- **Держите тесты быстрыми и сфокусированными:** Тесты единиц не должны обращаться к реальным базам данных или API.

## Основное использование

### Настройка PHPUnit

1. Установите PHPUnit с помощью Composer:
   ```bash
   composer require --dev phpunit/phpunit
   ```
2. Создайте директорию `tests` в корне вашего проекта.
3. Добавьте скрипт теста в ваш `composer.json`:
   ```json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```
4. Создайте файл `phpunit.xml`:
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

Теперь вы можете запускать тесты с помощью `composer test`.

### Тестирование простого обработчика маршрута

Предположим, у вас есть маршрут, который валидирует email:

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

Простой тест для этого контроллера:

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

**Советы:**
- Симулируйте POST-данные с помощью `$app->request()->data`.
- Избегайте использования статических методов `Flight::` в тестах — используйте экземпляр `$app`.

### Использование внедрения зависимостей для тестируемых контроллеров

Внедряйте зависимости (например, базу данных или почтовый сервис) в контроллеры, чтобы сделать их простыми для мокинга в тестах:

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

И тест с моками:

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

## Продвинутое использование

- **Мокирование:** Используйте встроенные моки PHPUnit или анонимные классы для замены зависимостей.
- **Тестирование контроллеров напрямую:** Создавайте экземпляры контроллеров с новым `Engine` и мокайте зависимости.
- **Избегайте чрезмерного мокирования:** Позволяйте реальной логике выполняться, где это возможно; мокайте только внешние сервисы.

## См. также

- [Unit Testing Guide](/guides/unit-testing) - Полное руководство по лучшим практикам тестирования единиц.
- [Dependency Injection Container](/learn/dependency-injection-container) - Как использовать DIC для управления зависимостями и улучшения тестируемости.
- [Extending](/learn/extending) - Как добавлять свои помощники или переопределять основные классы.
- [PDO Wrapper](/learn/pdo-wrapper) - Упрощает взаимодействие с базой данных и проще мокать в тестах.
- [Requests](/learn/requests) - Обработка HTTP-запросов в Flight.
- [Responses](/learn/responses) - Отправка ответов пользователям.
- [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) - Узнайте, как принципы SOLID могут улучшить ваши тесты единиц.

## Устранение неисправностей

- Избегайте использования глобального состояния (`Flight::set()`, `$_SESSION` и т.д.) в вашем коде и тестах.
- Если ваши тесты медленные, возможно, вы пишете интеграционные тесты — мокайте внешние сервисы, чтобы держать тесты единиц быстрыми.
- Если настройка тестов сложная, рассмотрите рефакторинг кода для использования внедрения зависимостей.

## Журнал изменений

- v3.15.0 - Добавлены примеры для внедрения зависимостей и мокирования.