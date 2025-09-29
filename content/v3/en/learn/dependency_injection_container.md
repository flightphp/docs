# Dependency Injection Container

## Overview

	The Dependency Injection Container (DIC) is a powerful enhancement that allows you to manage
your application's dependencies. 

## Understanding

Dependency Injection (DI) is a key concept in modern PHP frameworks and is 
used to manage the instantiation and configuration of objects. Some examples of DIC 
libraries are: [flightphp/container](https://github.com/flightphp/container), [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), and [league/container](https://container.thephpleague.com/).

A DIC a fancy way of allowing you to create and manage your classes in a
centralized location. This is useful for when you need to pass the same object to 
multiple classes (like your controllers or middleware for instance). 

## Basic Usage

The old way of doing things might look like this:
```php

require 'vendor/autoload.php';

// class to manage users from the database
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

// in your routes.php file

$db = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$UserController = new UserController($db);
Flight::route('/user/@id', [ $UserController, 'view' ]);
// other UserController routes...

Flight::start();
```

You can see from the above code that we are creating a new `PDO` object and passing it
to our `UserController` class. This is fine for a small application, but as your
application grows, you will find that you are creating or passing around the same `PDO` 
object in multiple places. This is where a DIC comes in handy.

Here is the same example using a DIC (using Dice):
```php

require 'vendor/autoload.php';

// same class as above. Nothing changed
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

// create a new container
$container = new \Dice\Dice;

// add a rule to tell the container how to create a PDO object
// don't forget to reassign it to itself like below!
$container = $container->addRule('PDO', [
	// shared means that the same object will be returned each time
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// This registers the container handler so Flight knows to use it.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// now we can use the container to create our UserController
Flight::route('/user/@id', [ UserController::class, 'view' ]);

Flight::start();
```

I bet you might be thinking that there was a lot of extra code added to the example.
The magic comes from when you have another controller that needs the `PDO` object. 

```php

// If all your controllers have a constructor that needs a PDO object
// each of the routes below will automatically have it injected!!!
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

The added bonus of utilizing a DIC is that unit testing becomes much easier. You can
create a mock object and pass it to your class. This is a huge benefit when you are
writing tests for your application!

### Creating a centralized DIC handler

You can create a centralized DIC handler in your services file by [extending](/learn/extending) your app. Here's an example:

```php
// services.php

// create a new container
$container = new \Dice\Dice;
// don't forget to reassign it to itself like below!
$container = $container->addRule('PDO', [
	// shared means that the same object will be returned each time
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// now we can create a mappable method to create any object. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// This registers the container handler so Flight knows to use it for controllers/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// lets say we have the following sample class that takes a PDO object in the constructor
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// code that sends an email
	}
}

// And finally you can create objects using dependency injection
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

### `flightphp/container`

Flight has a plugin that provides a simple PSR-11 compliant container that you can use to handle
your dependency injection. Here's a quick example of how to use it:

```php

// index.php for example
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
	// will output this correctly!
  }
}

Flight::route('GET /', [TestController::class, 'index']);

Flight::start();
```

#### Advanced Usage of flightphp/container

You can also resolve dependencies recursively. Here's an example:

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
    // Implementation ...
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

You can also create your own DIC handler. This is useful if you have a custom
container that you want to use that is not PSR-11 (Dice). See the 
[basic usage](#basic-usage) section for how to do this.

Additionally, there
are some helpful defaults that will make your life easier when using Flight.

#### Engine Instance

If you are using the `Engine` instance in your controllers/middleware, here is
how you would configure it:

```php

// Somewhere in your bootstrap file
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// This is where you pass in the instance
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Now you can use the Engine instance in your controllers/middleware

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

#### Adding Other Classes

If you have other classes that you want to add to the container, with Dice it's easy as they will be automatically resolved by the container. Here is an example:

```php

$container = new \Dice\Dice;
// If you don't need to inject any dependencies into your classes
// you don't need to define anything!
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

Flight can also use any PSR-11 compliant container. This means that you can use any
container that implements the PSR-11 interface. Here is an example using League's
PSR-11 container:

```php

require 'vendor/autoload.php';

// same UserController class as above

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

This can be a little more verbose than the previous Dice example, it still
gets the job done with the same benefits!

## See Also
- [Extending Flight](/learn/extending) - Learn how you can add dependency injection to your own classes by extending the framework.
- [Configuration](/learn/configuration) - Learn how to configure Flight for your application.
- [Routing](/learn/routing) - Learn how to define routes for your application and how dependency injection works with controllers.
- [Middleware](/learn/middleware) - Learn how to create middleware for your application and how dependency injection works with middleware.