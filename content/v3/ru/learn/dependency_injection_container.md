# Контейнер внедрения зависимостей

## Обзор

Контейнер внедрения зависимостей (DIC) — это мощное расширение, которое позволяет управлять зависимостями вашего приложения.

## Понимание

Внедрение зависимостей (DI) — это ключевой концепт в современных PHP-фреймворках и используется для управления созданием и конфигурацией объектов. Некоторые примеры библиотек DIC: [flightphp/container](https://github.com/flightphp/container), [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), и [league/container](https://container.thephpleague.com/).

DIC — это изысканный способ позволить вам создавать и управлять вашими классами в централизованном месте. Это полезно, когда вам нужно передавать один и тот же объект нескольким классам (например, вашим контроллерам или middleware).

## Основное использование

Старый способ может выглядеть так:
```php

require 'vendor/autoload.php';

// класс для управления пользователями из базы данных
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

// в вашем файле routes.php

$db = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$UserController = new UserController($db);
Flight::route('/user/@id', [ $UserController, 'view' ]);
// другие маршруты UserController...

Flight::start();
```

Из приведенного выше кода видно, что мы создаем новый объект `PDO` и передаем его в класс `UserController`. Это нормально для небольшого приложения, но по мере роста вашего приложения вы обнаружите, что создаете или передаете один и тот же объект `PDO` в нескольких местах. Здесь DIC становится очень полезным.

Вот тот же пример с использованием DIC (используя Dice):
```php

require 'vendor/autoload.php';

// тот же класс, что и выше. Ничего не изменилось
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

// создаем новый контейнер
$container = new \Dice\Dice;

// добавляем правило, чтобы сказать контейнеру, как создать объект PDO
// не забудьте переприсвоить его самому себе, как ниже!
$container = $container->addRule('PDO', [
	// shared означает, что один и тот же объект будет возвращен каждый раз
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Это регистрирует обработчик контейнера, чтобы Flight знал, как его использовать.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// теперь мы можем использовать контейнер для создания нашего UserController
Flight::route('/user/@id', [ UserController::class, 'view' ]);

Flight::start();
```

Я уверен, вы можете подумать, что в примере добавилось много лишнего кода. Магия происходит, когда у вас есть другой контроллер, которому нужен объект `PDO`.

```php

// Если все ваши контроллеры имеют конструктор, который требует объект PDO
// каждый из маршрутов ниже автоматически получит его внедренным!!!
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

Дополнительным преимуществом использования DIC является то, что модульное тестирование становится гораздо проще. Вы можете создать мок-объект и передать его в ваш класс. Это огромное преимущество при написании тестов для вашего приложения!

### Создание централизованного обработчика DIC

Вы можете создать централизованный обработчик DIC в вашем файле services, расширив ваше приложение через [extending](/learn/extending). Вот пример:

```php
// services.php

// создаем новый контейнер
$container = new \Dice\Dice;
// не забудьте переприсвоить его самому себе, как ниже!
$container = $container->addRule('PDO', [
	// shared означает, что один и тот же объект будет возвращен каждый раз
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// теперь мы можем создать маппируемый метод для создания любого объекта. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// Это регистрирует обработчик контейнера, чтобы Flight знал, как использовать его для контроллеров/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// предположим, у нас есть следующий пример класса, который принимает объект PDO в конструкторе
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// код для отправки email
	}
}

// И наконец, вы можете создавать объекты с использованием внедрения зависимостей
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

### `flightphp/container`

Flight имеет плагин, который предоставляет простой контейнер, соответствующий PSR-11, который вы можете использовать для обработки внедрения зависимостей. Вот быстрый пример, как его использовать:

```php

// index.php например
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
	// выведет это правильно!
  }
}

Flight::route('GET /', [TestController::class, 'index']);

Flight::start();
```

#### Расширенное использование flightphp/container

Вы также можете разрешать зависимости рекурсивно. Вот пример:

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
    // Реализация ...
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

Вы также можете создать свой собственный обработчик DIC. Это полезно, если у вас есть кастомный контейнер, который вы хотите использовать и который не соответствует PSR-11 (Dice). См. раздел [basic usage](#basic-usage) о том, как это сделать.

Кроме того, есть некоторые полезные значения по умолчанию, которые облегчат вам жизнь при использовании Flight.

#### Экземпляр Engine

Если вы используете экземпляр `Engine` в ваших контроллерах/middleware, вот как вы бы его настроили:

```php

// Где-то в вашем bootstrap-файле
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Здесь вы передаете экземпляр
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Теперь вы можете использовать экземпляр Engine в ваших контроллерах/middleware

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

#### Добавление других классов

Если у вас есть другие классы, которые вы хотите добавить в контейнер, с Dice это просто, поскольку они будут автоматически разрешены контейнером. Вот пример:

```php

$container = new \Dice\Dice;
// Если вам не нужно внедрять зависимости в ваши классы,
// вам не нужно ничего определять!
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

Flight также может использовать любой контейнер, соответствующий PSR-11. Это означает, что вы можете использовать любой контейнер, реализующий интерфейс PSR-11. Вот пример с использованием PSR-11 контейнера League:

```php

require 'vendor/autoload.php';

// тот же класс UserController, что и выше

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

Это может быть немного более многословным, чем предыдущий пример с Dice, но все равно выполняет задачу с теми же преимуществами!

## См. также
- [Extending Flight](/learn/extending) - Узнайте, как вы можете добавить внедрение зависимостей в свои собственные классы, расширив фреймворк.
- [Configuration](/learn/configuration) - Узнайте, как настроить Flight для вашего приложения.
- [Routing](/learn/routing) - Узнайте, как определять маршруты для вашего приложения и как работает внедрение зависимостей с контроллерами.
- [Middleware](/learn/middleware) - Узнайте, как создавать middleware для вашего приложения и как работает внедрение зависимостей с middleware.

## Устранение неисправностей
- Если у вас проблемы с контейнером, убедитесь, что вы передаете правильные имена классов в контейнер.

## Журнал изменений
- v3.7.0 - Добавлена возможность регистрации обработчика DIC в Flight.