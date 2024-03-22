# Dependency Injection Container

## Introduction

The Dependency Injection Container (DIC) is a powerful tool that allows you to manage
your application's dependencies. It is a key concept in modern PHP frameworks and is 
used to manage the instantiation and configuration of objects. Some examples of DIC 
libraries are: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), and [league/container](https://container.thephpleague.com/).

A DIC a fancy way of saying that it allows you to create and manage your classes in a
centralized location. This is useful for when you need to pass the same object to 
multiple classes (like your controllers). A simple example might help this make more
sense.

## Basic Example

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

$User = new UserController(new PDO('mysql:host=localhost;dbname=test', 'user', 'pass'));
Flight::route('/user/@id', [ $UserController, 'view' ]);

Flight::start();
```

You can see from the above code that we are creating a new `PDO` object and passing it
to our `UserController` class. This is fine for a small application, but as your
application grows, you will find that you are creating the same `PDO` object in multiple
places. This is where a DIC comes in handy.

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
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// or alternatively you can define the route like this
Flight::route('/user/@id', 'UserController->view');
// or
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

I bet you might be thinking that there was a lot of extra code added to the example.
The magic comes from when you have another controller that needs the `PDO` object. 

```php

// If all your controllers have a constructor that needs a PDO object
// each of the routes below will automatically have it injected!!!
Flight::route('/company/@id', 'CompanyController->view');
Flight::route('/organization/@id', 'OrganizationController->view');
Flight::route('/category/@id', 'CategoryController->view');
Flight::route('/settings', 'SettingsController->view');
```

The added bonus of utilizing a DIC is that unit testing becomes much easier. You can
create a mock object and pass it to your class. This is a huge benefit when you are
writing tests for your application!

## PSR-11

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

## Custom DIC Handler

You can also create your own DIC handler. This is useful if you have a custom
container that you want to use that is not PSR-11 (Dice). See the 
[basic example](#basic-example) for how to do this.

Additionally, there
are some helpful defaults that will make your life easier when using Flight.

### Engine Instance

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

### Adding Other Classes

If you have other classes that you want to add to the container, with Dice it's easy as they will be automatically resolved by the container. Here is an example:

```php

$container = new \Dice\Dice;
// If you don't need to inject anything into your class
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