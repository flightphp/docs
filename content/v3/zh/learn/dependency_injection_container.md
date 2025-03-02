# 依赖注入容器

## 介绍

依赖注入容器（DIC）是一个强大的工具，允许您管理应用程序的依赖关系。这是现代 PHP 框架中的一个关键概念，用于管理对象的实例化和配置。一些 DIC 库示例包括：[Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), [PHP-DI](http://php-di.org/), 以及 [league/container](https://container.thephpleague.com/)。

DIC 是指以一种精致的方式让您在一个集中的位置创建和管理类。当您需要将同一个对象传递给多个类（例如您的控制器）时，这非常有用。一个简单的示例可能有助于更好地理解这一点。

## 基本示例

以前的做法可能类似这样：

```php

require 'vendor/autoload.php';

// 用于从数据库中管理用户的类
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

您可以从上面的代码中看到，我们正在创建一个新的 `PDO` 对象并将其传递给我们的 `UserController` 类。这对于一个小型应用程序来说是可以的，但随着应用程序的发展，您会发现在多个地方创建相同的 `PDO` 对象。这就是 DIC 发挥作用的地方。

以下是使用 DIC（使用 Dice）的相同示例：

```php

require 'vendor/autoload.php';

// 与上例相同的类。未更改任何内容
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

// 创建一个新容器
$container = new \Dice\Dice;
// 不要忘记像下面这样重新分配它给自己！
$container = $container->addRule('PDO', [
	// shared 意味着每次返回相同对象
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// 这会注册容器处理程序，以便 Flight 知道如何使用它。
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// 现在我们可以使用容器来创建我们的 UserController
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// 或者您还可以像这样定义路由
Flight::route('/user/@id', 'UserController->view');
// 或者
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

您可能认为在示例中有很多额外的代码。其中的魔法之处在于当您有另一个需要 `PDO` 对象的控制器时。

```php

// 如果您的所有控制器都有一个需要 PDO 对象的构造函数
// 下面的每个路由将自动注入它！！！
Flight::route('/company/@id', 'CompanyController->view');
Flight::route('/organization/@id', 'OrganizationController->view');
Flight::route('/category/@id', 'CategoryController->view');
Flight::route('/settings', 'SettingsController->view');
```

利用 DIC 的额外好处是进行单元测试变得更加简单。您可以创建一个模拟对象并将其传递给您的类。当您为应用程序编写测试时，这是一个巨大的好处！

## PSR-11

Flight 还可以使用任何符合 PSR-11 的容器。这意味着您可以使用实现 PSR-11 接口的任何容器。以下是使用 League 的 PSR-11 容器的示例：

```php

require 'vendor/autoload.php';

// 与上面相同的 UserController 类

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

与之前 Dice 示例相比，这可能会更冗长一些，但仍然可以以相同的好处完成工作！

## 自定义 DIC 处理程序

您还可以创建自己的 DIC 处理程序。如果您有一个不符合 PSR-11（Dice）的自定义容器，这会很有用。参见 [基本示例](#basic-example) 了解如何处理。

另外，在使用 Flight 时，还有一些有用的默认设置可以让您更轻松。

### 引擎实例

如果您在控制器/中间件中使用 `Engine` 实例，这是您配置它的方式：

```php

// 在您的引导文件中的某处
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// 这是您传递实例的位置
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// 现在您可以在控制器/中间件中使用 Engine 实例

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### 添加其他类

如果您想要将其他类添加到容器中，使用 Dice 很容易，因为它们将自动由容器解析。以下是一个示例：

```php

$container = new \Dice\Dice;
// 如果您不需要向您的类注入任何内容
// 您不需要定义任何内容！
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