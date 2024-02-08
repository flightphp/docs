# Routen-Middleware

Flight unterstützt Routen- und Gruppenrouten-Middleware. Middleware ist eine Funktion, die vor (oder nach) dem Routen-Callback ausgeführt wird. Dies ist ein großartiger Weg, um API-Authentifizierungsprüfungen in Ihrem Code hinzuzufügen oder zu überprüfen, ob der Benutzer die Berechtigung hat, auf die Route zuzugreifen.

## Grundlegende Middleware

Hier ist ein grundlegendes Beispiel:

```php
// Wenn Sie nur eine anonyme Funktion bereitstellen, wird sie vor dem Routen-Callback ausgeführt.
// Es gibt keine "nach" Middleware-Funktionen außer Klassen (siehe unten)
Flight::route('/pfad', function() { echo 'Hier bin ich!'; })->addMiddleware(function() {
	echo 'Middleware zuerst!';
});

Flight::start();

// Dies gibt "Middleware zuerst! Hier bin ich!" aus
```

Es gibt einige sehr wichtige Hinweise zur Middleware, die Sie kennen sollten, bevor Sie sie verwenden:
- Middleware-Funktionen werden in der Reihenfolge ausgeführt, in der sie zur Route hinzugefügt werden. Die Ausführung erfolgt ähnlich wie [Slim Framework damit umgeht](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Befores werden in der hinzugefügten Reihenfolge ausgeführt, und Afters werden in umgekehrter Reihenfolge ausgeführt.
- Wenn Ihre Middleware-Funktion false zurückgibt, wird die gesamte Ausführung gestoppt und ein 403 Forbidden-Fehler ausgelöst. Wahrscheinlich möchten Sie dies eleganter behandeln mit einem `Flight::redirect()` oder etwas Ähnlichem.
- Wenn Sie Parameter aus Ihrer Route benötigen, werden sie als einzelnes Array an Ihre Middleware-Funktion übergeben. (`function($params) { ... }` oder `public function before($params) {}`). Der Grund dafür ist, dass Sie Ihre Parameter in Gruppen strukturieren können und in einigen dieser Gruppen können Ihre Parameter tatsächlich in einer anderen Reihenfolge erscheinen, was die Middleware-Funktion durch Verweis auf den falschen Parameter zerstören würde. Auf diese Weise können Sie auf sie nach Namen anstatt nach Position zugreifen.

## Middleware-Klassen

Middleware kann auch als Klasse registriert werden. Wenn Sie die "nach" Funktionalität benötigen, **müssen** Sie eine Klasse verwenden.

```php
class MeineMiddleware {
	public function before($params) {
		echo 'Middleware zuerst!';
	}

	public function after($params) {
		echo 'Middleware zuletzt!';
	}
}

$MeineMiddleware = new MeineMiddleware();
Flight::route('/pfad', function() { echo 'Hier bin ich! '; })->addMiddleware($MeineMiddleware); // auch ->addMiddleware([ $MeineMiddleware, $MeineMiddleware2 ]);

Flight::start();

// Dies wird "Middleware zuerst! Hier bin ich! Middleware zuletzt!" anzeigen
```

## Gruppierung von Middleware

Sie können eine Routengruppe hinzufügen, und dann wird jede Route in dieser Gruppe auch dieselbe Middleware haben. Dies ist nützlich, wenn Sie eine Reihe von Routen beispielsweise durch eine Auth-Middleware gruppieren müssen, um den API-Schlüssel im Header zu überprüfen.

```php

// am Ende der group-Methode hinzugefügt
Flight::group('/api', function() {

	// Diese "leer" aussehende Route wird tatsächlich /api entsprechen
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/benutzer', function() { echo 'benutzer'; }, false, 'benutzer');
	Flight::route('/benutzer/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_anzeigen');
}, [ new ApiAuthMiddleware() ]);
```

Wenn Sie eine globale Middleware auf alle Ihre Routen anwenden möchten, können Sie eine "leere" Gruppe hinzufügen:

```php

// am Ende der group-Methode hinzugefügt
Flight::group('', function() {
	Flight::route('/benutzer', function() { echo 'benutzer'; }, false, 'benutzer');
	Flight::route('/benutzer/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_anzeigen');
}, [ new ApiAuthMiddleware() ]);
```