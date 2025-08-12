# Route Middleware

Flight unterstützt Route- und Gruppen-Route-Middleware. Middleware ist eine Funktion, die vor (oder nach) dem Route-Callback ausgeführt wird. Dies ist eine großartige Möglichkeit, API-Authentifizierungsprüfungen in Ihrem Code hinzuzufügen oder zu überprüfen, ob der Benutzer die Berechtigung hat, auf die Route zuzugreifen.

## Basic Middleware

Hier ist ein grundlegendes Beispiel:

```php
// Wenn Sie nur eine anonyme Funktion angeben, wird sie vor dem Route-Callback ausgeführt. 
// es gibt keine "after"-Middleware-Funktionen außer für Klassen (siehe unten)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Dies wird "Middleware first! Here I am!" ausgeben.
```

Es gibt einige sehr wichtige Hinweise zu Middleware, die Sie vor der Nutzung beachten sollten:
- Middleware-Funktionen werden in der Reihenfolge ausgeführt, in der sie der Route hinzugefügt werden. Die Ausführung ist ähnlich wie bei [Slim Framework](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - "Befores" werden in der Reihenfolge ausgeführt, in der sie hinzugefügt wurden, und "Afters" werden in umgekehrter Reihenfolge ausgeführt.
- Wenn Ihre Middleware-Funktion false zurückgibt, wird die gesamte Ausführung gestoppt und ein 403 Forbidden-Fehler wird ausgelöst. Sie möchten das wahrscheinlich eleganter handhaben, z. B. mit `Flight::redirect()` oder etwas Ähnlichem.
- Wenn Sie Parameter von Ihrer Route benötigen, werden sie als einzelnes Array an Ihre Middleware-Funktion übergeben. (`function($params) { ... }` oder `public function before($params) {}`). Der Grund dafür ist, dass Sie Ihre Parameter in Gruppen strukturieren können und in einigen dieser Gruppen die Parameter in einer anderen Reihenfolge erscheinen könnten, was die Middleware-Funktion durch Verweis auf den falschen Parameter brechen würde. Auf diese Weise können Sie sie nach Namen statt nach Position zugreifen.
- Wenn Sie nur den Namen der Middleware angeben, wird sie automatisch durch den [Dependency Injection Container](dependency-injection-container) ausgeführt und die Middleware wird mit den benötigten Parametern ausgeführt. Wenn kein Dependency Injection Container registriert ist, wird die `flight\Engine`-Instanz in den `__construct()` übergeben.

## Middleware Classes

Middleware kann auch als Klasse registriert werden. Wenn Sie die "after"-Funktionalität benötigen, **müssen** Sie eine Klasse verwenden.

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // auch ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Dies wird "Middleware first! Here I am! Middleware last!" anzeigen.
```

## Handling Middleware Errors

Nehmen wir an, Sie haben eine Auth-Middleware und möchten den Benutzer auf eine Login-Seite umleiten, wenn er nicht authentifiziert ist. Sie haben ein paar Optionen zur Verfügung:

1. Sie können false von der Middleware-Funktion zurückgeben und Flight wird automatisch einen 403 Forbidden-Fehler zurückgeben, aber ohne Anpassung.
1. Sie können den Benutzer mit `Flight::redirect()` auf eine Login-Seite umleiten.
1. Sie können einen benutzerdefinierten Fehler in der Middleware erstellen und die Ausführung der Route stoppen.

### Basic Example

Hier ist ein einfaches Beispiel mit return false;:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// da es true ist, geht alles einfach weiter
	}
}
```

### Redirect Example

Hier ist ein Beispiel, das den Benutzer auf eine Login-Seite umleitet:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Custom Error Example

Nehmen wir an, Sie müssen einen JSON-Fehler auslösen, weil Sie eine API erstellen. Sie können das wie folgt tun:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
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

## Grouping Middleware

Sie können eine Route-Gruppe hinzufügen, und dann wird jede Route in dieser Gruppe die gleiche Middleware haben. Dies ist nützlich, wenn Sie eine Gruppe von Routen z. B. mit einer Auth-Middleware gruppieren müssen, um den API-Schlüssel im Header zu überprüfen.

```php

// hinzugefügt am Ende der group-Methode
Flight::group('/api', function() {

	// Diese "leere" Route passt tatsächlich zu /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Dies passt zu /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Dies passt zu /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Wenn Sie eine globale Middleware für all Ihre Routen anwenden möchten, können Sie eine "leere" Gruppe hinzufügen:

```php

// hinzugefügt am Ende der group-Methode
Flight::group('', function() {

	// Dies ist immer noch /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Und dies ist immer noch /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // oder [ new ApiAuthMiddleware() ], dasselbe
```