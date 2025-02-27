# Erweiterung

Flight ist als erweiterbares Framework konzipiert. Das Framework wird mit einer Reihe von Standardmethoden und -komponenten geliefert, ermöglicht es Ihnen jedoch, Ihre eigenen Methoden zuzuordnen, Ihre eigenen Klassen zu registrieren oder sogar vorhandene Klassen und Methoden zu überschreiben.

Wenn Sie nach einem DIC (Dependency Injection Container) suchen, wechseln Sie zur [Dependency Injection Container](dependency-injection-container) Seite.

## Methoden zuordnen

Um Ihre eigene einfache benutzerdefinierte Methode zuzuordnen, verwenden Sie die `map` Funktion:

```php
// Ordnen Sie Ihre Methode zu
Flight::map('hello', function (string $name) {
  echo "hallo $name!";
});

// Rufen Sie Ihre benutzerdefinierte Methode auf
Flight::hello('Bob');
```

Obwohl es möglich ist, einfache benutzerdefinierte Methoden zu erstellen, wird empfohlen, einfach standardmäßige Funktionen in PHP zu erstellen. Dies bietet Autocomplete in IDEs und ist einfacher zu lesen. Das Äquivalent des obigen Codes wäre:

```php
function hello(string $name) {
  echo "hallo $name!";
}

hello('Bob');
```

Dies wird häufiger verwendet, wenn Sie Variablen in Ihre Methode übergeben müssen, um einen erwarteten Wert zu erhalten. Die Verwendung der `register()` Methode wie unten ist mehr für die Übergabe von Konfigurationen gedacht und dann um Ihre vorkonfigurierte Klasse aufzurufen.

## Klassen registrieren

Um Ihre eigene Klasse zu registrieren und zu konfigurieren, verwenden Sie die `register` Funktion:

```php
// Registrieren Sie Ihre Klasse
Flight::register('user', User::class);

// Holen Sie sich eine Instanz Ihrer Klasse
$user = Flight::user();
```

Die Registermethode erlaubt es Ihnen auch, Parameter an den Klassenkonstruktor weiterzugeben. Wenn Sie also Ihre benutzerdefinierte Klasse laden, wird sie vorinitialisiert. Sie können die Konstruktorparameter definieren, indem Sie ein zusätzliches Array übergeben. Hier ist ein Beispiel für das Laden einer Datenbankverbindung:

```php
// Registrieren Sie die Klasse mit Konstruktorparametern
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Holen Sie sich eine Instanz Ihrer Klasse
// Dies wird ein Objekt mit den definierten Parametern erstellen
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// und falls Sie es später in Ihrem Code benötigen, rufen Sie einfach dieselbe Methode erneut auf
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Wenn Sie einen zusätzlichen Rückrufparameter übergeben, wird dieser sofort nach dem Konstruktor der Klasse ausgeführt. Dies ermöglicht es Ihnen, alle erforderlichen Einrichtungsverfahren für Ihr neues Objekt durchzuführen. Die Rückruffunktion nimmt einen Parameter, eine Instanz des neuen Objekts.

```php
// Der Rückruf erhält das Objekt, das konstruiert wurde
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Standardmäßig erhalten Sie jedes Mal, wenn Sie Ihre Klasse laden, eine gemeinsame Instanz. Um eine neue Instanz einer Klasse zu erhalten, übergeben Sie einfach `false` als Parameter:

```php
// Gemeinsame Instanz der Klasse
$shared = Flight::db();

// Neue Instanz der Klasse
$new = Flight::db(false);
```

Beachten Sie, dass zugeordnete Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide mit dem gleichen Namen deklarieren, wird nur die zugeordnete Methode aufgerufen.

## Protokollierung

Flight hat kein integriertes Protokollierungssystem, es ist jedoch wirklich einfach, eine Protokollbibliothek mit Flight zu verwenden. Hier ist ein Beispiel mit der Monolog-Bibliothek:

```php
// index.php oder bootstrap.php

// Registrieren Sie den Logger mit Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Jetzt, da es registriert ist, können Sie es in Ihrer Anwendung verwenden:

```php
// In Ihrem Controller oder Route
Flight::log()->warning('Dies ist eine Warnmeldung');
```

Dies protokolliert eine Nachricht in die von Ihnen angegebene Protokolldatei. Was, wenn Sie etwas protokollieren möchten, wenn ein Fehler auftritt? Sie können die `error` Methode verwenden:

```php
// In Ihrem Controller oder Route

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Anzeigen Ihrer benutzerdefinierten Fehlermeldung
	include 'errors/500.html';
});
```

Sie könnten auch ein einfaches APM (Application Performance Monitoring) System mit den `before` und `after` Methoden erstellen:

```php
// In Ihrer Bootstrap-Datei

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Anfrage '.Flight::request()->url.' dauerte ' . round($end - $start, 4) . ' Sekunden');

	// Sie könnten auch Ihre Anfrage- oder Antwort-Header hinzufügen
	// um sie ebenfalls zu protokollieren (sein Sie vorsichtig, da dies eine 
	// Menge Daten sein könnte, wenn Sie viele Anfragen haben)
	Flight::log()->info('Anfrage-Header: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Antwort-Header: ' . json_encode(Flight::response()->headers));
});
```

## Frameworkmethoden überschreiben

Flight ermöglicht es Ihnen, die Standardfunktionalität an Ihre eigenen Bedürfnisse anzupassen, ohne den Code ändern zu müssen. Sie können alle Methoden anzeigen, die Sie überschreiben können [hier](/learn/api).

Wenn Flight beispielsweise eine URL nicht mit einem Pfad abgleichen kann, ruft es die `notFound` Methode auf, die eine generische `HTTP 404` Antwort sendet. Sie können dieses Verhalten durch Verwendung der `map` Methode überschreiben:

```php
Flight::map('notFound', function() {
  // Benutzerdefinierte 404-Seite anzeigen
  include 'errors/404.html';
});
```

Flight erlaubt es Ihnen auch, Kernkomponenten des Frameworks zu ersetzen. Beispielsweise können Sie die Standard-Router-Klasse durch Ihre eigene benutzerdefinierte Klasse ersetzen:

```php
// Registrieren Sie Ihre benutzerdefinierte Klasse
Flight::register('router', MyRouter::class);

// Wenn Flight die Router-Instanz lädt, wird es Ihre Klasse laden
$myrouter = Flight::router();
```

Frameworkmethoden wie `map` und `register` können jedoch nicht überschrieben werden. Sie erhalten einen Fehler, wenn Sie versuchen, dies zu tun.