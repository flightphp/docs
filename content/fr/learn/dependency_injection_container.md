# Conteneur d'Injection de Dépendance

## Introduction

Le Conteneur d'Injection de Dépendance (CID) est un outil puissant qui vous permet de gérer 
les dépendances de votre application. C'est un concept clé dans les frameworks PHP modernes et 
est utilisé pour gérer l'instanciation et la configuration des objets. Certains exemples de bibliothèques CID 
sont : [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), et [league/container](https://container.thephpleague.com/).

Un CID est un moyen élégant de dire qu'il vous permet de créer et gérer vos classes dans un
emplacement centralisé. Cela est utile lorsque vous avez besoin de passer le même objet à
plusieurs classes (comme vos contrôleurs). Un exemple simple pourrait vous aider à mieux
comprendre.

## Exemple de Base

L'ancienne méthode de faire les choses pourrait ressembler à ceci :
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

Vous pouvez constater dans le code ci-dessus que nous créons un nouvel objet `PDO` et le passons
à notre classe `UserController`. C'est bien pour une petite application, mais à mesure que
votre application se développe, vous constaterez que vous créez le même objet `PDO` en plusieurs
endroits. C'est là qu'un CID est utile.

Voici le même exemple en utilisant un CID (en utilisant Dice) :
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
// n'oubliez pas de le réaffecter à lui-même comme ci-dessous!
$container = $container->addRule('PDO', [
	// shared signifie que le même objet sera renvoyé à chaque fois
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

Je parie que vous pourriez penser qu'il y a beaucoup de code supplémentaire ajouté à l'exemple.
La magie opère lorsque vous avez un autre contrôleur qui a besoin de l'objet `PDO`.

```php

// Si tous vos contrôleurs ont un constructeur qui a besoin d'un objet PDO
// chacune des routes ci-dessous l'injectera automatiquement !!!
Flight::route('/entreprise/@id', 'CompanyController->view');
Flight::route('/organisation/@id', 'OrganizationController->view');
Flight::route('/catégorie/@id', 'CategoryController->view');
Flight::route('/paramètres', 'SettingsController->view');
```

Le bonus supplémentaire de l'utilisation d'un CID est que les tests unitaires deviennent beaucoup plus faciles. Vous pouvez
créer un objet simulé et le passer à votre classe. C'est un énorme avantage lorsque vous
écrivez des tests pour votre application !

## PSR-11

Flight peut également utiliser tout conteneur conforme à PSR-11. Cela signifie que vous pouvez utiliser n'importe
quel conteneur qui implémente l'interface PSR-11. Voici un exemple en utilisant le conteneur PSR-11 de League :

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

Cela peut être un peu plus verbeux que l'exemple Dice précédent, mais cela fonctionne toujours
avec les mêmes avantages !

## Gestionnaire CID Personnalisé

Vous pouvez également créer votre propre gestionnaire CID. Ceci est utile si vous avez un conteneur personnalisé
que vous souhaitez utiliser qui n'est pas PSR-11 (Dice). Voir l'
[exemple de base](#basic-example) pour savoir comment faire cela.

De plus,
il existe quelques valeurs par défaut utiles qui faciliteront votre vie lors de l'utilisation de Flight.

### Instance du Moteur

Si vous utilisez l'instance `Engine` dans vos contrôleurs/middlewares, voici
comment vous le configureriez :

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

// Maintenant vous pouvez utiliser l'instance Engine dans vos contrôleurs/middlewares

class MonControleur {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### Ajout d'Autres Classes

Si vous avez d'autres classes que vous voulez ajouter au conteneur, avec Dice c'est facile car elles seront automatiquement résolues par le conteneur. Voici un exemple :

```php

$container = new \Dice\Dice;
// Si vous n'avez pas besoin d'injecter quelque chose dans votre classe
// vous n'avez pas besoin de définir quoi que ce soit !
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MaClassePersonnalisée {
	public function parseChose() {
		return 'chose';
	}
}

class UserController {

	protected MaClassePersonnalisée $MaClassePersonnalisée;

	public function __construct(MaClassePersonnalisée $MaClassePersonnalisée) {
		$this->MaClassePersonnalisée = $MaClassePersonnalisée;
	}

	public function index() {
		echo $this->MaClassePersonnalisée->parseChose();
	}
}

Flight::route('/utilisateur', 'UserController->index');
```  