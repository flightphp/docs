# Контейнер внедрения зависимостей

## Вступление

Контейнер внедрения зависимостей (DIC) является мощным инструментом, который позволяет управлять
зависимостями вашего приложения. Это ключевая концепция в современных PHP фреймворках и
используется для управления созданием и настройкой объектов. Некоторые примеры DIC
библиотек: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), и [league/container](https://container.thephpleague.com/).

DIC - это удобный способ сказать, что он позволяет создавать и управлять вашими классами в
централизованном месте. Это полезно, когда вам нужно передавать один и тот же объект
в несколько классов (например, в ваши контроллеры). Простой пример может помочь лучше
понять это.

## Основной пример

В старом подходе это могло выглядеть примерно так:
```php

require 'vendor/autoload.php';

// класс для управления пользователями в базе данных
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

Вы видите из приведенного выше кода, что мы создаем новый объект `PDO` и передаем его
в наш класс `UserController`. Это нормально для небольших приложений, но по мере
роста вашего приложения вы обнаружите, что создаете тот же объект `PDO` в нескольких
местах. Вот где пригодится DIC.

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
// не забудьте переприсвоить его самому себе, как показано ниже!
$container = $container->addRule('PDO', [
	// shared означает, что каждый раз будет возвращаться один и тот же объект
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Это регистрирует обработчик контейнера, чтобы Flight знал, что его использовать.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// теперь мы можем использовать контейнер для создания нашего UserController
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// или, альтернативно, вы можете определить маршрут так
Flight::route('/user/@id', 'UserController->view');
// или
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

Возможно, вы подумаете, что в пример было добавлено много лишнего кода.
Магия заключается в том, что когда у вас есть другой контроллер, который нуждается в объекте `PDO`. 

```php

// Если все ваши контроллеры имеют конструктор, который нуждается в объекте PDO
// каждый из нижеуказанных маршрутов автоматически будет его использовать!!!
Flight::route('/company/@id', 'CompanyController->view');
Flight::route('/organization/@id', 'OrganizationController->view');
Flight::route('/category/@id', 'CategoryController->view');
Flight::route('/settings', 'SettingsController->view');
```

Дополнительный бонус использования DIC заключается в том, что тестирование модулей становится намного проще. Вы можете
создать имитационный объект и передать его в ваш класс. Это огромное преимущество при написании тестов для вашего приложения!

## PSR-11

Flight также может использовать любой совместимый с PSR-11 контейнер. Это означает, что вы можете использовать любой
контейнер, который реализует интерфейс PSR-11. Вот пример использования контейнера PSR-11 от League:

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

Хотя это может быть немного более многословным, чем предыдущий пример с Dice, все равно
делает свою работу с теми же преимуществами!

## Пользовательский обработчик DIC

Вы также можете создать свой собственный обработчик DIC. Это полезно, если у вас есть пользовательский
контейнер, который вы хотите использовать и который не является PSR-11 (Dice). См. 
[основной пример](#basic-example) для того, как это сделать.

Кроме того, имеются некоторые полезные значения по умолчанию, которые сделают вашу жизнь проще при использовании Flight.

### Экземпляр Engine

Если вы используете экземпляр `Engine` в своих контроллерах/промежуточных уровнях, вот
как вы можете его настроить:

```php

// Где-то в вашем файле инициализации
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Здесь передается экземпляр
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Теперь вы можете использовать экземпляр Engine в ваших контроллерах/промежуточных уровнях

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

Если у вас есть другие классы, которые вы хотите добавить в контейнер, с Dice это легко, так как они будут автоматически разрешены контейнером. Вот пример:

```php

$container = new \Dice\Dice;
// Если вам не нужно внедрять что-либо в ваш класс
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