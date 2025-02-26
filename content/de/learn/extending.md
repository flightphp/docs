# Erweiterung

Flight ist so konzipiert, dass es ein erweiterbares Framework ist. Das Framework wird mit einer Reihe von Standardmethoden und -komponenten ausgeliefert, erlaubt es Ihnen jedoch, Ihre eigenen Methoden zuzuordnen, Ihre eigenen Klassen zu registrieren oder sogar vorhandene Klassen und Methoden zu überschreiben.

Wenn Sie nach einem DIC (Dependency Injection Container) suchen, springen Sie zur 
[Dependency Injection Container](dependency-injection-container) Seite.

## Methoden zuordnen

Um Ihre eigene einfache benutzerdefinierte Methode zuzuordnen, verwenden Sie die Funktion `map`:

```php
// Ordnen Sie Ihre Methode zu
Flight::map('hello', function (string $name) {
  echo "Hallo $name!";
});

// Rufen Sie Ihre benutzerdefinierte Methode auf
Flight::hello('Bob');
```

Obwohl es möglich ist, einfache benutzerdefinierte Methoden zu erstellen, wird empfohlen, einfach Standardfunktionen in PHP zu erstellen. Dies hat Autovervollständigung in IDEs und ist einfacher zu lesen.
Das Äquivalent des obigen Codes wäre:

```php
function hello(string $name) {
  echo "Hallo $name!";
}

hello('Bob');
```

Dies wird häufiger verwendet, wenn Sie Variablen in Ihre Methode übergeben müssen, um einen erwarteten Wert zu erhalten. Die Verwendung der `register()` Methode wie unten ist mehr für die Übergabe von Konfigurationen gedacht und dann für den Aufruf Ihrer vorkonfigurierten Klasse.

## Klassen registrieren

Um Ihre eigene Klasse zu registrieren und zu konfigurieren, verwenden Sie die Funktion `register`:

```php
// Registrieren Sie Ihre Klasse
Flight::register('user', User::class);

// Holen Sie sich eine Instanz Ihrer Klasse
$user = Flight::user();
```

Die `register` Methode ermöglicht es Ihnen auch, Parameter an den Klassenkonstruktor zu übergeben. Wenn Sie also Ihre benutzerdefinierte Klasse laden, wird sie vorinitialisiert. 
Sie können die Konstruktorparameter definieren, indem Sie ein zusätzliches Array übergeben.
Hier ist ein Beispiel für das Laden einer Datenbankverbindung:

```php
// Klasse mit Konstruktorparametern registrieren
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Holen Sie sich eine Instanz Ihrer Klasse
// Dies wird ein Objekt mit den definierten Parametern erstellen
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// und wenn Sie es später in Ihrem Code benötigen, rufen Sie einfach dieselbe Methode erneut auf
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Wenn Sie einen zusätzlichen Rückrufparameter übergeben, wird dieser sofort nach der Konstruktion der Klasse ausgeführt. Dies ermöglicht es Ihnen, alle Einrichtungsverfahren für Ihr neues Objekt durchzuführen. Die Rückruf-Funktion nimmt einen Parameter, eine Instanz des neuen Objekts.

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

Standardmäßig erhalten Sie jedes Mal, wenn Sie Ihre Klasse laden, eine gemeinsame Instanz.
Um eine neue Instanz einer Klasse zu erhalten, übergeben Sie einfach `false` als Parameter:

```php
// Gemeinsame Instanz der Klasse
$shared = Flight::db();

// Neue Instanz der Klasse
$new = Flight::db(false);
```

Beachten Sie, dass zugeordnete Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide mit demselben Namen deklarieren, wird nur die zugeordnete Methode aufgerufen.

## Protokollierung

Flight hat kein integriertes Protokollierungssystem, es ist jedoch sehr einfach, eine Protokollbibliothek mit Flight zu verwenden. Hier ist ein Beispiel mit der Monolog-Bibliothek:

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

Dies protokolliert eine Nachricht in die Protokolldatei, die Sie angegeben haben. Was ist, wenn Sie etwas protokollieren möchten, wenn ein Fehler auftritt? Sie können die Methode `error` verwenden:

```php
// In Ihrem Controller oder Route

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Zeigen Sie Ihre benutzerdefinierte Fehlerseite an
	include 'errors/500.html';
});
```

Sie könnten auch ein einfaches APM (Application Performance Monitoring) System 
mit den `before` und `after` Methoden erstellen:

```php
// In Ihrer Bootstrap-Datei

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Anfrage '.Flight::request()->url.' dauerte ' . round($end - $start, 4) . ' Sekunden');

	// Sie könnten auch Ihre Anforderungs- oder Antwortüberschriften hinzufügen
	// um sie ebenfalls zu protokollieren (seien Sie vorsichtig, da dies eine 
	// große Menge an Daten erzeugen würde, wenn Sie viele Anfragen haben)
	Flight::log()->info('Anforderungsüberschriften: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Antwortüberschriften: ' . json_encode(Flight::response()->headers));
});
```

## Framework-Methoden überschreiben

Flight ermöglicht es Ihnen, seine Standardfunktionalität anzupassen, um Ihren eigenen Bedürfnissen gerecht zu werden, ohne dass Sie Code ändern müssen. Sie können alle Methoden, die Sie überschreiben können, [hier](/learn/api) einsehen.

Wenn Flight beispielsweise eine URL nicht mit einer Route abgleichen kann, wird die Methode `notFound` aufgerufen, die eine generische `HTTP 404` Antwort sendet. Sie können dieses Verhalten durch die Verwendung der Methode `map` überschreiben:

```php
Flight::map('notFound', function() {
  // Benutzerdefinierte 404-Seite anzeigen
  include 'errors/404.html';
});
```

Flight ermöglicht es Ihnen auch, die Kernkomponenten des Frameworks zu ersetzen. 
Zum Beispiel können Sie die Standard-Router-Klasse durch Ihre eigene benutzerdefinierte Klasse ersetzen:

```php
// Registrieren Sie Ihre benutzerdefinierte Klasse
Flight::register('router', MyRouter::class);

// Wenn Flight die Router-Instanz lädt, wird es Ihre Klasse laden
$myrouter = Flight::router();
```

Frameworkmethoden wie `map` und `register` können jedoch nicht überschrieben werden. Sie erhalten einen Fehler, wenn Sie dies versuchen.