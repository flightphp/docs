# Тестування модулів

## Огляд

Тестування модулів у Flight допомагає вам переконатися, що ваша програма поводиться як очікується, виявляти помилки на ранніх етапах та полегшувати підтримку вашого коду. Flight розроблено для безперебійної роботи з [PHPUnit](https://phpunit.de/), найпопулярнішим фреймворком для тестування PHP.

## Розуміння

Тести модулів перевіряють поведінку невеликих частин вашої програми (наприклад, контролерів або сервісів) в ізоляції. У Flight це означає тестування того, як ваші маршрути, контролери та логіка реагують на різні входи — без залежності від глобального стану або реальних зовнішніх сервісів.

Ключові принципи:
- **Тестуйте поведінку, а не реалізацію:** Зосередьтеся на тому, що робить ваш код, а не як він це робить.
- **Уникайте глобального стану:** Використовуйте ін'єкцію залежностей замість `Flight::set()` або `Flight::get()`.
- **Мокайте зовнішні сервіси:** Замінюйте такі речі, як бази даних або поштові клієнти, тестовими подвійниками.
- **Тримайте тести швидкими та сфокусованими:** Тести модулів не повинні звертатися до реальних баз даних або API.

## Основне використання

### Налаштування PHPUnit

1. Встановіть PHPUnit за допомогою Composer:
   ```bash
   composer require --dev phpunit/phpunit
   ```
2. Створіть директорію `tests` у корені вашого проєкту.
3. Додайте скрипт для тестів до вашого `composer.json`:
   ```json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```
4. Створіть файл `phpunit.xml`:
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

Тепер ви можете запускати тести за допомогою `composer test`.

### Тестування простого обробника маршруту

Припустимо, у вас є маршрут, який валідує email:

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

Простий тест для цього контролера:

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

**Поради:**
- Симулюйте дані POST за допомогою `$app->request()->data`.
- Уникайте використання статичних методів `Flight::` у ваших тестах — використовуйте екземпляр `$app`.

### Використання ін'єкції залежностей для тестуємо контролерів

Інжектуйте залежності (наприклад, базу даних або поштового клієнта) у ваші контролери, щоб полегшити їх мокування в тестах:

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

І тест з моками:

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

## Розширене використання

- **Мокування:** Використовуйте вбудовані мокі PHPUnit або анонімні класи для заміни залежностей.
- **Тестування контролерів безпосередньо:** Створюйте екземпляри контролерів з новим `Engine` та мокайте залежності.
- **Уникайте надмірного мокування:** Дозволяйте реальній логіці виконуватися, де можливо; мокайте лише зовнішні сервіси.

## Дивіться також

- [Unit Testing Guide](/guides/unit-testing) - Комплексний посібник з найкращих практик тестування модулів.
- [Dependency Injection Container](/learn/dependency-injection-container) - Як використовувати DIC для керування залежностями та покращення тестованості.
- [Extending](/learn/extending) - Як додавати власні помічники або перевизначати основні класи.
- [PDO Wrapper](/learn/pdo-wrapper) - Спрощує взаємодію з базами даних і легше мокувати в тестах.
- [Requests](/learn/requests) - Обробка HTTP-запитів у Flight.
- [Responses](/learn/responses) - Надсилання відповідей користувачам.
- [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) - Дізнайтеся, як принципи SOLID можуть покращити ваші тести модулів.

## Вирішення проблем

- Уникайте використання глобального стану (`Flight::set()`, `$_SESSION` тощо) у вашому коді та тестах.
- Якщо ваші тести повільні, ви, можливо, пишете інтеграційні тести — мокайте зовнішні сервіси, щоб тримати тести модулів швидкими.
- Якщо налаштування тестів складне, розгляньте рефакторинг вашого коду для використання ін'єкції залежностей.

## Журнал змін

- v3.15.0 - Додано приклади для ін'єкції залежностей та мокування.