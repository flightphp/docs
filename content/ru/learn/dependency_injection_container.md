# Контейнер внедрения зависимостей

## Введение

Контейнер внедрения зависимостей (DI контейнер) - это мощный инструмент, который позволяет управлять
зависимостями вашего приложения. Это ключевое понятие в современных фреймворках PHP и
используется для управления созданием и настройкой объектов. Некоторые примеры библиотек DI
включают: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/),
[PHP-DI](http://php-di.org/) и [league/container](https://container.thephpleague.com/).

DI контейнер - это изысканный способ сказать, что он позволяет создавать и управлять вашими классами
в централизованном месте. Это полезно, когда вам нужно передать один и тот же объект
нескольким классам (например, вашим контроллерам). Простой пример может помочь лучше понять это.

## Базовый Пример

Старый способ делать вещи может выглядеть так:
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

$User = new UserController(new PDO('mysql:host=localhost;dbname=test', 'user', 'pass'));
Flight::route('/user/@id', [ $UserController, 'view' ]);

Flight::start();
```

Из приведенного выше кода видно, что мы создаем новый объект `PDO` и передаем его
в наш класс `UserController`. Это подходит для небольшого приложения, но по мере
роста приложения вы обнаружите, что создаете один и тот же объект `PDO` в нескольких
местах. Именно здесь пригодится DI контейнер.

Вот тот же пример с использованием DI контейнера (используя Dice):
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
// не забудьте переприсвоить его самому себе, как показано ниже!
$container = $container->addRule('PDO', [
	// shared означает, что каждый раз будет возвращаться тот же объект
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Это регистрирует обработчик контейнера, чтобы Flight знал, как его использовать.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// теперь мы можем использовать контейнер для создания нашего UserController
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// или альтернативно можно определить маршрут так
Flight::route('/user/@id', 'UserController->view');
// или
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

Возможно, вы думаете, что к примеру было добавлено много лишнего кода.
Магия заключается в том, когда у вас есть еще один контроллер, которому нужен объект `PDO`.

```php

// Если у всех ваших контроллеров есть конструктор, который требует объект PDO
// каждый из нижеуказанных маршрутов автоматически получит его внедренным!!!
Flight::route('/company/@id', 'CompanyController->view');
Flight::route('/organization/@id', 'OrganizationController->view');
Flight::route('/category/@id', 'CategoryController->view');
Flight::route('/settings', 'SettingsController->view');
```

Дополнительный бонус использования DI контейнера - это то, что тестирование модулей становится намного проще. Вы можете
создать фиктивный объект и передать его в ваш класс. Это огромное преимущество, когда вы пишете тесты для своего приложения!

## PSR-11

Flight также может использовать любой совместимый с PSR-11 контейнер. Это означает, что вы можете использовать любой
контейнер, реализующий интерфейс PSR-11. Вот пример использования контейнера PSR-11 от League:

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

Хотя это может быть немного более многословно, чем предыдущий пример с Dice, это все
же делает работу с теми же преимуществами!

## Пользовательский обработчик DIC

Вы также можете создать собственный обработчик DIC. Это полезно, если у вас есть собственный
контейнер, который вы хотите использовать, но он не является PSR-11 (Dice). Смотрите
[пример выше](#basic-example) о том, как это сделать.

Кроме того,
есть некоторые полезные настройки по умолчанию, которые сделают вашу жизнь проще при использовании Flight.

### Экземпляр Engine

Если вы используете экземпляр `Engine` в ваших контроллерах/посреднике, вот
как его настроить:

```php

// Где-то в вашем файле инициализации
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

// Теперь вы можете использовать экземпляр Engine в ваших контроллерах/посреднике

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### Добавление других классов

Если у вас есть другие классы, которые вы хотите добавить в контейнер, то с Dice это легко, поскольку они будут автоматически разрешаться контейнером.
Вот пример:

```php

$container = new \Dice\Dice;
// Если вам не нужно внедрять что-либо в ваш класс
// вам нет необходимости определять что-либо!
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