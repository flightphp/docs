# 依赖注入容器

## 概述

依赖注入容器 (DIC) 是一个强大的增强功能，它允许您管理应用程序的依赖关系。

## 理解

依赖注入 (DI) 是现代 PHP 框架中的一个关键概念，用于管理对象的实例化和配置。一些 DIC 库的示例包括：[flightphp/container](https://github.com/flightphp/container)、[Dice](https://r.je/dice)、[Pimple](https://pimple.symfony.com/)、
[PHP-DI](http://php-di.org/) 和 [league/container](https://container.thephpleague.com/)。

DIC 是一种花哨的方式，允许您在集中位置创建和管理您的类。这对于需要将同一个对象传递给多个类（例如您的控制器或中间件）时非常有用。

## 基本用法

以前的方式可能看起来像这样：
```php

require 'vendor/autoload.php';

// 从数据库管理用户的类
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

// 在您的 routes.php 文件中

$db = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$UserController = new UserController($db);
Flight::route('/user/@id', [ $UserController, 'view' ]);
// 其他 UserController 路由...

Flight::start();
```

从上面的代码中，您可以看到我们创建了一个新的 `PDO` 对象并将其传递给我们的 `UserController` 类。对于小型应用程序来说，这没问题，但随着应用程序的增长，您会发现自己在多个地方创建或传递相同的 `PDO` 对象。这就是 DIC 派上用场的地方。

这里是使用 DIC 的相同示例（使用 Dice）：
```php

require 'vendor/autoload.php';

// 与上面相同的类。没有变化
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

// 创建一个新的容器
$container = new \Dice\Dice;

// 添加一个规则，告诉容器如何创建 PDO 对象
// 不要忘记像下面一样将其重新赋值给自身！
$container = $container->addRule('PDO', [
	// shared 表示每次都会返回相同的对象
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// 这注册了容器处理程序，以便 Flight 知道使用它。
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// 现在我们可以使用容器来创建我们的 UserController
Flight::route('/user/@id', [ UserController::class, 'view' ]);

Flight::start();
```

我猜您可能会想，这个示例添加了很多额外的代码。魔力来自于当您有另一个需要 `PDO` 对象的控制器时。

```php

// 如果您的所有控制器都有一个需要 PDO 对象的构造函数
// 下面的每个路由都会自动注入它！！！
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

使用 DIC 的额外好处是单元测试变得更容易。您可以创建一个模拟对象并将其传递给您的类。当您为应用程序编写测试时，这是一个巨大的好处！

### 创建集中式的 DIC 处理程序

您可以通过[扩展](/learn/extending)您的应用程序，在 services 文件中创建一个集中式的 DIC 处理程序。以下是一个示例：

```php
// services.php

// 创建一个新的容器
$container = new \Dice\Dice;
// 不要忘记像下面一样将其重新赋值给自身！
$container = $container->addRule('PDO', [
	// shared 表示每次都会返回相同的对象
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// 现在我们可以创建一个可映射的方法来创建任何对象。
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// 这注册了容器处理程序，以便 Flight 知道用于控制器/中间件
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// 假设我们有一个以下示例类，它在构造函数中接受 PDO 对象
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// 发送电子邮件的代码
	}
}

// 最后，您可以使用依赖注入创建对象
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

### `flightphp/container`

Flight 有一个插件，它提供了一个简单的符合 PSR-11 的容器，您可以使用它来处理依赖注入。以下是使用它的快速示例：

```php

// 例如 index.php
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
	// 将正确输出这个！
  }
}

Flight::route('GET /', [TestController::class, 'index']);

Flight::start();
```

#### flightphp/container 的高级用法

您也可以递归解析依赖关系。以下是一个示例：

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
    // 实现 ...
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

您也可以创建自己的 DIC 处理程序。如果您有一个自定义的容器想要使用它而不是 PSR-11 (Dice)，这很有用。请参阅[basic usage](#basic-usage)部分了解如何做。

此外，还有一些有用的默认设置，当使用 Flight 时会让您的生活更容易。

#### Engine 实例

如果您在控制器/中间件中使用 `Engine` 实例，以下是配置它的方式：

```php

// 在您的引导文件中某处
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// 这就是您传递实例的地方
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

#### 添加其他类

如果您有其他想要添加到容器的类，使用 Dice 很容易，因为它们将被容器自动解析。以下是一个示例：

```php

$container = new \Dice\Dice;
// 如果您不需要向您的类注入任何依赖关系
// 您不需要定义任何东西！
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

Flight 也可以使用任何符合 PSR-11 的容器。这意味着您可以使用任何实现了 PSR-11 接口的容器。以下是使用 League 的 PSR-11 容器的示例：

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

这可能比之前的 Dice 示例更冗长，但它仍然能完成相同的好处！

## 另请参阅
- [扩展 Flight](/learn/extending) - 了解如何通过扩展框架将依赖注入添加到您自己的类中。
- [配置](/learn/configuration) - 了解如何为您的应用程序配置 Flight。
- [路由](/learn/routing) - 了解如何为您的应用程序定义路由，以及依赖注入如何与控制器一起工作。
- [中间件](/learn/middleware) - 了解如何为您的应用程序创建中间件，以及依赖注入如何与中间件一起工作。

## 故障排除
- 如果您的容器有问题，请确保您向容器传递正确的类名。

## 更新日志
- v3.7.0 - 添加了向 Flight 注册 DIC 处理程序的能力。