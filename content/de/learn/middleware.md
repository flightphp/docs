# Routen Middleware

Flight unterstützt Routen- und Gruppenrouten-Middleware. Middleware ist eine Funktion, die vor (oder nach) dem Routenrückruf ausgeführt wird. Dies ist eine großartige Möglichkeit, API-Authentifizierungsprüfungen in Ihrem Code hinzuzufügen oder zu überprüfen, ob der Benutzer die Berechtigung zum Zugriff auf die Route hat.

## Grundlegende Middleware

Hier ist ein grundlegendes Beispiel:

```php
// Wenn Sie nur eine anonyme Funktion angeben, wird sie vor dem Routenrückruf ausgeführt.
// Es gibt keine "nachher"-Middleware-Funktionen, außer für Klassen (siehe unten)
Flight::route('/pfad', function() { echo 'Hier bin ich!'; })->addMiddleware(function() {
	echo 'Middleware zuerst!';
});

Flight::start();

// Dies gibt "Middleware zuerst! Hier bin ich!" aus
```

Es gibt einige sehr wichtige Hinweise zur Middleware, die Sie beachten sollten, bevor Sie sie verwenden:
- Middleware-Funktionen werden in der Reihenfolge ausgeführt, in der sie der Route hinzugefügt wurden. Die Ausführung ähnelt der Vorgehensweise, wie [Slim Framework dies handhabt](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Befores werden in der hinzugefügten Reihenfolge ausgeführt, Afters werden in umgekehrter Reihenfolge ausgeführt.
- Wenn Ihre Middleware-Funktion false zurückgibt, wird die gesamte Ausführung gestoppt und es wird ein 403 Forbidden Fehler ausgelöst. Sie sollten dies wahrscheinlich mit `Flight::redirect()` oder etwas Ähnlichem eleganter behandeln.
- Wenn Sie Parameter aus Ihrer Route benötigen, werden sie als ein einzelnes Array an Ihre Middleware-Funktion übergeben. (`function($params) { ... }` oder `public function before($params) {}`). Der Grund dafür ist, dass Sie Ihre Parameter in Gruppen strukturieren können und in einigen dieser Gruppen Ihre Parameter tatsächlich in einer anderen Reihenfolge erscheinen können, was die Middleware-Funktion durch Verwendung des falschen Parameters brechen würde. Auf diese Weise können Sie auf sie nach Namen anstatt nach Position zugreifen.
- Wenn Sie nur den Namen der Middleware übergeben, wird sie automatisch vom [Abhängigkeitseinspritzungsbehälter](dependency-injection-container) ausgeführt und die Middleware wird mit den erforderlichen Parametern ausgeführt. Wenn Sie keinen registrierten Abhängigkeitseinspritzungsbehälter haben, wird die `flight\Engine`-Instanz in den `__construct()` übergeben.

## Middleware-Klassen

Middleware kann auch als Klasse registriert werden. Wenn Sie die "nachher"-Funktionalität benötigen, **müssen** Sie eine Klasse verwenden.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware zuerst!';
	}

	public function after($params) {
		echo 'Middleware zuletzt!';
	}
}

$MeineMiddleware = new MyMiddleware();
Flight::route('/pfad', function() { echo 'Hier bin ich! '; })->addMiddleware($MeineMiddleware); // auch ->addMiddleware([$MeineMiddleware, $MeineMiddleware2]);

Flight::start();

// Dies zeigt "Middleware zuerst! Hier bin ich! Middleware zuletzt!" an
```

## Behandlung von Middleware-Fehlern

Angenommen, Sie haben eine Auth-Middleware und möchten den Benutzer auf eine Login-Seite umleiten, wenn er nicht authentifiziert ist. Sie haben ein paar Optionen zur Verfügung:

1. Sie können false aus der Middleware-Funktion zurückgeben, und Flight gibt automatisch einen 403 Forbidden Fehler zurück, jedoch ohne Anpassung.
1. Sie können den Benutzer durch Verwendung von `Flight::redirect()` auf eine Login-Seite umleiten.
1. Sie können einen benutzerdefinierten Fehler innerhalb der Middleware erstellen und die Ausführung der Route anhalten.

### Grundlegendes Beispiel

Hier ist ein einfaches return false; Beispiel:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['benutzer']) === false) {
			return false;
		}

		// da es wahr ist, geht einfach alles weiter
	}
}
```

### Umleitungsbeispiel

Hier ist ein Beispiel, wie Sie den Benutzer auf eine Login-Seite umleiten:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['benutzer']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Benutzerdefiniertes Fehlerbeispiel

Angenommen, Sie müssen einen JSON-Fehler auslösen, weil Sie eine API erstellen. Sie können das folgendermaßen tun:
```php
class MyMiddleware {
	public function before($params) {
		$autorisation = Flight::request()->headers['Authorization'];
		if(empty($autorisation)) {
			Flight::json(['error' => 'Sie müssen angemeldet sein, um auf diese Seite zuzugreifen.'], 403);
			exit;
			// oder
			Flight::halt(403, json_encode(['error' => 'Sie müssen angemeldet sein, um auf diese Seite zuzugreifen.']);
		}
	}
}
```

## Gruppierung von Middleware

Sie können eine Routengruppe hinzufügen, und dann wird jede Route in dieser Gruppe ebenfalls dieselbe Middleware haben. Dies ist nützlich, wenn Sie eine Gruppe von Routen beispielsweise durch eine Auth-Middleware gruppieren müssen, um den API-Schlüssel im Header zu überprüfen.

```php

// am Ende der Gruppenmethode hinzugefügt
Flight::group('/api', function() {

	// Diese "leer" aussehende Route wird tatsächlich /api übereinstimmen
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/benutzer', function() { echo 'benutzer'; }, false, 'benutzer');
	Flight::route('/benutzer/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_ansicht');
}, [ new ApiAuthMiddleware() ]);
```

Wenn Sie eine globale Middleware auf alle Ihre Routen anwenden möchten, können Sie eine "leere" Gruppe hinzufügen:

```php

// am Ende der Gruppenmethode hinzugefügt
Flight::group('', function() {
	Flight::route('/benutzer', function() { echo 'benutzer'; }, false, 'benutzer');
	Flight::route('/benutzer/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_ansicht');
}, [ new ApiAuthMiddleware() ]);
```  