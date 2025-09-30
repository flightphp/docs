# Контейнер для ін'єкції залежностей

## Огляд

Контейнер для ін'єкції залежностей (DIC) — це потужне розширення, яке дозволяє керувати залежностями вашого додатка.

## Розуміння

Ін'єкція залежностей (DI) — це ключове поняття в сучасних PHP-фреймворках і використовується для керування створенням та конфігурацією об'єктів. Деякі приклади бібліотек DIC: [flightphp/container](https://github.com/flightphp/container), [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), і [league/container](https://container.thephpleague.com/).

DIC — це вишуканий спосіб дозволити вам створювати та керувати вашими класами в централізованому місці. Це корисно, коли вам потрібно передавати той самий об'єкт до кількох класів (наприклад, до ваших контролерів або middleware).

## Основне використання

Старий спосіб робити речі може виглядати так:
```php

require 'vendor/autoload.php';

// клас для керування користувачами з бази даних
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

// у вашому файлі routes.php

$db = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$UserController = new UserController($db);
Flight::route('/user/@id', [ $UserController, 'view' ]);
// інші маршрути UserController...

Flight::start();
```

З наведеного вище коду видно, що ми створюємо новий об'єкт `PDO` і передаємо його до класу `UserController`. Це нормально для малого додатка, але коли ваш додаток росте, ви помітите, що створюєте або передаєте той самий об'єкт `PDO` 
в кількох місцях. Тут DIC стає корисним.

Ось той самий приклад з використанням DIC (використовуючи Dice):
```php

require 'vendor/autoload.php';

// той самий клас, як вище. Нічого не змінилося
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

// створюємо новий контейнер
$container = new \Dice\Dice;

// додаємо правило, щоб сказати контейнеру, як створювати об'єкт PDO
// не забудьте перепризначити його собі, як нижче!
$container = $container->addRule('PDO', [
	// shared означає, що той самий об'єкт буде повертатися кожного разу
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Це реєструє обробник контейнера, щоб Flight знав використовувати його.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// тепер ми можемо використовувати контейнер для створення нашого UserController
Flight::route('/user/@id', [ UserController::class, 'view' ]);

Flight::start();
```

Я впевнений, ви можете думати, що було додано багато зайвого коду до прикладу.
Магія приходить, коли у вас є інший контролер, якому потрібен об'єкт `PDO`.

```php

// Якщо всі ваші контролери мають конструктор, якому потрібен об'єкт PDO
// кожен з маршрутів нижче автоматично отримає його ін'єкцію!!!
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

Додатковою перевагою використання DIC є те, що модульне тестування стає набагато простішим. Ви можете
створити мок-об'єкт і передати його до вашого класу. Це величезна користь, коли ви пишете тести для вашого додатка!

### Створення централізованого обробника DIC

Ви можете створити централізований обробник DIC у вашому файлі сервісів, [розширюючи](/learn/extending) ваш додаток. Ось приклад:

```php
// services.php

// створюємо новий контейнер
$container = new \Dice\Dice;
// не забудьте перепризначити його собі, як нижче!
$container = $container->addRule('PDO', [
	// shared означає, що той самий об'єкт буде повертатися кожного разу
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// тепер ми можемо створити мапований метод для створення будь-якого об'єкта. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// Це реєструє обробник контейнера, щоб Flight знав використовувати його для контролерів/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// припустимо, у нас є наступний приклад класу, який приймає об'єкт PDO в конструкторі
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// код, що надсилає email
	}
}

// І нарешті, ви можете створювати об'єкти з використанням ін'єкції залежностей
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

### `flightphp/container`

Flight має плагін, який надає простий контейнер, сумісний з PSR-11, який ви можете використовувати для керування
вашими залежностями. Ось швидкий приклад, як його використовувати:

```php

// index.php наприклад
require 'vendor/autoload.php';

use flight\Container;

$container = new Container;

$container->set(PDO::class, fn(): PDO => new PDO('sqlite::memory:'));

Flight::registerContainerHandler([$container, 'get']);

class TestController {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function index() {
    var_dump($this->pdo);
	// виведе це правильно!
  }
}

Flight::route('GET /', [TestController::class, 'index']);

Flight::start();
```

#### Розширене використання flightphp/container

Ви також можете розв'язувати залежності рекурсивно. Ось приклад:

```php
<?php

require 'vendor/autoload.php';

use flight\Container;

class User {}

interface UserRepository {
  function find(int $id): ?User;
}

class PdoUserRepository implements UserRepository {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function find(int $id): ?User {
    // Реалізація ...
    return null;
  }
}

$container = new Container;

$container->set(PDO::class, static fn(): PDO => new PDO('sqlite::memory:'));
$container->set(UserRepository::class, PdoUserRepository::class);

$userRepository = $container->get(UserRepository::class);
var_dump($userRepository);

/*
object(PdoUserRepository)#4 (1) {
  ["pdo":"PdoUserRepository":private]=>
  object(PDO)#3 (0) {
  }
}
 */
```

### DICE

Ви також можете створити свій власний обробник DIC. Це корисно, якщо у вас є власний
контейнер, який ви хочете використовувати, що не є PSR-11 (Dice). Дивіться 
[основне використання](#basic-usage) для того, як це зробити.

Крім того, є
деякі корисні налаштування за замовчуванням, які полегшать ваше життя при використанні Flight.

#### Екземпляр Engine

Якщо ви використовуєте екземпляр `Engine` у ваших контролерах/middleware, ось
як би ви його налаштували:

```php

// Десь у вашому файлі завантаження
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Тут ви передаєте екземпляр
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Тепер ви можете використовувати екземпляр Engine у ваших контролерах/middleware

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

#### Додавання інших класів

Якщо у вас є інші класи, які ви хочете додати до контейнера, з Dice це легко, оскільки вони будуть автоматично розв'язані контейнером. Ось приклад:

```php

$container = new \Dice\Dice;
// Якщо вам не потрібно ін'єктувати залежності до ваших класів
// вам не потрібно нічого визначати!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MyCustomClass {
	public function parseThing() {
		return 'thing';
	}
}

class UserController {

	protected MyCustomClass $MyCustomClass;

	public function __construct(MyCustomClass $MyCustomClass) {
		$this->MyCustomClass = $MyCustomClass;
	}

	public function index() {
		echo $this->MyCustomClass->parseThing();
	}
}

Flight::route('/user', 'UserController->index');
```

### PSR-11

Flight також може використовувати будь-який контейнер, сумісний з PSR-11. Це означає, що ви можете використовувати будь-який
контейнер, який реалізує інтерфейс PSR-11. Ось приклад з використанням
контейнера PSR-11 від League:

```php

require 'vendor/autoload.php';

// той самий клас UserController, як вище

$container = new \League\Container\Container();
$container->add(UserController::class)->addArgument(PdoWrapper::class);
$container->add(PdoWrapper::class)
	->addArgument('mysql:host=localhost;dbname=test')
	->addArgument('user')
	->addArgument('pass');
Flight::registerContainerHandler($container);

Flight::route('/user', [ 'UserController', 'view' ]);

Flight::start();
```

Це може бути трохи більш багатослівним, ніж попередній приклад з Dice, але все
однаково виконує роботу з тими самими перевагами!

## Дивіться також
- [Розширення Flight](/learn/extending) - Дізнайтеся, як ви можете додати ін'єкцію залежностей до ваших власних класів, розширюючи фреймворк.
- [Конфігурація](/learn/configuration) - Дізнайтеся, як налаштувати Flight для вашого додатка.
- [Маршрутизація](/learn/routing) - Дізнайтеся, як визначати маршрути для вашого додатка та як працює ін'єкція залежностей з контролерами.
- [Middleware](/learn/middleware) - Дізнайтеся, як створювати middleware для вашого додатка та як працює ін'єкція залежностей з middleware.

## Вирішення проблем
- Якщо у вас проблеми з вашим контейнером, переконайтеся, що ви передаєте правильні назви класів до контейнера.

## Журнал змін
- v3.7.0 - Додано можливість реєструвати обробник DIC до Flight.