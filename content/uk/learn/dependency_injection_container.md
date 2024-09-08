# Контейнер для впровадження залежностей

## Вступ

Контейнер для впровадження залежностей (DIC) — це потужний інструмент, який дозволяє вам керувати залежностями вашого застосунку. Це ключова концепція в сучасних PHP фреймворках і використовується для управління створенням та налаштуванням об'єктів. Деякі приклади бібліотек DIC: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/) та [league/container](https://container.thephpleague.com/).

DIC - це вишуканий спосіб сказати, що він дозволяє вам створювати та керувати вашими класами в централізованому місці. Це корисно, коли вам потрібно передавати один і той же об'єкт кільком класам (як-от вашим контролерам). Простий приклад може допомогти це зрозуміти.

## Основний приклад

Старий спосіб виконання завдань може виглядати так:
```php

require 'vendor/autoload.php';

// клас для управління користувачами з бази даних
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

Ви можете побачити з наведеного вище коду, що ми створюємо новий об'єкт `PDO` і передаємо його нашому класу `UserController`. Це нормально для малих застосунків, але коли ваш застосунок росте, ви виявите, що створюєте один і той же об'єкт `PDO` в кількох місцях. Ось тут DIC стає в нагоді.

Ось той же приклад, використовуючи DIC (використовуючи Dice):
```php

require 'vendor/autoload.php';

// той же клас, що й вище. Нічого не змінилося
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
// не забудьте перепризначити його, як показано нижче!
$container = $container->addRule('PDO', [
	// спільний означає, що той же об'єкт буде повертатися щоразу
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Це реєструє обробник контейнера, щоб Flight знав, що використовувати його.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// тепер ми можемо використовувати контейнер для створення нашого UserController
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// або, альтернативно, ви можете визначити маршрут таким чином
Flight::route('/user/@id', 'UserController->view');
// або
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

Можливо, вам здається, що до прикладу було додано багато зайвого коду. Чарівність виникає, коли у вас є інший контролер, який потребує об'єкт `PDO`. 

```php

// Якщо всі ваші контролери мають конструктор, який потребує об'єкт PDO
// кожен з маршрутів нижче автоматично буде мати його впровадженим!!!
Flight::route('/company/@id', 'CompanyController->view');
Flight::route('/organization/@id', 'OrganizationController->view');
Flight::route('/category/@id', 'CategoryController->view');
Flight::route('/settings', 'SettingsController->view');
```

Додатковим бонусом від використання DIC є те, що модульне тестування стає значно легшим. Ви можете створити змодельований об'єкт і передати його своєму класу. Це величезна перевага, коли ви пишете тести для свого застосунку!

## PSR-11

Flight може також використовувати будь-який контейнер, сумісний з PSR-11. Це означає, що ви можете використовувати будь-який контейнер, який реалізує інтерфейс PSR-11. Ось приклад, як використовувати контейнер PSR-11 від League:

```php

require 'vendor/autoload.php';

// той же клас UserController, що й вище

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

Це може бути трохи більш розлогим, ніж попередній приклад з Dice, але все одно виконує свою роботу з такими ж перевагами!

## Користувацький обробник DIC

Ви також можете створити власний обробник DIC. Це корисно, якщо у вас є власний контейнер, який ви хочете використовувати, який не є PSR-11 (Dice). Дивіться [основний приклад](#основний-приклад) для того, як це зробити.

Крім того, є деякі корисні значення за замовчуванням, які полегшать вам життя при використанні Flight.

### Екземпляр двигуна

Якщо ви використовуєте екземпляр `Engine` у своїх контролерах/проміжних програмах, ось як ви його налаштуєте:

```php

// Десь у вашому файлі завантаження
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Ось тут ви передаєте екземпляр
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Тепер ви можете використовувати екземпляр Engine у своїх контролерах/проміжних програмах

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### Додавання інших класів

Якщо у вас є інші класи, які ви хочете додати до контейнера, з Dice це легко, оскільки вони автоматично будуть визначатися контейнером. Ось приклад:

```php

$container = new \Dice\Dice;
// Якщо вам не потрібно впроваджувати нічого в свій клас
// ви не повинні нічого визначати!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MyCustomClass {
	public function parseThing() {
		return 'річ';
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