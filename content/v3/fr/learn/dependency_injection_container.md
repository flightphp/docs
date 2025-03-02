# Conteneur d'injection de dépendances

## Introduction

Le Conteneur d'injection de dépendances (DIC) est un outil puissant qui vous permet de gérer
les dépendances de votre application. C'est un concept clé dans les frameworks PHP modernes et est
utilisé pour gérer l'instanciation et la configuration des objets. Quelques exemples de bibliothèques DIC
sont : [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/),
[PHP-DI](http://php-di.org/) et [league/container](https://container.thephpleague.com/).

Un DIC est un moyen sophistiqué de dire qu'il vous permet de créer et de gérer vos classes dans
un emplacement centralisé. C'est utile lorsque vous avez besoin de passer le même objet à
plusieurs classes (comme vos contrôleurs). Un exemple simple pourrait aider à mieux comprendre cela.

## Exemple de base

L'ancienne manière de faire ressemblait à ceci :
```php

require 'vendor/autoload.php';

// classe pour gérer les utilisateurs de la base de données
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

Vous pouvez voir dans le code ci-dessus que nous créons un nouvel objet `PDO` et le passons
à notre classe `UserController`. C'est bien pour une petite application, mais à mesure que votre
application grandit, vous constaterez que vous créez le même objet `PDO` à plusieurs
endroits. C'est là qu'un DIC s'avère utile.

Voici le même exemple utilisant un DIC (en utilisant Dice) :
```php

require 'vendor/autoload.php';

// même classe que ci-dessus. Rien n'a changé
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

// créer un nouveau conteneur
$container = new \Dice\Dice;
// n'oubliez pas de le réassigner comme ci-dessous !
$container = $container->addRule('PDO', [
	// shared signifie que le même objet sera retourné à chaque fois
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Cela enregistre le gestionnaire de conteneur pour que Flight sache l'utiliser.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// maintenant nous pouvons utiliser le conteneur pour créer notre UserController
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// ou alternativement vous pouvez définir la route comme ceci
Flight::route('/user/@id', 'UserController->view');
// ou
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

Je parie que vous pouvez penser qu'il y a beaucoup de code supplémentaire ajouté à l'exemple.
La magie opère lorsque vous avez un autre contrôleur qui a besoin de l'objet `PDO`.

```php

// Si tous vos contrôleurs ont un constructeur qui a besoin d'un objet PDO
// chacune des routes ci-dessous l'injectera automatiquement !!!
Flight::route('/company/@id', 'CompanyController->view');
Flight::route('/organization/@id', 'OrganizationController->view');
Flight::route('/category/@id', 'CategoryController->view');
Flight::route('/settings', 'SettingsController->view');
```

Le bonus supplémentaire d'utiliser un DIC est que les tests unitaires deviennent beaucoup plus faciles. Vous pouvez
créer un objet simulé et le passer à votre classe. C'est un énorme avantage lorsque vous
rédigez des tests pour votre application!

## PSR-11

Flight peut également utiliser n'importe quel conteneur compatible avec PSR-11. Cela signifie que vous pouvez utiliser n'importe
un conteneur qui implémente l'interface PSR-11. Voici un exemple utilisant le conteneur PSR-11 de League :

```php

require 'vendor/autoload.php';

// même classe UserController que ci-dessus

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

Bien que cela puisse être un peu plus verbeux que l'exemple Dice précédent, cela fonctionne tout de même
avec les mêmes avantages!

## Gestionnaire DIC personnalisé

Vous pouvez également créer votre propre gestionnaire DIC. C'est utile si vous avez un conteneur
personnalisé que vous voulez utiliser qui n'est pas PSR-11 (Dice). Consultez l'
[exemple de base](#basic-example) pour savoir comment faire cela.

De plus, il y a
quelques valeurs par défaut utiles qui faciliteront votre vie lors de l'utilisation de Flight.

### Instance du moteur

Si vous utilisez l'instance `Engine` dans vos contrôleurs/middleware, voici
comment vous la configureriez :

```php

// Quelque part dans votre fichier d'amorçage
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// C'est ici que vous passez l'instance
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Maintenant vous pouvez utiliser l'instance Engine dans vos contrôleurs/middleware

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### Ajout d'autres classes

Si vous avez d'autres classes que vous voulez ajouter au conteneur, avec Dice c'est facile car elles seront automatiquement résolues par le conteneur. Voici un exemple :

```php

$container = new \Dice\Dice;
// Si vous n'avez pas besoin d'injecter quoi que ce soit dans votre classe
// vous n'avez pas besoin de définir quoi que ce soit !
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