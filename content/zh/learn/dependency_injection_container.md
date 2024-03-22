# 依赖注入容器

## 介绍

依赖注入容器（DIC）是一个强大的工具，允许您管理应用程序的依赖关系。它是现代PHP框架中的关键概念，用于管理对象的实例化和配置。一些DIC库的例子包括：[Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), [PHP-DI](http://php-di.org/), 以及 [league/container](https://container.thephpleague.com/)。

DIC是一个说法华丽的方式，它允许您在一个集中的位置创建和管理您的类。当您需要将同一个对象传递给多个类（比如您的控制器）时，这是非常有用的。一个简单的例子可能有助于更清晰地理解这个概念。

## 基本示例

以前的做法可能看起来像这样：
```php

require 'vendor/autoload.php';

// 用于从数据库管理用户的类
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

从上面的代码可以看出，我们正在创建一个新的`PDO`对象并将其传递给我们的`UserController`类。对于一个小型应用程序来说，这是可以接受的，但随着应用程序的增长，您会发现自己在多个地方创建相同的`PDO`对象。这就是DIC派上用场的地方。

以下是使用DIC（使用Dice）的相同示例：
```php

require 'vendor/autoload.php';

// 与上述相同的类，没有更改
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
// 不要忘记像下面这样重复分配给自己！
$container = $container->addRule('PDO', [
	// 共享表示每次返回相同对象
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// 这样注册容器处理程序，以便Flight知道如何使用它。
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// 现在我们可以使用容器来创建我们的UserController
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// 或者，您可以像这样定义路由
Flight::route('/user/@id', 'UserController->view');
// 或
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

我敢打赌，您可能认为示例中添加了很多额外的代码。魔法之处在于当您有另一个需要`PDO`对象的控制器时。

```php

// 如果您的所有控制器都有一个需要PDO对象的构造函数
// 下面的每个路由将自动注入它！！！
Flight::route('/company/@id', 'CompanyController->view');
Flight::route('/organization/@id', 'OrganizationController->view');
Flight::route('/category/@id', 'CategoryController->view');
Flight::route('/settings', 'SettingsController->view');
```

利用DIC的额外好处是单元测试变得更加容易。您可以创建一个模拟对象并将其传递给您的类。当您为应用程序编写测试时，这是一个巨大的好处！

## PSR-11

Flight还可以使用任何符合PSR-11的容器。这意味着您可以使用任何实现PSR-11接口的容器。这里是使用League的PSR-11容器的示例：

```php

require 'vendor/autoload.php';

// 与上述相同的UserController类

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

它可能比之前的Dice示例更冗长一些，但仍然以相同的好处完成工作！

## 自定义DIC处理程序

您还可以创建自己的DIC处理程序。如果您有一个想要使用而不是PSR-11（Dice）的自定义容器，这将会很有用。查看如何执行此操作的[基本示例](#基本示例)。

此外，在使用Flight时还有一些有用的默认设置可以让您的生活更轻松。

### 引擎实例

如果您在控制器/中间件中使用`Engine`实例，这里是如何配置它：

```php

// 在您的引导文件中的某处
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// 这是您传入实例的位置
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// 现在您可以在您的控制器/中间件中使用引擎实例

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

如果您有其他要添加到容器中的类，在Dice中很容易，因为它们将自动由容器解析。这里是一个示例：

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