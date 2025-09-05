# Routen-Middleware

Flight unterstützt Routen- und Gruppen-Routen-Middleware. Middleware ist eine Funktion, die vor (oder nach) dem Routen-Callback ausgeführt wird. Dies ist ein großartiger Weg, um API-Authentifizierungsprüfungen in Ihrem Code hinzuzufügen oder zu überprüfen, ob der Benutzer die Berechtigung hat, auf die Route zuzugreifen.

## Grundlegende Middleware

Hier ist ein grundlegendes Beispiel:

```php
// Wenn Sie nur eine anonyme Funktion angeben, wird sie vor dem Routen-Callback ausgeführt. 
// Es gibt keine "after"-Middleware-Funktionen außer für Klassen (siehe unten)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Dies wird "Middleware first! Here I am!" ausgeben
```

Es gibt einige sehr wichtige Hinweise zu Middleware, die Sie beachten sollten, bevor Sie sie verwenden:
- Middleware-Funktionen werden in der Reihenfolge ausgeführt, in der sie der Route hinzugefügt werden. Die Ausführung ist ähnlich wie bei [Slim Framework handles this](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Befores werden in der Reihenfolge ausgeführt, in der sie hinzugefügt wurden, und Afters werden in umgekehrter Reihenfolge ausgeführt.
- Wenn Ihre Middleware-Funktion false zurückgibt, wird die gesamte Ausführung gestoppt und ein 403 Forbidden-Fehler wird ausgelöst. Sie möchten das wahrscheinlich eleganter handhaben, z. B. mit einem `Flight::redirect()` oder etwas Ähnlichem.
- Wenn Sie Parameter von Ihrer Route benötigen, werden sie als einzelnes Array an Ihre Middleware-Funktion übergeben. (`function($params) { ... }` oder `public function before($params) {}`). Der Grund dafür ist, dass Sie Ihre Parameter in Gruppen strukturieren können und in einigen dieser Gruppen die Parameter in einer anderen Reihenfolge erscheinen könnten, was die Middleware-Funktion durch Verweis auf den falschen Parameter brechen würde. Auf diese Weise können Sie sie nach Namen anstatt nach Position zugreifen.
- Wenn Sie nur den Namen der Middleware angeben, wird sie automatisch vom [dependency injection container](dependency-injection-container) ausgeführt und die Middleware wird mit den Parametern ausgeführt, die sie benötigt. Wenn Sie keinen Dependency Injection Container registriert haben, wird die `flight\Engine`-Instanz in den `__construct()` übergeben.

## Middleware-Klassen

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

// Dies wird "Middleware first! Here I am! Middleware last!" anzeigen
```

## Handhabung von Middleware-Fehlern

Nehmen wir an, Sie haben eine Auth-Middleware und möchten den Benutzer auf eine Login-Seite umleiten, wenn er nicht authentifiziert ist. Sie haben ein paar Optionen zur Verfügung:

1. Sie können false von der Middleware-Funktion zurückgeben und Flight wird automatisch einen 403 Forbidden-Fehler zurückgeben, aber ohne Anpassung.
1. Sie können den Benutzer mit `Flight::redirect()` auf eine Login-Seite umleiten.
1. Sie können einen benutzerdefinierten Fehler in der Middleware erstellen und die Ausführung der Route stoppen.

### Grundlegendes Beispiel

Hier ist ein einfaches Beispiel mit return false;:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// Da es true ist, geht alles einfach weiter
	}
}
```

### Umleitungsbeispiel

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

### Benutzerdefinierter Fehler-Beispiel

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
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## Gruppierung von Middleware

Sie können eine Routengruppe hinzufügen, und dann wird jede Route in dieser Gruppe die gleiche Middleware haben. Dies ist nützlich, wenn Sie eine Gruppe von Routen, sagen wir durch eine Auth-Middleware, gruppieren müssen, um den API-Schlüssel im Header zu überprüfen.

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

Wenn Sie eine globale Middleware für all Ihre Routen anwenden möchten, können Sie eine "leere" Gruppe hinzufügen:

```php

// am Ende der group-Methode hinzugefügt
Flight::group('', function() {

	// Dies ist immer noch /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Und dies ist immer noch /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // oder [ new ApiAuthMiddleware() ], dasselbe
```