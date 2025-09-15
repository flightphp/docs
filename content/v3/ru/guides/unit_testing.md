# Единичное тестирование в Flight PHP с PHPUnit

Этот гид вводит единичное тестирование в Flight PHP с использованием [PHPUnit](https://phpunit.de/), предназначенный для начинающих, которые хотят понять *почему* единичное тестирование важно и как его применять на практике. Мы сосредоточимся на тестировании *поведения* — обеспечении того, что ваше приложение делает то, что ожидается, например, отправляет электронное письмо или сохраняет запись — вместо тривиальных расчётов. Мы начнём с простого [route handler](/learn/routing) и перейдём к более сложному [controller](/learn/routing), включив [dependency injection](/learn/dependency-injection-container) (DI) и имитацию внешних сервисов.

## Почему проводить единичное тестирование?

Единичное тестирование гарантирует, что ваш код работает так, как ожидается, обнаруживая ошибки до их попадания в производство. Это особенно полезно в Flight, где лёгкий роутинг и гибкость могут привести к сложным взаимодействиям. Для одиночных разработчиков или команд, тесты действуют как защитная сеть, документируя ожидаемое поведение и предотвращая регрессии при повторном обращении к коду. Они также улучшают дизайн: код, который трудно тестировать, часто указывает на чрезмерную сложность или тесную связь классов.

В отличие от простых примеров (например, тестирование `x * y = z`), мы сосредоточимся на реальных сценариях, таких как проверка ввода, сохранение данных или срабатывание действий, как отправка электронных писем. Наша цель — сделать тестирование доступным и значимым.

## Общие принципы руководства

1. **Тестируйте поведение, а не реализацию**: Сосредоточьтесь на результатах (например, "электронное письмо отправлено" или "запись сохранена"), а не на внутренних деталях. Это делает тесты устойчивыми к рефакторингу.
2. **Перестаньте использовать `Flight::`**: Статические методы Flight очень удобны, но усложняют тестирование. Привыкайте использовать переменную `$app` из `$app = Flight::app();`. `$app` имеет все те же методы, что и `Flight::`. Вы всё ещё сможете использовать `$app->route()` или `$this->app->json()` в вашем контроллере и т.д. Также используйте реальный роутер Flight с `$router = $app->router()` и затем `$router->get()`, `$router->post()`, `$router->group()` и т.д. Смотрите [Routing](/learn/routing).
3. **Делайте тесты быстрыми**: Быстрые тесты поощряют частое выполнение. Избегайте медленных операций, таких как вызовы базы данных, в единичных тестах. Если тест медленный, это признак, что вы пишете интеграционный тест, а не единичный. Интеграционные тесты включают реальные базы данных, реальные HTTP-вызовы, реальную отправку электронных писем и т.д. У них есть своё место, но они медленные и могут быть ненадёжными, то есть иногда падать по неизвестным причинам. 
4. **Используйте описательные имена**: Имена тестов должны чётко описывать тестируемое поведение. Это улучшает читаемость и поддерживаемость.
5. **Избегайте глобальных переменных как чумы**: Минимизируйте использование `$app->set()` и `$app->get()`, так как они действуют как глобальное состояние, требуя имитации в каждом тесте. Предпочтите DI или контейнер DI (смотрите [Dependency Injection Container](/learn/dependency-injection-container)). Даже использование `$app->map()` технически является "глобальным" и должно избегаться в пользу DI. Используйте библиотеку сессий, такую как [flightphp/session](https://github.com/flightphp/session), чтобы вы могли имитировать объект сессии в тестах. **Не** обращайтесь напрямую к [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) в вашем коде, так как это вводит глобальную переменную, усложняя тестирование.
6. **Используйте Dependency Injection**: Внедряйте зависимости (например, [`PDO`](https://www.php.net/manual/en/class.pdo.php), сервисы отправки почты) в контроллеры, чтобы изолировать логику и упростить имитацию. Если у класса слишком много зависимостей, рассмотрите рефакторинг его в меньшие классы, каждый с одной ответственностью, следуя [SOLID principles](https://en.wikipedia.org/wiki/SOLID).
7. **Имитируйте внешние сервисы**: Имитируйте базы данных, HTTP-клиенты (cURL) или сервисы отправки почты, чтобы избежать внешних вызовов. Тестируйте один или два уровня в глубину, но позвольте основной логике работать. Например, если ваше приложение отправляет SMS, вы **НЕ** хотите реально отправлять SMS каждый раз при запуске тестов, так как это накапливает расходы (и замедляет процесс). Вместо этого имитируйте сервис SMS и просто проверьте, что ваш код вызвал сервис SMS с правильными параметрами.
8. **Стремитесь к высокому покрытию, а не к совершенству**: 100% покрытие строк — хорошо, но это не значит, что всё в коде тестируется правильно (прочитайте о [branch/path coverage in PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Приоритизируйте критические поведения (например, регистрацию пользователя, ответы API и захват неудачных ответов).
9. **Используйте контроллеры для маршрутов**: В определениях маршрутов используйте контроллеры, а не замыкания. Экземпляр `flight\Engine $app` по умолчанию внедряется в каждый контроллер через конструктор. В тестах используйте `$app = new Flight\Engine()`, чтобы создать экземпляр Flight в тесте, внедрить его в контроллер и вызвать методы напрямую (например, `$controller->register()`). Смотрите [Extending Flight](/learn/extending) и [Routing](/learn/routing).
10. **Выберите стиль имитации и придерживайтесь его**: PHPUnit поддерживает несколько стилей имитации (например, prophecy, встроенные имитаторы), или вы можете использовать анонимные классы, которые имеют свои преимущества, такие как автодополнение кода, сбои при изменении определения метода и т.д. Просто будьте последовательны в тестах. Смотрите [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Используйте `protected` видимость для методов/свойств, которые вы хотите тестировать в подклассах**: Это позволяет переопределять их в подклассах тестов без того, чтобы делать их публичными, что особенно полезно для имитаторов анонимных классов.

## Настройка PHPUnit

Сначала настройте [PHPUnit](https://phpunit.de/) в вашем проекте Flight PHP с помощью Composer для лёгкого тестирования. Смотрите [PHPUnit Getting Started guide](https://phpunit.readthedocs.io/en/12.3/installation.html) для деталей.

1. В каталоге вашего проекта выполните:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Это установит последнюю версию PHPUnit как зависимость для разработки.

2. Создайте каталог `tests` в корне вашего проекта для файлов тестов.

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

Теперь, когда тесты настроены, вы можете запустить `composer test` для выполнения тестов.

## Тестирование простого обработчика маршрута

Давайте начнём с базового [route](/learn/routing), который проверяет ввод email пользователя. Мы протестируем его поведение: возвращение сообщения об успехе для валидных email и ошибки для невалидных. Для проверки email мы используем [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

```php
// index.php
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php
class UserController {
	protected $app;

	public function __construct(flight\Engine $app) {
		$this->app = $app;  // Это присваивает экземпляр приложения
	}

	public function register() {
		$email = $this->app->request()->data->email;
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => 'Invalid email'];  // Это устанавливает массив ответа для ошибки
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Valid email'];  // Это устанавливает массив ответа для успеха
		}

		$this->app->json($responseArray);
	}
}
```

Чтобы протестировать это, создайте файл теста. Смотрите [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) для большего о структуре тестов:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();  // Создаёт новый экземпляр Engine
		$request = $app->request();
		$request->data->email = 'test@example.com';  // Симулирует данные POST
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
		$request->data->email = 'invalid-email';  // Симулирует данные POST
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);
	}
}
```

**Ключевые моменты**:
- Мы симулируем данные POST с помощью класса request. Не используйте глобальные переменные, такие как `$_POST`, `$_GET` и т.д., так как это усложняет тестирование (вам придётся всегда сбрасывать эти значения, иначе другие тесты могут сломаться).
- Все контроллеры по умолчанию получают экземпляр `flight\Engine`, даже без настройки контейнера DI. Это упрощает прямое тестирование контроллеров.
- Здесь нет использования `Flight::` вообще, что делает код проще для тестирования.
- Тесты проверяют поведение: правильный статус и сообщение для валидных/невалидных email.

Запустите `composer test`, чтобы проверить, что маршрут работает как ожидается. Для большего о [requests](/learn/requests) и [responses](/learn/responses) в Flight смотрите релевантные документы.

## Использование Dependency Injection для тестируемых контроллеров

Для более сложных сценариев используйте [dependency injection](/learn/dependency-injection-container) (DI), чтобы сделать контроллеры тестируемыми. Избегайте глобалов Flight (например, `Flight::set()`, `Flight::map()`, `Flight::register()`), так как они действуют как глобальное состояние, требуя имитации для каждого теста. Вместо этого используйте контейнер DI Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) или ручную DI.

Давайте используем [`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper) вместо raw PDO. Этот обёртка гораздо проще имитировать и тестировать!

Вот контроллер, который сохраняет пользователя в базу данных и отправляет приветственное электронное письмо:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;  // Это присваивает экземпляр приложения
        $this->db = $db;  // Это присваивает базу данных
        $this->mailer = $mailer;  // Это присваивает сервис почты
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// Это добавляет возврат, чтобы остановить выполнение для единичного тестирования
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**Ключевые моменты**:
- Контроллер зависит от экземпляра [`PdoWrapper`](/awesome-plugins/pdo-wrapper) и `MailerInterface` (предполагаемый внешний сервис электронных писем).
- Зависимости внедряются через конструктор, избегая глобалов.

### Тестирование контроллера с имитациями

Теперь протестируем поведение `UserController`: проверку email, сохранение в базу данных и отправку электронных писем. Мы имитируем базу данных и сервис почты, чтобы изолировать контроллер.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Иногда смешивание стилей имитации бывает необходимо
		// Здесь мы используем встроенную имитацию PHPUnit для PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Используем анонимный класс для имитации PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// При имитации таким образом, мы не совершаем реального вызова базы данных.
			// Мы можем дополнительно настроить эту имитацию, чтобы симулировать сбои и т.д.
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
			// Пустой конструктор обходит конструктор родителя
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Должен быть вызван');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Должен быть вызван');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Нужно сопоставить jsonHalt, чтобы избежать выхода
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

**Ключевые моменты**:
- Мы имитируем `PdoWrapper` и `MailerInterface`, чтобы избежать реальных вызовов базы данных или электронных писем.
- Тесты проверяют поведение: валидные email запускают вставку в базу данных и отправку электронных писем; невалидные пропускают оба.
- Имитируйте внешние зависимости (например, `PdoWrapper`, `MailerInterface`), позволяя логике контроллера работать.

### Избыточная имитация

Будьте осторожны, чтобы не имитировать слишком много вашего кода. Дам пример ниже, почему это может быть плохо, используя наш `UserController`. Мы изменим проверку на метод `isEmailValid` (с использованием `filter_var`) и другие новые добавления в отдельный метод `registerUser`.

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
			// Это добавляет возврат, чтобы остановить выполнение для единичного тестирования
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'User registered']);
    }

	protected function isEmailValid($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;  // Это проверяет валидность email
	}

	protected function registerUser($email) {
		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);
	}
}
```

И теперь переимитированный единичный тест, который на самом деле ничего не тестирует:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// Мы пропускаем дополнительное внедрение зависимостей, так как это "просто"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Обходим зависимости в конструкторе
			public function __construct($app) {
				$this->app = $app;
			}

			// Мы просто заставим это быть валидным.
			protected function isEmailValid($email) {
				return true;  // Всегда возвращает true, обходя реальную проверку
			}

			// Обходим реальные вызовы DB и почты
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

Ура, у нас есть единичные тесты и они проходят! Но подождите, что если я действительно изменю внутреннюю логику `isEmailValid` или `registerUser`? Мои тесты всё равно пройдут, потому что я имитировал всю функциональность. Дам вам пример.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... другие методы ...

	protected function isEmailValid($email) {
		// Изменённая логика
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Теперь оно должно иметь только конкретный домен
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Если я запущу мои вышеуказанные единичные тесты, они всё равно пройдут! Но поскольку я не тестировал поведение (не позволил некоторому коду работать), у меня может быть ошибка, ожидающая в производстве. Тесты должны быть модифицированы, чтобы учитывать новое поведение, и также противоположное, когда поведение не такое, как ожидается.

## Полный пример

Вы можете найти полный пример проекта Flight PHP с единичными тестами на GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Для большего гидов смотрите [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) и [Troubleshooting](/learn/troubleshooting).

## Распространённые ловушки

- **Избыточная имитация**: Не имитируйте каждую зависимость; позвольте некоторой логике (например, проверке в контроллере) работать, чтобы тестировать реальное поведение. Смотрите [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Глобальное состояние**: Использование глобальных переменных PHP (например, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) сильно делает тесты хрупкими. То же касается и `Flight::`. Рефакторите, чтобы передавать зависимости явно.
- **Сложная настройка**: Если настройка теста громоздкая, ваш класс может иметь слишком много зависимостей или ответственностей, нарушающих [SOLID principles](https://en.wikipedia.org/wiki/SOLID).

## Масштабирование с единичными тестами

Единичные тесты сияют в больших проектах или при повторном обращении к коду через месяцы. Они документируют поведение и ловят регрессии, экономя время на переобучении вашего приложения. Для одиночных разработчиков тестируйте критические пути (например, регистрацию пользователя, обработку платежей). Для команд тесты обеспечивают последовательное поведение при вкладах. Смотрите [Why Frameworks?](/learn/why-frameworks) для большего о преимуществах использования фреймворков и тестов.

Поделитесь своими советами по тестированию в репозитории документации Flight PHP!

_Написано [n0nag0n](https://github.com/n0nag0n) 2025_