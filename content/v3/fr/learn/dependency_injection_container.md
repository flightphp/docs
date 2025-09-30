# Conteneur d'injection de dépendances

## Aperçu

Le Conteneur d'injection de dépendances (DIC) est une amélioration puissante qui vous permet de gérer
les dépendances de votre application.

## Comprendre

L'injection de dépendances (DI) est un concept clé dans les frameworks PHP modernes et est 
utilisée pour gérer l'instanciation et la configuration des objets. Certains exemples de bibliothèques DIC 
sont : [flightphp/container](https://github.com/flightphp/container), [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), et [league/container](https://container.thephpleague.com/).

Un DIC est une façon élégante de vous permettre de créer et de gérer vos classes dans un
emplacement centralisé. Cela est utile lorsque vous devez passer le même objet à 
plusieurs classes (comme vos contrôleurs ou middleware par exemple).

## Utilisation de base

L'ancienne façon de faire les choses pourrait ressembler à ceci :
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

Vous pouvez voir dans le code ci-dessus que nous créons un nouvel objet `PDO` et le passons
à notre classe `UserController`. Cela est correct pour une petite application, mais à mesure que
votre application grandit, vous constaterez que vous créez ou passez le même objet `PDO` 
à plusieurs endroits. C'est là qu'un DIC entre en jeu de manière pratique.

Voici le même exemple en utilisant un DIC (avec Dice) :
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

Je parie que vous pensez peut-être qu'il y a beaucoup de code supplémentaire ajouté à l'exemple.
La magie vient quand vous avez un autre contrôleur qui a besoin de l'objet `PDO`.

```php

// If all your controllers have a constructor that needs a PDO object
// each of the routes below will automatically have it injected!!!
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

L'avantage supplémentaire d'utiliser un DIC est que les tests unitaires deviennent beaucoup plus faciles. Vous pouvez
créer un objet mock et le passer à votre classe. C'est un énorme avantage lorsque vous écrivez des tests pour votre application !

### Créer un gestionnaire DIC centralisé

Vous pouvez créer un gestionnaire DIC centralisé dans votre fichier de services en [étendant](/learn/extending) votre application. Voici un exemple :

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

Flight dispose d'un plugin qui fournit un conteneur simple conforme à PSR-11 que vous pouvez utiliser pour gérer
votre injection de dépendances. Voici un exemple rapide de son utilisation :

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

#### Utilisation avancée de flightphp/container

Vous pouvez également résoudre les dépendances de manière récursive. Voici un exemple :

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

Vous pouvez également créer votre propre gestionnaire DIC. Cela est utile si vous avez un conteneur personnalisé
que vous souhaitez utiliser qui n'est pas conforme à PSR-11 (Dice). Voir la 
[section d'utilisation de base](#basic-usage) pour savoir comment faire.

De plus, il
y a des valeurs par défaut utiles qui rendront votre vie plus facile lors de l'utilisation de Flight.

#### Instance Engine

Si vous utilisez l'instance `Engine` dans vos contrôleurs/middleware, voici
comment vous la configureriez :

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

#### Ajouter d'autres classes

Si vous avez d'autres classes que vous souhaitez ajouter au conteneur, avec Dice c'est facile car elles seront automatiquement résolues par le conteneur. Voici un exemple :

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

Flight peut également utiliser n'importe quel conteneur conforme à PSR-11. Cela signifie que vous pouvez utiliser n'importe quel
conteneur qui implémente l'interface PSR-11. Voici un exemple en utilisant le conteneur PSR-11 de League :

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

Cela peut être un peu plus verbeux que l'exemple précédent avec Dice, cela accomplit
toujours la tâche avec les mêmes avantages !

## Voir aussi
- [Extending Flight](/learn/extending) - Apprenez comment vous pouvez ajouter l'injection de dépendances à vos propres classes en étendant le framework.
- [Configuration](/learn/configuration) - Apprenez comment configurer Flight pour votre application.
- [Routing](/learn/routing) - Apprenez comment définir des routes pour votre application et comment l'injection de dépendances fonctionne avec les contrôleurs.
- [Middleware](/learn/middleware) - Apprenez comment créer du middleware pour votre application et comment l'injection de dépendances fonctionne avec le middleware.

## Dépannage
- Si vous rencontrez des problèmes avec votre conteneur, assurez-vous de passer les noms de classes corrects au conteneur.

## Journal des modifications
- v3.7.0 - Ajout de la possibilité d'enregistrer un gestionnaire DIC avec Flight.