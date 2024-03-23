# Dependency Injection Container

## Einführung

Der Dependency Injection Container (DIC) ist ein leistungsstolles Werkzeug, das es Ihnen ermöglicht, die Abhängigkeiten Ihrer Anwendung zu verwalten. Es handelt sich um ein Schlüsselkonzept in modernen PHP-Frameworks und wird zur Verwaltung der Instanziierung und Konfiguration von Objekten verwendet. Einige Beispiele für DIC-Bibliotheken sind: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), [PHP-DI](http://php-di.org/) und [league/container](https://container.thephpleague.com/).

Ein DIC ist eine elegante Möglichkeit zu sagen, dass es Ihnen ermöglicht, Ihre Klassen an einem zentralen Ort zu erstellen und zu verwalten. Dies ist nützlich, wenn Sie dasselbe Objekt an mehrere Klassen (wie Ihre Controller) übergeben müssen. Ein einfaches Beispiel könnte dabei helfen, dies verständlicher zu machen.

## Grundlegendes Beispiel

Der alte Weg, Dinge zu erledigen, könnte wie folgt aussehen:
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

Man kann aus dem obigen Code erkennen, dass wir ein neues `PDO`-Objekt erstellen und es unserer `UserController`-Klasse übergeben. Dies ist für eine kleine Anwendung in Ordnung, aber wenn Ihre Anwendung wächst, werden Sie feststellen, dass Sie das gleiche `PDO`-Objekt an mehreren Stellen erstellen. Hier kommt ein DIC zum Einsatz.

Hier ist dasselbe Beispiel unter Verwendung eines DIC (mit Dice):
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

// einen neuen Container erstellen
$container = new \Dice\Dice;
// vergessen Sie nicht, es wie unten sich selbst neu zuzuweisen!
$container = $container->addRule('PDO', [
	// shared bedeutet, dass jedes Mal dasselbe Objekt zurückgegeben wird
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Hiermit wird der Container Handler registriert, sodass Flight weiß, dass er ihn verwenden soll.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Jetzt können wir den Container verwenden, um unseren UserController zu erstellen
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// oder alternativ können Sie die Route wie folgt definieren
Flight::route('/user/@id', 'UserController->view');
// oder
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

Ich wette, Sie denken, es wurde viel zusätzlicher Code zum Beispiel hinzugefügt.
Die Magie entsteht, wenn Sie einen weiteren Controller haben, der das `PDO`-Objekt benötigt.

```php

// Wenn all Ihre Controller einen Konstruktor haben, der ein PDO-Objekt benötigt
// werden jedem der unten stehenden Routen automatisch injiziert!!!
Flight::route('/unternehmen/@id', 'Unternehmenscontroller->view');
Flight::route('/organisation/@id', 'Organisationscontroller->view');
Flight::route('/kategorie/@id', 'Kategoriescontroller->view');
Flight::route('/einstellungen', 'Einstellungscontroller->view');
```

Der zusätzliche Bonus bei der Verwendung eines DIC ist, dass das Unit Testing deutlich einfacher wird. Sie können ein Mock-Objekt erstellen und es Ihrer Klasse übergeben. Das ist ein großer Vorteil, wenn Sie Tests für Ihre Anwendung schreiben!

## PSR-11

Flight kann auch jeden Container verwenden, der mit PSR-11 kompatibel ist. Dies bedeutet, dass Sie jeden Container verwenden können, der das PSR-11-Interface implementiert. Hier ist ein Beispiel unter Verwendung des PSR-11-Containers von League:

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

Flight::route('/benutzer', [ 'Benutzercontroller', 'view' ]);

Flight::start();
```

Dies kann etwas ausführlicher sein als das vorherige Dice-Beispiel, erledigt jedoch die gleiche Arbeit mit den gleichen Vorteilen!

## Eigener DIC-Handler

Sie können auch Ihren eigenen DIC-Handler erstellen. Dies ist nützlich, wenn Sie einen benutzerdefinierten Container verwenden möchten, der nicht PSR-11-konform ist (Dice). Sehen Sie sich das [Grundbeispiel](#grundlegendes-beispiel) an, um herauszufinden, wie dies funktioniert.

Zusätzlich gibt es einige hilfreiche Standards, die Ihr Leben erleichtern, wenn Sie Flight verwenden.

### Engine-Instanz

Wenn Sie die `Engine`-Instanz in Ihren Controllern/Middleware verwenden, erhalten Sie hier die Konfiguration:

```php

// Irgendwo in Ihrer Startdatei
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Hier übergeben Sie die Instanz
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Jetzt können Sie die Engine-Instanz in Ihren Controllern/Middleware verwenden

class MeinController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### Hinzufügen weiterer Klassen

Wenn Sie andere Klassen dem Container hinzufügen möchten, ist dies mit Dice einfach, da sie automatisch vom Container aufgelöst werden. Hier ist ein Beispiel:

```php

$container = new \Dice\Dice;
// Wenn Sie nichts in Ihre Klasse einspeisen müssen
// müssen Sie nichts definieren!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MeineBenutzerdefinierteKlasse {
	public function analyseThing() {
		return 'Sache';
	}
}

class Benutzercontroller {

	protected MyCustomClass $MyCustomClass;

	public function __construct(MyCustomClass $MyCustomClass) {
		$this->MyCustomClass = $MyCustomClass;
	}

	public function index() {
		echo $this->MyCustomClass->parseThing();
	}
}

Flight::route('/benutzer', 'Benutzercontroller->index');
```