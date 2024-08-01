```de
# Routen-Middleware

Flight unterstützt Routen- und Gruppenrouten-Middleware. Middleware ist eine Funktion, die vor (oder nach) dem Routenrückruf ausgeführt wird. Dies ist eine großartige Möglichkeit, API-Authentifizierungsprüfungen in Ihrem Code hinzuzufügen oder zu validieren, ob der Benutzer die Berechtigung hat, auf die Route zuzugreifen.

## Grundlegende Middleware

Hier ist ein grundlegendes Beispiel:

```php
// Wenn Sie nur eine anonyme Funktion bereitstellen, wird sie vor dem Routenrückruf ausgeführt. Es gibt keine "nach" Middleware-Funktionen außer Klassen (siehe unten)
Flight::route('/pfad', function() { echo ' Hier bin ich!'; })->addMiddleware(function() {
	echo 'Middleware zuerst!';
});

Flight::start();

// Dies gibt "Middleware zuerst! Hier bin ich!" aus
```

Es gibt einige sehr wichtige Hinweise zur Middleware, die Sie kennen sollten, bevor Sie sie verwenden:
- Middleware-Funktionen werden in der Reihenfolge ausgeführt, in der sie zur Route hinzugefügt werden. Die Ausführung ähnelt der Handhabung durch das [Slim Framework](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Before werden in der hinzugefügten Reihenfolge ausgeführt, und Afters werden in umgekehrter Reihenfolge ausgeführt.
- Wenn Ihre Middleware-Funktion false zurückgibt, wird die gesamte Ausführung gestoppt und ein 403 Forbidden error wird geworfen. Wahrscheinlich möchten Sie dies mit einem `Flight::redirect()` oder etwas Ähnlichem eleganter behandeln.
- Wenn Sie Parameter aus Ihrer Route benötigen, werden sie als einzelnes Array an Ihre Middleware-Funktion übergeben. (`function($params) { ... }` oder `public function before($params) {}`). Der Grund dafür ist, dass Sie Ihre Parameter in Gruppen strukturieren können und in einigen dieser Gruppen können Ihre Parameter tatsächlich in einer anderen Reihenfolge erscheinen, was die Middleware-Funktion brechen würde, indem sie sich auf den falschen Parameter bezieht. Auf diese Weise können Sie auf sie nach Namen und nicht nach Position zugreifen.
- Wenn Sie nur den Namen der Middleware übergeben, wird sie automatisch vom [dependency injection container](dependency-injection-container) ausgeführt, und die Middleware wird mit den benötigten Parametern ausgeführt. Wenn Sie keinen dependency injection container registriert haben, wird die `flight\Engine`-Instanz in den `__construct()` übergeben.

## Middleware-Klassen

Middleware kann auch als Klasse registriert werden. Wenn Sie die "nach" Funktionalität benötigen, **müssen** Sie eine Klasse verwenden.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware zuerst!';
	}

	public function after($params) {
		echo 'Middleware zuletzt!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/pfad', function() { echo ' Hier bin ich! '; })->addMiddleware($MyMiddleware); // auch ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Dies zeigt "Middleware zuerst! Hier bin ich! Middleware zuletzt!" an
```

## Behandlung von Middleware-Fehlern

Angenommen, Sie haben eine Authentifizierungs-Middleware und möchten den Benutzer auf eine Login-Seite umleiten, wenn er nicht authentifiziert ist. Sie haben ein paar Optionen zur Verfügung:

1. Sie können false aus der Middleware-Funktion zurückgeben, und Flight gibt automatisch einen 403 Forbidden error zurück, aber ohne Anpassung.
1. Sie können den Benutzer mit `Flight::redirect()` auf eine Login-Seite umleiten.
1. Sie können einen benutzerdefinierten Fehler innerhalb der Middleware erstellen und die Ausführung der Route stoppen.

### Grundlegendes Beispiel

Hier ist ein einfaches Beispiel mit return false;:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// da es true ist, geht einfach alles weiter
	}
}
```

### Umleitungsbeispiel

Hier ist ein Beispiel, um den Benutzer auf eine Login-Seite umzuleiten:
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

### Benutzerdefiniertes Fehlerbeispiel

Angenommen, Sie müssen einen JSON-Fehler auslösen, weil Sie eine API erstellen. Sie können das so machen:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'Sie müssen angemeldet sein, um auf diese Seite zuzugreifen.'], 403);
			// oder
			Flight::json(['error' => 'Sie müssen angemeldet sein, um auf diese Seite zuzugreifen.'], 403);
			exit;
			// oder
			Flight::halt(403, json_encode(['error' => 'Sie müssen angemeldet sein, um auf diese Seite zuzugreifen.']);
		}
	}
}
```

## Gruppierung von Middleware

Sie können eine Routengruppe hinzufügen, und dann wird jede Route in dieser Gruppe auch dieselbe Middleware haben. Dies ist nützlich, wenn Sie beispielsweise eine Reihe von Routen nach einer Auth-Middleware gruppieren müssen, um den API-Schlüssel im Header zu überprüfen.

```php

// am Ende der Gruppierungsmethode hinzugefügt
Flight::group('/api', function() {

	// Diese "leer" aussehende Route passt tatsächlich zu /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Dies passt zu /api/benutzer
    Flight::route('/users', function() { echo 'Benutzer'; }, false, 'benutzer');
	// Dies passt zu /api/benutzer/1234
	Flight::route('/users/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'user_ansicht');
}, [ new ApiAuthMiddleware() ]);
```

Wenn Sie eine globale Middleware auf alle Ihre Routen anwenden möchten, können Sie eine "leere" Gruppe hinzufügen:

```php

// am Ende der Gruppierungsmethode
Flight::group('', function() {

	// Dies ist immer noch /benutzer
	Flight::route('/users', function() { echo 'Benutzer'; }, false, 'benutzer');
	// Und das ist immer noch /benutzer/1234
	Flight::route('/users/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'user_ansicht');
}, [ new ApiAuthMiddleware() ]);
```