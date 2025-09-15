# Юніт-тестування в Flight PHP з PHPUnit

Цей посібник вводить юніт-тестування в Flight PHP за допомогою [PHPUnit](https://phpunit.de/), призначений для початківців, які хочуть зрозуміти *чому* юніт-тестування важливе і як його застосовувати на практиці. Ми зосередимося на тестуванні *поведінки* — забезпеченні того, що ваш додаток робить те, що ви очікуєте, наприклад, надсилання електронного листа чи збереження запису — а не на тривіальних розрахунках. Ми розпочнемо з простого [route handler](/learn/routing) і перейдемо до більш складного [controller](/learn/routing), включаючи [dependency injection](/learn/dependency-injection-container) (DI) і імітацію сторонніх послуг.

## Чому проводити юніт-тести?

Юніт-тестування забезпечує, що ваш код поводиться так, як очікується, виявляючи помилки до того, як вони потраплять у продакшн. Воно особливо корисне в Flight, де легка маршрутизація і гнучкість можуть призводити до складних взаємодій. Для самостійних розробників чи команд юніт-тести діють як сітка безпеки, документуючи очікувану поведінку і запобігаючи регресіям при поверненні до коду пізніше. Вони також покращують дизайн: код, який важко тестувати, часто вказує на надмірну складність чи тісну зв'язку класів.

На відміну від спрощених прикладів (наприклад, тестування `x * y = z`), ми зосередимося на реальних поведінках, таких як перевірка введених даних, збереження даних чи запуск дій, як-от надсилання електронних листів. Наша мета — зробити тестування доступним і значущим.

## Загальні керівні принципи

1. **Тестуйте поведінку, а не реалізацію**: Зосередьтеся на результатах (наприклад, "електронний лист надіслано" чи "запис збережено"), а не на внутрішніх деталях. Це робить тести стійкими до рефакторингу.
2. **Не використовуйте `Flight::`**: Статичні методи Flight дуже зручні, але ускладнюють тестування. Звикніть використовувати змінну `$app` з `$app = Flight::app();`. `$app` має всі ті самі методи, що й `Flight::`. Ви все одно зможете використовувати `$app->route()` чи `$this->app->json()` у вашому контролері тощо. Також використовуйте реальний Flight роутер з `$router = $app->router()`, а потім `$router->get()`, `$router->post()`, `$router->group()` тощо. Див. [Routing](/learn/routing).
3. **Тримайте тести швидкими**: Швидкі тести заохочують до частого виконання. Уникайте повільних операцій, як-от виклики бази даних, у юніт-тестах. Якщо тест повільний, це знак, що ви пишете інтеграційний тест, а не юніт-тест. Інтеграційні тести включають реальні бази даних, реальні HTTP-виклики, реальне надсилання електронних листів тощо. Вони мають своє місце, але є повільними і нестабільними, тобто інколи провалюються з невідомих причин.
4. **Використовуйте описові назви**: Назви тестів повинні чітко описувати тестувану поведінку. Це покращує читабельність і підтримку.
5. **Уникайте глобальних змінних, як чуми**: Мінімізуйте використання `$app->set()` і `$app->get()`, оскільки вони діють як глобальний стан, вимагаючи імітації в кожному тесті. Замість цього віддавайте перевагу DI чи контейнеру DI (див. [Dependency Injection Container](/learn/dependency-injection-container)). Навіть використання `$app->map()` технічно є "глобальним" і повинно бути уникнутим на користь DI. Використовуйте бібліотеку сесій, таку як [flightphp/session](https://github.com/flightphp/session), щоб ви могли імітувати об'єкт сесії в тестах. **Не** викликайте [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) безпосередньо у вашому коді, оскільки це вводить глобальну змінну, ускладнюючи тестування.
6. **Використовуйте Dependency Injection**: Вводьте залежності (наприклад, [`PDO`](https://www.php.net/manual/en/class.pdo.php), сервіси надсилання листів) у контролери, щоб ізолювати логіку і спростити імітацію. Якщо у вас клас з надто багатьма залежностями, розгляньте рефакторинг його на менші класи, які мають одну відповідальність, дотримуючись [SOLID principles](https://en.wikipedia.org/wiki/SOLID).
7. **Імітуйте сторонні послуги**: Імітуйте бази даних, клієнти HTTP (cURL) чи сервіси електронної пошти, щоб уникнути зовнішніх викликів. Тестуйте один чи два рівні в глибину, але дайте вашій основній логіці працювати. Наприклад, якщо ваш додаток надсилає SMS, ви **НЕ** хочете реально надсилати SMS кожного разу, коли запускаєте тести, бо це накопичуватиме витрати (і буде повільнішим). Замість цього імітуйте сервіс SMS і просто перевірте, що ваш код викликав сервіс SMS з правильними параметрами.
8. **Цілюйтеся на високе покриття, а не на досконалість**: 100% покриття рядків — добре, але це не означає, що все в вашому коді протестовано правильно (прочитайте про [branch/path coverage in PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Пріоритетізуйте критичні поведінки (наприклад, реєстрацію користувача, відповіді API і захоплення невдалих відповідей).
9. **Використовуйте контролери для маршрутів**: У визначеннях маршрутів використовуйте контролери, а не замикання. Екземпляр `flight\Engine $app` за замовчуванням вводиться в кожен контролер через конструктор. У тестах використовуйте `$app = new Flight\Engine()`, щоб створити Flight у тесті, ввести його в контролер і викликати методи безпосередньо (наприклад, `$controller->register()`). Див. [Extending Flight](/learn/extending) і [Routing](/learn/routing).
10. **Виберіть стиль імітації і тримайтеся його**: PHPUnit підтримує кілька стилів імітації (наприклад, prophecy, вбудовані імітації), або ви можете використовувати анонімні класи, які мають свої переваги, як-от автодоповнення коду, збої при зміні визначення методу тощо. Просто будьте послідовними у ваших тестах. Див. [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Використовуйте `protected` видимість для методів/властивостей, які ви хочете тестувати у підкласах**: Це дозволяє перевизначати їх у підкласах тестів, не роблячи їх публічними, це особливо корисно для імітацій анонімними класами.

## Налаштування PHPUnit

Спочатку налаштуйте [PHPUnit](https://phpunit.de/) у вашому проекті Flight PHP за допомогою Composer для зручного тестування. Див. [PHPUnit Getting Started guide](https://phpunit.readthedocs.io/en/12.3/installation.html) для деталей.

1. У директорії вашого проекту виконайте:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Це встановлює останню версію PHPUnit як залежність для розробки.

2. Створіть директорію `tests` у корені вашого проекту для файлів тестів.

3. Додайте скрипт тестування до `composer.json` для зручності:
   ```json
   // інші вміст composer.json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. Створіть файл `phpunit.xml` у корені:
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

Тепер, коли ваші тести налаштовані, ви можете запустити `composer test`, щоб виконати тести.

## Тестування простого route handler

Давайте розпочнемо з базового [route](/learn/routing), який перевіряє введену електронну пошту користувача. Ми протестуємо його поведінку: повернення повідомлення про успіх для валідних електронних адрес і помилки для невалідних. Для перевірки електронної пошти ми використовуємо [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

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
		$email = $this->app->request()->data->email; // Симулюємо дані POST
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

Щоб протестувати це, створіть файл тесту. Див. [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) для деталей щодо структуризації тестів:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() { // Тестує, чи повертається успіх для валідної електронної пошти
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // Симулюємо дані POST
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() { // Тестує, чи повертається помилка для невалідної електронної пошти
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email'; // Симулюємо дані POST
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);
	}
}
```

**Ключові пункти**:
- Ми симулюємо дані POST за допомогою класу запиту. Не використовуйте глобальні змінні, як-от `$_POST`, `$_GET` тощо, оскільки це ускладнює тестування (вам доведеться завжди скидати ці значення, інакше інші тести можуть зірватися).
- Усі контролери за замовчуванням отримують екземпляр `flight\Engine`, навіть без налаштування контейнера DI. Це полегшує безпосереднє тестування контролерів.
- Тут немає використання `Flight::`, що робить код легшим для тестування.
- Тести перевіряють поведінку: правильний статус і повідомлення для валідних/невалідних електронних адрес.

Запустіть `composer test`, щоб перевірити, чи маршрут поводиться, як очікується. Для деталей щодо [requests](/learn/requests) і [responses](/learn/responses) в Flight див. відповідні документи.

## Використання Dependency Injection для тестувальних контролерів

Для складніших сценаріїв використовуйте [dependency injection](/learn/dependency-injection-container) (DI), щоб зробити контролери тестувальними. Уникайте глобальних елементів Flight (наприклад, `Flight::set()`, `Flight::map()`, `Flight::register()`), оскільки вони діють як глобальний стан, вимагаючи імітації для кожного тесту. Замість цього використовуйте контейнер DI Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) чи ручну DI.

Давайте використаємо [`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper) замість сирої PDO. Цей обгортка набагато легше імітувати і тестувати!

Ось контролер, який зберігає користувача в базі даних і надсилає вітальний електронний лист:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) { // Вводимо залежності через конструктор
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// Додаємо повернення тут, щоб допомогти юніт-тестуванню зупинити виконання
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**Ключові пункти**:
- Контролер залежить від екземпляру [`PdoWrapper`](/awesome-plugins/pdo-wrapper) і `MailerInterface` (умовний сторонній сервіс електронної пошти).
- Залежності вводяться через конструктор, уникаючи глобальних елементів.

### Тестування контролера з імітаціями

Тепер протестуємо поведінку `UserController`: перевірку електронних адрес, збереження в базі даних і надсилання електронних листів. Ми імітуємо базу даних і сервіс електронної пошти, щоб ізолювати контролер.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() { // Тестує, чи валідна електронна пошта зберігає і надсилає електронний лист

		// Інколи змішування стилів імітації необхідне
		// Тут ми використовуємо вбудовану імітацію PHPUnit для PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Використовуємо анонімний клас для імітації PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// Коли ми імітуємо це, ми не робимо реального виклику бази даних.
			// Ми можемо далі налаштувати цю імітацію, щоб симулювати збої тощо.
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

    public function testInvalidEmailSkipsSaveAndEmail() { // Тестує, чи невалідна електронна пошта пропускає збереження і надсилання
		 $mockDb = new class() extends PdoWrapper {
			// Порожній конструктор обходиться батьківського
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Should not be called'); // Повинне не викликатися
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Should not be called'); // Повинне не викликатися
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Потрібно зіставити jsonHalt, щоб уникнути виходу
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

**Ключові пункти**:
- Ми імітуємо `PdoWrapper` і `MailerInterface`, щоб уникнути реальних викликів бази даних чи електронної пошти.
- Тести перевіряють поведінку: валідні електронні адреси запускають вставку в базу даних і надсилання електронних листів; невалідні пропускають обидва.
- Імітуйте сторонні залежності (наприклад, `PdoWrapper`, `MailerInterface`), дозволяючи логіці контролера працювати.

### Перебагато імітацій

Будьте обережні, щоб не імітувати забагато вашого коду. Дозвольте деякій логіці (наприклад, перевірці контролера) працювати, щоб тестувати реальну поведінку. Ось приклад, чому це може бути погано, використовуючи наш `UserController`. Ми змінимо цю перевірку на метод, званий `isEmailValid` (використовуючи `filter_var`), і інші нові додатки на окремий метод, званий `registerUser`.

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
			// Додаємо повернення тут, щоб допомогти юніт-тестуванню зупинити виконання
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'User registered']);
    }

	protected function isEmailValid($email) { // Перевіряє, чи є електронна пошта валідною
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	protected function registerUser($email) { // Реєструє користувача
		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);
	}
}
```

І тепер перезмішаний юніт-тест, який фактично нічого не тестує:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() { // Тестує, чи валідна електронна пошта зберігає і надсилає електронний лист
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// Ми пропускаємо додаткову ін'єкцію залежностей, бо це "легко"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Обходимо залежності в конструкторі
			public function __construct($app) {
				$this->app = $app;
			}

			// Ми просто змусимо це бути валідним
			protected function isEmailValid($email) {
				return true; // Завжди повертає true, обходячи реальну перевірку
			}

			// Обходимо реальні виклики DB і mailer
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

Ура, у нас є юніт-тести і вони проходять! Але чекайте, що якщо я насправді зміню внутрішню роботу `isEmailValid` чи `registerUser`? Мої тести все одно пройдуть, бо я імітував всю функціональність. Дозвольте мені показати, що я маю на увазі.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... інші методи ...

	protected function isEmailValid($email) {
		// Змінена логіка
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Тепер це повинно мати конкретний домен
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Якщо я запускатиму мої тести вище, вони все одно пройдуть! Але оскільки я не тестував поведінку (дозволяючи деякому коду працювати), у мене може бути помилка, яка чекає на продакшн. Тест повинен бути змінений, щоб врахувати нову поведінку, і також протилежне, коли поведінка не така, як ми очікуємо.

## Повний приклад

Ви можете знайти повний приклад проекту Flight PHP з юніт-тестами на GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Для додаткових посібників див. [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) і [Troubleshooting](/learn/troubleshooting).

## Поширені пастки

- **Перебагато імітацій**: Не імітуйте кожну залежність; дозвольте деякій логіці (наприклад, перевірці контролера) працювати, щоб тестувати реальну поведінку. Див. [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Глобальний стан**: Використання глобальних змінних PHP (наприклад, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) сильно ускладнює тести. Те саме стосується `Flight::`. Рефакторіть, щоб передавати залежності явно.
- **Складна налаштування**: Якщо налаштування тесту складне, ваш клас може мати забагато залежностей чи відповідальностей, що порушує [SOLID principles](https://en.wikipedia.org/wiki/SOLID).

## Масштабування з юніт-тестами

Юніт-тести блищать у великих проектах чи при поверненні до коду через місяці. Вони документують поведінку і виявляють регресії, заощаджуючи час на переучування вашого додатка. Для самостійних розробників тестуйте критичні шляхи (наприклад, реєстрацію користувача, обробку платежів). Для команд тести забезпечують послідовну поведінку під час внесків. Див. [Why Frameworks?](/learn/why-frameworks) для деталей про переваги використання фреймворків і тестів.

Внесіть свої поради щодо тестування до репозиторію документації Flight PHP!

_Написано [n0nag0n](https://github.com/n0nag0n) 2025_