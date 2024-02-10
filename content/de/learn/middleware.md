# Routen Middleware

Flight unterstützt Routen- und Gruppenrouten-Middleware. Middleware ist eine Funktion, die vor (oder nach) dem Routen-Callback ausgeführt wird. Dies ist eine großartige Möglichkeit, API-Authentifizierungsprüfungen in Ihrem Code hinzuzufügen oder zu überprüfen, ob der Benutzer die Berechtigung zum Zugriff auf die Route hat.

## Basis-Middleware

Hier ist ein grundlegendes Beispiel:

```php
// Wenn Sie nur eine anonyme Funktion bereitstellen, wird sie vor dem Routen-Callback ausgeführt.
// Es gibt keine "nach"-Middleware-Funktionen außer Klassen (siehe unten)
Flight::route('/pfad', function() { echo 'Hier bin ich!'; })->addMiddleware(function() {
	echo 'Middleware zuerst!';
});

Flight::start();

// Dies gibt "Middleware zuerst! Hier bin ich!" aus
```

Es gibt einige sehr wichtige Hinweise zur Middleware, die Sie kennen sollten, bevor Sie sie verwenden:
- Middleware-Funktionen werden in der Reihenfolge ausgeführt, in der sie zur Route hinzugefügt werden. Die Ausführung ähnelt der Vorgehensweise von [Slim Framework dies behandelt](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Befores werden in der hinzugefügten Reihenfolge ausgeführt, und Afters werden in umgekehrter Reihenfolge ausgeführt.
- Wenn Ihre Middleware-Funktion false zurückgibt, wird die gesamte Ausführung gestoppt und es wird ein 403 Forbidden Error ausgelöst. Sie möchten dies wahrscheinlich mit einer `Flight::redirect()` oder etwas Ähnlichem eleganter behandeln.
- Wenn Sie Parameter aus Ihrer Route benötigen, werden sie als Array an Ihre Middleware-Funktion übergeben. (`function($params) { ... }` oder `public function before($params) {}`). Der Grund dafür ist, dass Sie Ihre Parameter in Gruppen strukturieren können und in einigen dieser Gruppen Ihre Parameter tatsächlich in einer anderen Reihenfolge auftauchen könnten, was die Middleware-Funktion zerbrechen würde, indem sie auf den falschen Parameter verweist. Auf diese Weise können Sie auf sie nach Namen anstelle von Position zugreifen.

## Middleware-Klassen

Middleware kann auch als Klasse registriert werden. Wenn Sie die "nach"-Funktionalität benötigen, **müssen** Sie eine Klasse verwenden.

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
Flight::route('/pfad', function() { echo 'Hier bin ich! '; })->addMiddleware($MyMiddleware); // auch ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Dies zeigt "Middleware zuerst! Hier bin ich! Middleware zuletzt!" an
```

## Gruppierung von Middleware

Sie können eine Routengruppe hinzufügen, und dann wird jede Route in dieser Gruppe auch die gleiche Middleware haben. Dies ist nützlich, wenn Sie z. B. eine Auth-Middleware benötigen, um den API-Schlüssel im Header zu überprüfen.

```php

// am Ende der Gruppenmethode hinzugefügt
Flight::group('/api', function() {

	// Diese "leer" aussehende Route wird tatsächlich mit /api übereinstimmen
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/benutzer', function() { echo 'benutzer'; }, false, 'benutzer');
	Flight::route('/benutzer/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_anzeigen');
}, [ new ApiAuthMiddleware() ]);
```

Wenn Sie eine globale Middleware auf alle Ihre Routen anwenden möchten, können Sie eine "leere" Gruppe hinzufügen:

```php

// am Ende der Gruppenmethode hinzugefügt
Flight::group('', function() {
	Flight::route('/benutzer', function() { echo 'benutzer'; }, false, 'benutzer');
	Flight::route('/benutzer/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_anzeigen');
}, [ new ApiAuthMiddleware() ]);
```