# Middleware

## Überblick

Flight unterstützt Route- und Gruppen-Route-Middleware. Middleware ist ein Teil Ihrer Anwendung, in dem Code ausgeführt wird, bevor 
(oder nach) dem Route-Callback. Dies ist eine großartige Möglichkeit, API-Authentifizierungsprüfungen in Ihrem Code hinzuzufügen oder zu überprüfen, ob 
der Benutzer die Berechtigung hat, auf die Route zuzugreifen.

## Verständnis

Middleware kann Ihre App erheblich vereinfachen. Anstatt komplexer abstrakter Klassenvererbung oder Methoden-Überschreibungen ermöglicht Middleware 
Ihnen, Ihre Routen zu steuern, indem Sie Ihre benutzerdefinierte App-Logik zuweisen. Sie können Middleware wie ein Sandwich betrachten. 
Sie haben Brot außen und dann Schichten von Zutaten wie Salat, Tomaten, Fleisch und Käse. Stellen Sie sich vor, 
jede Anfrage ist wie ein Bissen des Sandwiches, bei dem Sie zuerst die äußeren Schichten essen und zum Kern vordringen.

Hier ist eine visuelle Darstellung, wie Middleware funktioniert. Dann zeigen wir Ihnen ein praktisches Beispiel, wie dies funktioniert.

```text
Benutzeranfrage an URL /api ----> 
	Middleware->before() ausgeführt ----->
		Callable/ Methode an /api ausgeführt und Antwort generiert ------>
	Middleware->after() ausgeführt ----->
Benutzer erhält Antwort vom Server
```

Und hier ist ein praktisches Beispiel:

```text
Benutzer navigiert zu URL /dashboard
	LoggedInMiddleware->before() wird ausgeführt
		before() prüft auf gültige angemeldete Sitzung
			wenn ja, nichts tun und Ausführung fortsetzen
			wenn nein, Benutzer zu /login umleiten
				Callable/ Methode an /api ausgeführt und Antwort generiert
	LoggedInMiddleware->after() hat nichts definiert, also lässt es die Ausführung fortfahren
Benutzer erhält Dashboard-HTML vom Server
```

### Ausführungsreihenfolge

Middleware-Funktionen werden in der Reihenfolge ausgeführt, in der sie der Route hinzugefügt werden. Die Ausführung ähnelt der Art und Weise, wie [Slim Framework dies handhabt](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).

`before()`-Methoden werden in der Reihenfolge ausgeführt, in der sie hinzugefügt wurden, und `after()`-Methoden werden in umgekehrter Reihenfolge ausgeführt.

Beispiel: Middleware1->before(), Middleware2->before(), Middleware2->after(), Middleware1->after().

## Grundlegende Verwendung

Sie können Middleware als jede aufrufbare Methode verwenden, einschließlich einer anonymen Funktion oder einer Klasse (empfohlen).

### Anonyme Funktion

Hier ist ein einfaches Beispiel:

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Dies wird "Middleware first! Here I am!" ausgeben
```

> **Hinweis:** Bei der Verwendung einer anonymen Funktion wird nur eine `before()`-Methode interpretiert. Sie **können** kein `after()`-Verhalten mit einer anonymen Klasse definieren.

### Verwendung von Klassen

Middleware kann (und sollte) als Klasse registriert werden. Wenn Sie die "after"-Funktionalität benötigen, **müssen** Sie eine Klasse verwenden.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); 
// auch ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Dies wird "Middleware first! Here I am! Middleware last!" anzeigen
```

Sie können auch nur den Klassenname der Middleware definieren, und sie wird die Klasse instanziieren.

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **Hinweis:** Wenn Sie nur den Namen der Middleware übergeben, wird sie automatisch vom [Dependency Injection Container](dependency-injection-container) ausgeführt, und die Middleware wird mit den Parametern ausgeführt, die sie benötigt. Wenn kein Dependency Injection Container registriert ist, wird standardmäßig die `flight\Engine`-Instanz in den `__construct(Engine $app)` übergeben.

### Verwendung von Routen mit Parametern

Wenn Sie Parameter aus Ihrer Route benötigen, werden sie in einem einzelnen Array an Ihre Middleware-Funktion übergeben. (`function($params) { ... }` oder `public function before($params) { ... }`). Der Grund dafür ist, dass Sie Ihre Parameter in Gruppen strukturieren können und in einigen dieser Gruppen Ihre Parameter möglicherweise in einer anderen Reihenfolge erscheinen, was die Middleware-Funktion durch Verweis auf den falschen Parameter kaputt machen würde. Auf diese Weise können Sie sie nach Namen anstelle der Position zugreifen.

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];

		// jobId kann übergeben oder nicht übergeben werden
		$jobId = $params['jobId'] ?? 0;

		// vielleicht wenn es keine Job-ID gibt, müssen Sie nichts nachschlagen.
		if($jobId === 0) {
			return;
		}

		// Führen Sie eine Suche in Ihrer Datenbank durch
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {

	// Diese Gruppe unten erhält immer noch die Parent-Middleware
	// Aber die Parameter werden in einem einzelnen Array 
	// in der Middleware übergeben.
	$router->group('/job/@jobId', function(Router $router) {
		$router->get('', [ JobController::class, 'view' ]);
		$router->put('', [ JobController::class, 'update' ]);
		$router->delete('', [ JobController::class, 'delete' ]);
		// mehr Routen...
	});
}, [ RouteSecurityMiddleware::class ]);
```

### Gruppierung von Routen mit Middleware

Sie können eine Route-Gruppe hinzufügen, und dann wird jede Route in dieser Gruppe dieselbe Middleware haben. Dies ist 
nützlich, wenn Sie eine Menge von Routen gruppieren müssen, z. B. mit einer Auth-Middleware, um den API-Schlüssel im Header zu prüfen.

```php

// am Ende der group-Methode hinzugefügt
Flight::group('/api', function() {

	// Diese "leere" Route passt tatsächlich zu /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Dies passt zu /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Dies passt zu /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Wenn Sie eine globale Middleware auf alle Ihre Routen anwenden möchten, können Sie eine "leere" Gruppe hinzufügen:

```php

// am Ende der group-Methode hinzugefügt
Flight::group('', function() {

	// Dies ist immer noch /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Und dies ist immer noch /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // oder [ new ApiAuthMiddleware() ], dasselbe
```

### Häufige Anwendungsfälle

#### API-Schlüssel-Validierung
Wenn Sie Ihre `/api`-Routen schützen möchten, indem Sie überprüfen, ob der API-Schlüssel korrekt ist, können Sie das leicht mit Middleware handhaben.

```php
use flight\Engine;

class ApiMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}
	
	public function before(array $params) {
		$authorizationHeader = $this->app->request()->getHeader('Authorization');
		$apiKey = str_replace('Bearer ', '', $authorizationHeader);

		// Führen Sie eine Suche in Ihrer Datenbank für den API-Schlüssel durch
		$apiKeyHash = hash('sha256', $apiKey);
		$hasValidApiKey = !!$this->db()->fetchField("SELECT 1 FROM api_keys WHERE hash = ? AND valid_date >= NOW()", [ $apiKeyHash ]);

		if($hasValidApiKey !== true) {
			$this->app->jsonHalt(['error' => 'Invalid API Key']);
		}
	}
}

// routes.php
$router->group('/api', function(Router $router) {
	$router->get('/users', [ ApiController::class, 'getUsers' ]);
	$router->get('/companies', [ ApiController::class, 'getCompanies' ]);
	// mehr Routen...
}, [ ApiMiddleware::class ]);
```

Jetzt sind alle Ihre API-Routen durch diese API-Schlüssel-Validierungs-Middleware geschützt, die Sie eingerichtet haben! Wenn Sie mehr Routen in die Router-Gruppe einfügen, erhalten sie sofort denselben Schutz!

#### Anmeldungs-Validierung

Möchten Sie einige Routen schützen, damit sie nur für angemeldete Benutzer verfügbar sind? Das kann leicht mit Middleware erreicht werden!

```php
use flight\Engine;

class LoggedInMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$session = $this->app->session();
		if($session->get('logged_in') !== true) {
			$this->app->redirect('/login');
			exit;
		}
	}
}

// routes.php
$router->group('/admin', function(Router $router) {
	$router->get('/dashboard', [ DashboardController::class, 'index' ]);
	$router->get('/clients', [ ClientController::class, 'index' ]);
	// mehr Routen...
}, [ LoggedInMiddleware::class ]);
```

#### Route-Parameter-Validierung

Möchten Sie Ihre Benutzer schützen, indem Sie verhindern, dass sie Werte in der URL ändern, um auf Daten zuzugreifen, die sie nicht sollten? Das kann mit Middleware gelöst werden!

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];
		$jobId = $params['jobId'];

		// Führen Sie eine Suche in Ihrer Datenbank durch
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {
	$router->get('', [ JobController::class, 'view' ]);
	$router->put('', [ JobController::class, 'update' ]);
	$router->delete('', [ JobController::class, 'delete' ]);
	// mehr Routen...
}, [ RouteSecurityMiddleware::class ]);
```

## Handhabung der Middleware-Ausführung

Sagen wir, Sie haben eine Auth-Middleware und möchten den Benutzer auf eine Login-Seite umleiten, wenn er nicht authentifiziert ist. Sie haben ein paar Optionen zur Verfügung:

1. Sie können false von der Middleware-Funktion zurückgeben, und Flight gibt automatisch einen 403 Forbidden-Fehler zurück, aber ohne Anpassung.
1. Sie können den Benutzer auf eine Login-Seite umleiten mit `Flight::redirect()`.
1. Sie können einen benutzerdefinierten Fehler in der Middleware erstellen und die Ausführung der Route stoppen.

### Einfach und Unkompliziert

Hier ist ein einfaches `return false;`-Beispiel:

```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			return false;
		}

		// da es wahr ist, läuft alles einfach weiter
	}
}
```

### Umleitungs-Beispiel

Hier ist ein Beispiel für die Umleitung des Benutzers auf eine Login-Seite:
```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Benutzerdefiniertes Fehler-Beispiel

Sagen wir, Sie müssen einen JSON-Fehler werfen, weil Sie eine API bauen. Sie können das so tun:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->getHeader('Authorization');
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// oder
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// oder
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']));
		}
	}
}
```

## Siehe auch
- [Routing](/learn/routing) - Wie man Routen zu Controllern zuweist und Views rendert.
- [Requests](/learn/requests) - Verständnis, wie man eingehende Anfragen handhabt.
- [Responses](/learn/responses) - Wie man HTTP-Antworten anpasst.
- [Dependency Injection](/learn/dependency-injection-container) - Vereinfachung der Objekterstellung und -verwaltung in Routen.
- [Warum ein Framework?](/learn/why-frameworks) - Verständnis der Vorteile der Verwendung eines Frameworks wie Flight.
- [Middleware-Ausführungsstrategie-Beispiel](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## Fehlerbehebung
- Wenn Sie eine Umleitung in Ihrer Middleware haben, aber Ihre App scheint nicht umzuleiten, stellen Sie sicher, dass Sie eine `exit;`-Anweisung in Ihrer Middleware hinzufügen.

## Änderungsprotokoll
- v3.1: Unterstützung für Middleware hinzugefügt.