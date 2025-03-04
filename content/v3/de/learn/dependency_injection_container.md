# Dependency Injection Container

## Einführung

Der Dependency Injection Container (DIC) ist ein leistungsstolles Werkzeug, das es ermöglicht, die Abhängigkeiten Ihrer Anwendung zu verwalten. Es ist ein Schlüsselkonzept in modernen PHP-Frameworks und wird verwendet, um die Instanziierung und Konfiguration von Objekten zu verwalten. Einige Beispiele für DIC-Bibliotheken sind: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), [PHP-DI](http://php-di.org/) und [league/container](https://container.thephpleague.com/).

Ein DIC ist eine elegante Möglichkeit zu sagen, dass es Ihnen ermöglicht, Ihre Klassen an einem zentralen Ort zu erstellen und zu verwalten. Dies ist nützlich, wenn Sie dasselbe Objekt an mehrere Klassen übergeben müssen (wie Ihre Controller). Ein einfaches Beispiel könnte dies verständlicher machen.

## Grundbeispiel

Der alte Weg, Dinge zu erledigen, könnte so aussehen:
```php

require 'vendor/autoload.php';

// Klasse zur Verwaltung von Benutzern aus der Datenbank
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

Sie können im obigen Code sehen, dass wir ein neues `PDO`-Objekt erstellen und es an unsere `UserController`-Klasse übergeben. Dies ist in Ordnung für eine kleine Anwendung, aber wenn Ihre Anwendung wächst, werden Sie feststellen, dass Sie das gleiche `PDO`-Objekt an mehreren Stellen erstellen. Hier kommt ein DIC ins Spiel.

Hier ist das gleiche Beispiel unter Verwendung eines DICs (unter Verwendung von Dice):
```php

require 'vendor/autoload.php';

// dieselbe Klasse wie oben. Nichts geändert
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

// erstelle einen neuen Container
$container = new \Dice\Dice;
// vergiss nicht, ihn wie unten erneut zuzuweisen!
$container = $container->addRule('PDO', [
	// shared bedeutet, dass jedes Mal dasselbe Objekt zurückgegeben wird
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Hiermit wird der Container-Handler registriert, damit Flight weiß, dass er ihn verwenden soll.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// jetzt können wir den Container verwenden, um unseren UserController zu erstellen
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// oder alternativ können Sie die Route wie folgt definieren
Flight::route('/user/@id', 'UserController->view');
// oder
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

Sie denken vielleicht, dass viel zusätzlicher Code zum Beispiel hinzugefügt wurde. Die Magie entsteht, wenn Sie einen anderen Controller haben, der das `PDO`-Objekt benötigt.

```php

// Wenn alle Ihre Controller einen Konstruktor haben, der ein PDO-Objekt benötigt
// wird dies automatisch für jede der untenstehenden Routen eingefügt!!!
Flight::route('/company/@id', 'CompanyController->view');
Flight::route('/organization/@id', 'OrganizationController->view');
Flight::route('/category/@id', 'CategoryController->view');
Flight::route('/settings', 'SettingsController->view');
```

Der zusätzliche Vorteil der Nutzung eines DICs besteht darin, dass Unittests wesentlich einfacher werden. Sie können ein Mock-Objekt erstellen und es an Ihre Klasse übergeben. Dies ist ein großer Vorteil, wenn Sie Tests für Ihre Anwendung schreiben!

## PSR-11

Flight kann auch jeden PSR-11-kompatiblen Container verwenden. Dies bedeutet, dass Sie jeden Container verwenden können, der das PSR-11-Interface implementiert. Hier ist ein Beispiel für die Verwendung des PSR-11-Containers von League:

```php

require 'vendor/autoload.php';

// dieselbe UserController-Klasse wie oben

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

Obwohl dies etwas ausführlicher ist als das vorherige Dice-Beispiel, erledigt es dennoch die Aufgabe mit denselben Vorteilen!

## Eigener DIC-Handler

Sie können auch Ihren eigenen DIC-Handler erstellen. Dies ist nützlich, wenn Sie einen benutzerdefinierten Container haben, den Sie verwenden möchten, der nicht PSR-11 (Dice) ist. Sehen Sie sich das [Grundbeispiel](#basic-example) dafür an.

Darüber hinaus gibt es einige nützliche Standardeinstellungen, die Ihnen das Leben erleichtern, wenn Sie Flight verwenden.

### Engine-Instanz

Wenn Sie die `Engine`-Instanz in Ihren Controllern/Middleware verwenden, so konfigurieren Sie diese:

```php

// Irgendwo in Ihrer Startdatei
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Hier geben Sie die Instanz ein
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Nun können Sie die Engine-Instanz in Ihren Controllern/Middleware verwenden

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### Hinzufügen anderer Klassen

Wenn Sie andere Klassen in den Container aufnehmen möchten, ist dies mit Dice einfach, da sie automatisch vom Container aufgelöst werden. Hier ist ein Beispiel:

```php

$container = new \Dice\Dice;
// Wenn Sie nichts in Ihre Klasse injizieren müssen,
// müssen Sie nichts definieren!
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