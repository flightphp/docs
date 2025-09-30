# Dependency Injection Container

## Overview

Der Dependency Injection Container (DIC) ist eine leistungsstarke Erweiterung, die es Ihnen ermöglicht, die Abhängigkeiten Ihrer Anwendung zu verwalten.

## Understanding

Dependency Injection (DI) ist ein zentrales Konzept in modernen PHP-Frameworks und wird verwendet, um die Instanziierung und Konfiguration von Objekten zu verwalten. Einige Beispiele für DIC-Bibliotheken sind: [flightphp/container](https://github.com/flightphp/container), [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), und [league/container](https://container.thephpleague.com/).

Ein DIC ist eine elegante Möglichkeit, Ihre Klassen an einem zentralen Ort zu erstellen und zu verwalten. Dies ist nützlich, wenn Sie dasselbe Objekt an mehrere Klassen weitergeben müssen (z. B. an Ihre Controller oder Middleware).

## Basic Usage

Die alte Methode könnte so aussehen:
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

Aus dem obigen Code können Sie sehen, dass wir ein neues `PDO`-Objekt erstellen und es an unsere `UserController`-Klasse weitergeben. Das ist für eine kleine Anwendung in Ordnung, aber wenn Ihre Anwendung wächst, werden Sie feststellen, dass Sie dasselbe `PDO`-Objekt an mehreren Stellen erstellen oder weitergeben. Hier kommt ein DIC ins Spiel.

Hier ist dasselbe Beispiel mit einem DIC (unter Verwendung von Dice):
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

Ich wette, Sie denken, dass eine Menge zusätzlicher Code zum Beispiel hinzugefügt wurde. Die Magie entsteht, wenn Sie einen anderen Controller haben, der das `PDO`-Objekt benötigt.

```php

// If all your controllers have a constructor that needs a PDO object
// each of the routes below will automatically have it injected!!!
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

Der zusätzliche Vorteil der Nutzung eines DIC ist, dass Unit-Testing viel einfacher wird. Sie können ein Mock-Objekt erstellen und es an Ihre Klasse weitergeben. Das ist ein großer Vorteil, wenn Sie Tests für Ihre Anwendung schreiben!

### Creating a centralized DIC handler

Sie können einen zentralen DIC-Handler in Ihrer Services-Datei erstellen, indem Sie Ihre App [erweitern](/learn/extending). Hier ist ein Beispiel:

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

Flight hat ein Plugin, das einen einfachen PSR-11-konformen Container bereitstellt, den Sie zur Handhabung Ihrer Dependency Injection verwenden können. Hier ist ein schnelles Beispiel, wie Sie es verwenden:

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

Sie können auch Abhängigkeiten rekursiv auflösen. Hier ist ein Beispiel:

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

Sie können auch Ihren eigenen DIC-Handler erstellen. Das ist nützlich, wenn Sie einen benutzerdefinierten Container verwenden möchten, der nicht PSR-11 ist (Dice). Siehe den Abschnitt 
[basic usage](#basic-usage) für die Vorgehensweise.

Zusätzlich gibt es einige hilfreiche Standardeinstellungen, die Ihr Leben mit Flight erleichtern.

#### Engine Instance

Wenn Sie die `Engine`-Instanz in Ihren Controllern/Middleware verwenden, hier ist, wie Sie sie konfigurieren würden:

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

Wenn Sie andere Klassen zum Container hinzufügen möchten, ist das mit Dice einfach, da sie automatisch vom Container aufgelöst werden. Hier ist ein Beispiel:

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

Flight kann auch jeden PSR-11-konformen Container verwenden. Das bedeutet, dass Sie jeden Container verwenden können, der die PSR-11-Schnittstelle implementiert. Hier ist ein Beispiel mit Leagues PSR-11-Container:

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

Das kann etwas ausführlicher sein als das vorherige Dice-Beispiel, es erledigt dennoch die Aufgabe mit denselben Vorteilen!

## See Also
- [Extending Flight](/learn/extending) - Lernen Sie, wie Sie Dependency Injection zu Ihren eigenen Klassen hinzufügen können, indem Sie das Framework erweitern.
- [Configuration](/learn/configuration) - Lernen Sie, wie Sie Flight für Ihre Anwendung konfigurieren.
- [Routing](/learn/routing) - Lernen Sie, wie Sie Routen für Ihre Anwendung definieren und wie Dependency Injection mit Controllern funktioniert.
- [Middleware](/learn/middleware) - Lernen Sie, wie Sie Middleware für Ihre Anwendung erstellen und wie Dependency Injection mit Middleware funktioniert.

## Troubleshooting
- Wenn Sie Probleme mit Ihrem Container haben, stellen Sie sicher, dass Sie die korrekten Klassennamen an den Container weitergeben.

## Changelog
- v3.7.0 - Hinzugefügt: Möglichkeit, einen DIC-Handler zu Flight zu registrieren.