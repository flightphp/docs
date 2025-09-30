# Unit Testing в Flight PHP с PHPUnit

Этот гид вводит в unit testing в Flight PHP с использованием [PHPUnit](https://phpunit.de/), предназначен для начинающих, которые хотят понять *почему* unit testing важен и как применять его на практике. Мы сосредоточимся на тестировании *поведения* — обеспечении того, что ваше приложение делает то, что ожидается, например, отправка email или сохранение записи — вместо тривиальных вычислений. Мы начнем с простого [route handler](/learn/routing) и перейдем к более сложному [controller](/learn/routing), включая [dependency injection](/learn/dependency-injection-container) (DI) и mocking сторонних сервисов.

## Почему Unit Test?

Unit testing обеспечивает, что ваш код ведет себя как ожидается, ловит баги до того, как они попадут в production. Это особенно ценно в Flight, где легковесный routing и гибкость могут привести к сложным взаимодействиям. Для solo-разработчиков или команд unit tests действуют как safety net, документируя ожидаемое поведение и предотвращая регрессии при возвращении к коду позже. Они также улучшают дизайн: код, который трудно тестировать, часто сигнализирует о чрезмерной сложности или тесной связанности классов.

В отличие от простых примеров (например, тестирование `x * y = z`), мы сосредоточимся на реальном поведении, таком как валидация ввода, сохранение данных или запуск действий вроде email. Наша цель — сделать тестирование доступным и значимым.

## Общие Руководящие Принципы

1. **Тестируйте Поведение, Не Реализацию**: Сосредоточьтесь на результатах (например, «email отправлен» или «запись сохранена») вместо внутренних деталей. Это делает тесты устойчивыми к рефакторингу.
2. **Перестаньте использовать `Flight::`**: Статические методы Flight невероятно удобны, но усложняют тестирование. Вы должны привыкнуть использовать переменную `$app` из `$app = Flight::app();`. `$app` имеет все те же методы, что и `Flight::`. Вы все еще сможете использовать `$app->route()` или `$this->app->json()` в вашем controller и т.д. Также вы должны использовать реальный Flight router с `$router = $app->router()` и затем вы сможете использовать `$router->get()`, `$router->post()`, `$router->group()` и т.д. См. [Routing](/learn/routing).
3. **Держите Тесты Быстрыми**: Быстрые тесты поощряют частое выполнение. Избегайте медленных операций, таких как вызовы базы данных в unit tests. Если у вас есть медленный тест, это признак, что вы пишете integration test, а не unit test. Integration tests — это когда вы действительно вовлекаете реальные базы данных, реальные HTTP-вызовы, реальную отправку email и т.д. У них есть свое место, но они медленные и могут быть flaky, то есть иногда падают по неизвестной причине. 
4. **Используйте Описательные Имена**: Имена тестов должны четко описывать тестируемое поведение. Это улучшает читаемость и поддерживаемость.
5. **Избегайте Globals Как Чумы**: Минимизируйте использование `$app->set()` и `$app->get()`, поскольку они действуют как global state, требуя mocks в каждом тесте. Предпочитайте DI или DI container (см. [Dependency Injection Container](/learn/dependency-injection-container)). Даже использование метода `$app->map()` технически является "global" и должно избегаться в пользу DI. Используйте библиотеку сессий, такую как [flightphp/session](https://github.com/flightphp/session), чтобы вы могли mock объект сессии в ваших тестах. **Не** вызывайте [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) напрямую в вашем коде, поскольку это внедряет global variable в ваш код, усложняя тестирование.
6. **Используйте Dependency Injection**: Внедряйте зависимости (например, [`PDO`](https://www.php.net/manual/en/class.pdo.php), mailers) в controllers для изоляции логики и упрощения mocking. Если у вас есть класс с слишком многими зависимостями, рассмотрите рефакторинг его в меньшие классы, каждый с одной ответственностью, следуя [SOLID principles](https://en.wikipedia.org/wiki/SOLID).
7. **Mock Сторонние Сервисы**: Mock базы данных, HTTP-клиенты (cURL) или email-сервисы, чтобы избежать внешних вызовов. Тестируйте на один-два уровня в глубину, но позволяйте вашей основной логике работать. Например, если ваше приложение отправляет SMS, вы **НЕ** хотите реально отправлять SMS каждый раз, когда запускаете тесты, потому что эти расходы накопятся (и это будет медленнее). Вместо этого mock сервис SMS и просто проверьте, что ваш код вызвал сервис SMS с правильными параметрами.
8. **Стремитесь к Высокому Покрытию, Не к Совершенству**: 100% покрытие строк хорошо, но это не значит, что все в вашем коде протестировано правильно (погуглите [branch/path coverage в PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Приоритизируйте критические поведения (например, регистрацию пользователя, ответы API и захват неудачных ответов).
9. **Используйте Controllers для Routes**: В ваших определениях routes используйте controllers, а не closures. `flight\Engine $app` по умолчанию внедряется в каждый controller через конструктор. В тестах используйте `$app = new Flight\Engine()` для инстанцирования Flight в тесте, внедрите его в ваш controller и вызывайте методы напрямую (например, `$controller->register()`). См. [Extending Flight](/learn/extending) и [Routing](/learn/routing).
10. **Выберите Стиль Mocking и Придерживайтесь Его**: PHPUnit поддерживает несколько стилей mocking (например, prophecy, встроенные mocks), или вы можете использовать anonymous classes, которые имеют свои преимущества, такие как code completion, поломка при изменении определения метода и т.д. Просто будьте последовательны в ваших тестах. См. [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Используйте `protected` visibility для методов/свойств, которые вы хотите тестировать в подклассах**: Это позволяет переопределять их в тестовых подклассах без их публичности, это особенно полезно для anonymous class mocks.

## Настройка PHPUnit

Сначала настройте [PHPUnit](https://phpunit.de/) в вашем проекте Flight PHP с использованием Composer для удобного тестирования. См. [PHPUnit Getting Started guide](https://phpunit.readthedocs.io/en/12.3/installation.html) для более подробной информации.

1. В директории вашего проекта запустите:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Это установит последнюю версию PHPUnit как development dependency.

2. Создайте директорию `tests` в корне вашего проекта для файлов тестов.

3. Добавьте скрипт теста в `composer.json` для удобства:
   ```json
   // other composer.json content
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. Создайте файл `phpunit.xml` в корне:
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

Теперь, когда ваши тесты собраны, вы можете запустить `composer test` для выполнения тестов.

## Тестирование Простого Route Handler

Давайте начнем с базового [route](/learn/routing), который валидирует email-ввод пользователя. Мы протестируем его поведение: возвращение сообщения об успехе для валидных email и ошибки для невалидных. Для валидации email мы используем [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

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

Чтобы протестировать это, создайте файл теста. См. [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) для большего количества информации о структурировании тестов:

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

**Ключевые Моменты**:
- Мы симулируем POST-данные с использованием request class. Не используйте globals вроде `$_POST`, `$_GET` и т.д., поскольку это усложняет тестирование (вы всегда должны сбрасывать эти значения, иначе другие тесты могут сломаться).
- Все controllers по умолчанию будут иметь инстанс `flight\Engine`, внедренный в них, даже без настройки DIC container. Это делает гораздо проще тестировать controllers напрямую.
- Нет использования `Flight::` вообще, что делает код проще для тестирования.
- Тесты проверяют поведение: правильный статус и сообщение для валидных/невалидных email.

Запустите `composer test`, чтобы проверить, что route ведет себя как ожидается. Для большего количества информации о [requests](/learn/requests) и [responses](/learn/responses) в Flight см. соответствующие docs.

## Использование Dependency Injection для Testable Controllers

Для более сложных сценариев используйте [dependency injection](/learn/dependency-injection-container) (DI), чтобы сделать controllers testable. Избегайте globals Flight (например, `Flight::set()`, `Flight::map()`, `Flight::register()`), поскольку они действуют как global state, требуя mocks для каждого теста. Вместо этого используйте DI container Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) или manual DI.

Давайте используем [`flight\database\PdoWrapper`](/learn/pdo-wrapper) вместо raw PDO. Этот wrapper гораздо проще mock и unit test!

Вот controller, который сохраняет пользователя в базу данных и отправляет welcome email:

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

**Ключевые Моменты**:
- Controller зависит от инстанса [`PdoWrapper`](/learn/pdo-wrapper) и `MailerInterface` (предполагаемый сторонний email-сервис).
- Зависимости внедряются через конструктор, избегая globals.

### Тестирование Controller с Mocks

Теперь протестируем поведение `UserController`: валидацию email, сохранение в базу данных и отправку email. Мы замоким базу данных и mailer, чтобы изолировать controller.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Иногда смешивание стилей mocking необходимо
		// Здесь мы используем встроенный mock PHPUnit для PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Используя anonymous class для mocking PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// Когда мы моким его таким образом, мы не делаем реальный вызов базы данных.
			// Мы можем дополнительно настроить это, чтобы изменить mock PDOStatement для симуляции сбоев и т.д.
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

**Ключевые Моменты**:
- Мы моким `PdoWrapper` и `MailerInterface`, чтобы избежать реальных вызовов базы данных или email.
- Тесты проверяют поведение: валидные email запускают вставки в базу данных и отправку email; невалидные email пропускают оба.
- Mock сторонние зависимости (например, `PdoWrapper`, `MailerInterface`), позволяя логике controller работать.

### Слишком Много Mocking

Будьте осторожны, чтобы не mock слишком много вашего кода. Позвольте мне дать пример ниже, почему это может быть плохой идеей, используя наш `UserController`. Мы изменим эту проверку на метод под названием `isEmailValid` (используя `filter_var`) и другие новые добавления в отдельный метод под названием `registerUser`.

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

И теперь overmocked unit test, который на самом деле ничего не тестирует:

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

Ура, у нас есть unit tests и они проходят! Но подождите, что если я на самом деле изменю внутреннюю работу `isEmailValid` или `registerUser`? Мои тесты все равно пройдут, потому что я замокил всю функциональность. Позвольте мне показать, что я имею в виду.

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

Если я запущу свои unit tests выше, они все равно пройдут! Но потому что я не тестировал поведение (на самом деле позволяя некоторому коду работать), я потенциально закодировал баг, ожидающий проявления в production. Тест должен быть модифицирован, чтобы учесть новое поведение, и также противоположность, когда поведение не то, что мы ожидаем.

## Полный Пример

Вы можете найти полный пример проекта Flight PHP с unit tests на GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Для более глубокого понимания см. [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).

## Распространенные Ошибки

- **Over-Mocking**: Не мокайте каждую зависимость; позвольте некоторой логике (например, валидации в controller) работать, чтобы тестировать реальное поведение. См. [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Global State**: Использование global PHP-переменных (например, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) сильно делает тесты хрупкими. То же самое с `Flight::`. Рефакторьте, чтобы передавать зависимости явно.
- **Сложная Настройка**: Если настройка теста громоздкая, ваш класс может иметь слишком много зависимостей или ответственностей, нарушая [SOLID principles](/learn/unit-testing-and-solid-principles).

## Масштабирование с Unit Tests

Unit tests сияют в больших проектах или при возвращении к коду через месяцы. Они документируют поведение и ловят регрессии, спасая вас от повторного изучения вашего app. Для solo devs тестируйте критические пути (например, регистрацию пользователя, обработку платежей). Для команд тесты обеспечивают последовательное поведение среди вкладов. См. [Why Frameworks?](/learn/why-frameworks) для большего количества информации о преимуществах использования фреймворков и тестов.

Внесите свои собственные советы по тестированию в репозиторий документации Flight PHP!

_Написано [n0nag0n](https://github.com/n0nag0n) 2025_