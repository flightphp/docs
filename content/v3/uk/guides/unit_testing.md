# Юніт-тестування в Flight PHP з PHPUnit

Цей посібник знайомить з юніт-тестуванням у Flight PHP за допомогою [PHPUnit](https://phpunit.de/), орієнтований на початківців, які хочуть зрозуміти *чому* юніт-тестування важливе та як його застосовувати на практиці. Ми зосередимося на тестуванні *поведінки* — забезпеченні того, що ваша програма робить те, що ви очікуєте, наприклад, надсилання email або збереження запису — а не тривіальних обчислень. Ми почнемо з простого [обробника маршруту](/learn/routing) і перейдемо до складнішого [контролера](/learn/routing), включаючи [вприскування залежностей](/learn/dependency-injection-container) (DI) та імітацію сторонніх сервісів.

## Чому проводити юніт-тестування?

Юніт-тестування забезпечує, що ваш код поводиться як очікується, виявляючи помилки до того, як вони потраплять у продакшн. Це особливо цінно у Flight, де легка маршрутизація та гнучкість можуть призводити до складних взаємодій. Для соло-розробників або команд юніт-тести слугують сіткою безпеки, документуючи очікувану поведінку та запобігаючи регресіям при повторному перегляді коду пізніше. Вони також покращують дизайн: код, який важко тестувати, часто сигналізує про надмірну складність або тісне зв'язування класів.

На відміну від спрощених прикладів (наприклад, тестування `x * y = z`), ми зосередимося на реальних поведінках, таких як валідація введення, збереження даних або активація дій, як-от email. Наша мета — зробити тестування доступним і значущим.

## Загальні принципи керівництва

1. **Тестуйте поведінку, а не реалізацію**: Зосередьтеся на результатах (наприклад, «email надіслано» або «запис збережено») замість внутрішніх деталей. Це робить тести стійкими до рефакторингу.
2. **Перестаньте використовувати `Flight::`**: Статичні методи Flight жахливо зручні, але ускладнюють тестування. Ви повинні звикнути використовувати змінну `$app` з `$app = Flight::app();`. `$app` має всі ті самі методи, що й `Flight::`. Ви все ще зможете використовувати `$app->route()` або `$this->app->json()` у вашому контролері тощо. Ви також повинні використовувати реальний роутер Flight з `$router = $app->router()` і тоді ви зможете використовувати `$router->get()`, `$router->post()`, `$router->group()` тощо. Див. [Routing](/learn/routing).
3. **Тримайте тести швидкими**: Швидкі тести заохочують до частого виконання. Уникайте повільних операцій, як-от виклики бази даних у юніт-тестах. Якщо у вас є повільний тест, це знак, що ви пишете інтеграційний тест, а не юніт-тест. Інтеграційні тести — це коли ви дійсно залучаєте реальні бази даних, реальні HTTP-виклики, реальне надсилання email тощо. Вони мають своє місце, але вони повільні та можуть бути нестабільними, тобто іноді провалюються з невідомої причини. 
4. **Використовуйте описові назви**: Назви тестів повинні чітко описувати поведінку, яка тестується. Це покращує читабельність і підтримку.
5. **Уникайте глобальних змінних як чуми**: Мінімізуйте використання `$app->set()` і `$app->get()`, оскільки вони діють як глобальний стан, що вимагає імітацій у кожному тесті. Віддавайте перевагу DI або контейнеру DI (див. [Dependency Injection Container](/learn/dependency-injection-container)). Навіть використання методу `$app->map()` технічно є «глобальним» і повинно уникатися на користь DI. Використовуйте бібліотеку сесій, таку як [flightphp/session](https://github.com/flightphp/session), щоб ви могли імітувати об'єкт сесії у ваших тестах. **Не** викликайте [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) безпосередньо у вашому коді, оскільки це впорскує глобальну змінну у ваш код, ускладнюючи тестування.
6. **Використовуйте вприскування залежностей**: Впроваджуйте залежності (наприклад, [`PDO`](https://www.php.net/manual/en/class.pdo.php), поштові клієнти) у контролери, щоб ізолювати логіку та спростити імітацію. Якщо у вас є клас з надто багатьма залежностями, розгляньте рефакторинг його на менші класи, кожен з яких має єдину відповідальність, дотримуючись [принципів SOLID](https://en.wikipedia.org/wiki/SOLID).
7. **Імітуйте сторонні сервіси**: Імітуйте бази даних, HTTP-клієнти (cURL) або поштові сервіси, щоб уникнути зовнішніх викликів. Тестуйте на один-два рівні глибини, але дозвольте вашій основній логіці виконуватися. Наприклад, якщо ваша програма надсилає текстове повідомлення, ви **НЕ** хочете дійсно надсилати текстове повідомлення щоразу, коли запускаєте тести, бо ці витрати накопичаться (і це буде повільніше). Натомість імітуйте сервіс текстових повідомлень і просто перевірте, що ваш код викликав сервіс текстових повідомлень з правильними параметрами.
8. **Стреміться до високого покриття, а не до досконалості**: 100% покриття рядків — це добре, але це насправді не означає, що все у вашому коді протестовано так, як повинно бути (прочитайте про [branch/path coverage в PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Пріоритезуйте критичні поведінки (наприклад, реєстрацію користувача, відповіді API та захоплення невдалих відповідей).
9. **Використовуйте контролери для маршрутів**: У ваших визначеннях маршрутів використовуйте контролери, а не замикання. `flight\Engine $app` впроваджується в кожен контролер через конструктор за замовчуванням. У тестах використовуйте `$app = new Flight\Engine()` для інстанціювання Flight у тесті, впровадіть його у ваш контролер і викликайте методи безпосередньо (наприклад, `$controller->register()`). Див. [Extending Flight](/learn/extending) і [Routing](/learn/routing).
10. **Оберіть стиль імітації та дотримуйтеся його**: PHPUnit підтримує кілька стилів імітації (наприклад, prophecy, вбудовані імітації), або ви можете використовувати анонімні класи, які мають свої переваги, як-от автодоповнення коду, зламання, якщо ви змінюєте визначення методу тощо. Просто будьте послідовними у ваших тестах. Див. [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Використовуйте `protected` видимість для методів/властивостей, які ви хочете тестувати в підкласах**: Це дозволяє перевизначати їх у тестових підкласах без того, щоб робити їх публічними, це особливо корисно для імітацій анонімних класів.

## Налаштування PHPUnit

Спочатку налаштуйте [PHPUnit](https://phpunit.de/) у вашому проекті Flight PHP за допомогою Composer для легкого тестування. Див. [посібник з початку роботи з PHPUnit](https://phpunit.readthedocs.io/en/12.3/installation.html) для деталей.

1. У директорії вашого проекту запустіть:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Це встановлює останню версію PHPUnit як залежність для розробки.

2. Створіть директорію `tests` у корені вашого проекту для файлів тестів.

3. Додайте скрипт тесту до `composer.json` для зручності:
   ```json
   // інший вміст composer.json
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

Тепер, коли ваші тести побудовані, ви можете запустити `composer test` для виконання тестів.

## Тестування простого обробника маршруту

Почнемо з базового [маршруту](/learn/routing), який валідує email введення користувача. Ми протестуємо його поведінку: повернення повідомлення про успіх для валідних email та помилки для невалідних. Для валідації email ми використовуємо [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

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

Щоб протестувати це, створіть файл тесту. Див. [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) для деталей про структуру тестів:

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

**Ключові моменти**:
- Ми симулюємо POST-дані за допомогою класу request. Не використовуйте глобальні змінні, як `$_POST`, `$_GET` тощо, оскільки це ускладнює тестування (ви завжди повинні скидати ці значення, інакше інші тести можуть зламатися).
- Усі контролери за замовчуванням матимуть інстанс `flight\Engine` впроваджений у них навіть без налаштованого контейнера DIC. Це значно полегшує тестування контролерів безпосередньо.
- Немає використання `Flight::` взагалі, що робить код легшим для тестування.
- Тести перевіряють поведінку: правильний статус і повідомлення для валідних/невалідних email.

Запустіть `composer test`, щоб перевірити, чи маршрут поводиться як очікується. Для деталей про [requests](/learn/requests) і [responses](/learn/responses) у Flight див. відповідну документацію.

## Використання вприскування залежностей для тестуємо контролерів

Для складніших сценаріїв використовуйте [вприскування залежностей](/learn/dependency-injection-container) (DI), щоб зробити контролери тестовими. Уникайте глобальних змінних Flight (наприклад, `Flight::set()`, `Flight::map()`, `Flight::register()`), оскільки вони діють як глобальний стан, що вимагає імітацій для кожного тесту. Натомість використовуйте контейнер DI Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) або ручне DI.

Використаємо [`flight\database\PdoWrapper`](/learn/pdo-wrapper) замість сирого PDO. Цей обгортка набагато легша для імітації та юніт-тестування!

Ось контролер, який зберігає користувача в базу даних і надсилає email привітання:

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

**Ключові моменти**:
- Контролер залежить від інстансу [`PdoWrapper`](/learn/pdo-wrapper) та `MailerInterface` (уявний сторонній поштовий сервіс).
- Залежності впроваджуються через конструктор, уникаючи глобальних змінних.

### Тестування контролера з імітаціями

Тепер протестуємо поведінку `UserController`: валідацію email, збереження в базу даних та надсилання email. Ми імітуємо базу даних і поштовий сервіс, щоб ізолювати контролер.

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

**Ключові моменти**:
- Ми імітуємо `PdoWrapper` та `MailerInterface`, щоб уникнути реальних викликів бази даних або email.
- Тести перевіряють поведінку: валідні email активують вставки в базу даних та надсилання email; невалідні email пропускають обидва.
- Імітуйте сторонні залежності (наприклад, `PdoWrapper`, `MailerInterface`), дозволяючи логіці контролера виконуватися.

### Надмірна імітація

Будьте обережні, щоб не імітувати надто багато вашого коду. Дозвольте мені дати приклад нижче про те, чому це може бути поганою річчю, використовуючи наш `UserController`. Ми змінимо цю перевірку на метод під назвою `isEmailValid` (використовуючи `filter_var`) і інші нові додатки на окремий метод під назвою `registerUser`.

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

І тепер надмірно імітований юніт-тест, який насправді нічого не тестує:

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

Ура, у нас є юніт-тести, і вони проходять! Але чекайте, що якщо я насправді зміню внутрішні механізми `isEmailValid` або `registerUser`? Мої тести все ще пройдуть, бо я імітував всю функціональність. Дозвольте мені показати, що я маю на увазі.

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

Якщо я запустив би мої юніт-тести вище, вони все ще пройшли б! Але бо я не тестував поведінку (насправді дозволяючи деякому коду виконуватися), я потенційно закодував помилку, яка чекає на прояв у продакшні. Тест повинен бути модифікований, щоб врахувати нову поведінку, а також протилежність, коли поведінка не така, як ми очікуємо.

## Повний приклад

Ви можете знайти повний приклад проекту Flight PHP з юніт-тестами на GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Для глибшого розуміння див. [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).

## Поширені помилки

- **Надмірна імітація**: Не імітуйте кожну залежність; дозвольте деякій логіці (наприклад, валідації контролера) виконуватися, щоб тестувати реальну поведінку. Див. [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Глобальний стан**: Використання глобальних змінних PHP (наприклад, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) робить тести крихкими. Те саме стосується `Flight::`. Рефакторте, щоб передавати залежності явно.
- **Складне налаштування**: Якщо налаштування тесту громіздке, ваш клас може мати надто багато залежностей або відповідальностей, що порушує [принципи SOLID](/learn/unit-testing-and-solid-principles).

## Масштабування з юніт-тестами

Юніт-тести сяють у більших проектах або при повторному перегляді коду через місяці. Вони документують поведінку та виявляють регресії, заощаджуючи вам від повторного вивчення вашої програми. Для соло-розробників тестуйте критичні шляхи (наприклад, реєстрацію користувача, обробку платежів). Для команд тести забезпечують послідовну поведінку в внесках. Див. [Why Frameworks?](/learn/why-frameworks) для деталей про переваги використання фреймворків і тестів.

Внесіть свої поради з тестування до репозиторію документації Flight PHP!

_Написано [n0nag0n](https://github.com/n0nag0n) 2025_