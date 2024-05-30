# Erweiterung

Flight ist darauf ausgelegt, ein erweiterbares Framework zu sein. Das Framework wird mit einer Reihe von Standardmethoden und -komponenten geliefert, aber es ermöglicht Ihnen, Ihre eigenen Methoden zu mappen, Ihre eigenen Klassen zu registrieren oder sogar vorhandene Klassen und Methoden zu überschreiben.

Wenn Sie nach einem DIC (Dependency Injection Container) suchen, gehen Sie zur [Seite des Dependency Injection Containers](dependency-injection-container).

## Methoden Zuordnen

Um Ihre eigene einfache benutzerdefinierte Methode zuzuordnen, verwenden Sie die `map` Funktion:

```php
// Ordne deine Methode zu
Flight::map('hello', function (string $name) {
  echo "Hallo $name!";
});

// Rufen Sie Ihre benutzerdefinierte Methode auf
Flight::hello('Bob');
```

Dies wird häufiger verwendet, wenn Sie Variablen in Ihre Methode passen müssen, um einen erwarteten Wert zu erhalten. Die Verwendung der `register()` Methode wie unten ist eher für das Übergeben von Konfigurationen und das Aufrufen Ihrer vorab konfigurierten Klasse.

## Klassen registrieren

Um Ihre eigene Klasse zu registrieren und zu konfigurieren, verwenden Sie die `register` Funktion:

```php
// Registriere deine Klasse
Flight::register('user', User::class);

// Erhalte eine Instanz deiner Klasse
$user = Flight::user();
```

Die Registriermethode ermöglicht es Ihnen auch, Parameter an den Konstruktor Ihrer Klasse zu übergeben. Wenn Sie Ihre benutzerdefinierte Klasse laden, wird sie also vorinitialisiert. Sie können die Konstruktorparameter festlegen, indem Sie ein zusätzliches Array übergeben. Hier ist ein Beispiel zum Laden einer Datenbankverbindung:

```php
// Klasse mit Konstruktorparametern registrieren
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Erhalte eine Instanz deiner Klasse
// Dies wird ein Objekt mit den definierten Parametern erstellen
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// und wenn Sie es später in Ihrem Code benötigen, rufen Sie einfach die gleiche Methode erneut auf
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Wenn Sie einen zusätzlichen Rückrufparameter übergeben, wird dieser sofort nach dem Klassenkonstruktor ausgeführt. Dies ermöglicht es Ihnen, alle Einrichtungsverfahren für Ihr neues Objekt durchzuführen. Die Rückruffunktion nimmt einen Parameter entgegen, eine Instanz des neuen Objekts.

```php
// Der Rückruf erhält das konstruierte Objekt
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Standardmäßig erhalten Sie jedes Mal, wenn Sie Ihre Klasse laden, eine gemeinsam genutzte Instanz. Um eine neue Instanz einer Klasse zu erhalten, geben Sie einfach `false` als Parameter ein:

```php
// Gemeinsam genutzte Instanz der Klasse
$shared = Flight::db();

// Neue Instanz der Klasse
$new = Flight::db(false);
```

Beachten Sie, dass gemappte Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide mit demselben Namen deklarieren, wird nur die gemappte Methode aufgerufen.

## Framework-Methoden überschreiben

Flight ermöglicht es Ihnen, seine Standardfunktionalität anzupassen, um Ihren eigenen Anforderungen gerecht zu werden, ohne dass Sie dabei Code ändern müssen.

Wenn Flight beispielsweise keine URL mit einer Route übereinstimmen kann, ruft es die `notFound` Methode auf, die eine generische `HTTP 404` Antwort sendet. Sie können dieses Verhalten überschreiben, indem Sie die `map` Methode verwenden:

```php
Flight::map('notFound', function() {
  // Zeige benutzerdefinierte 404 Seite an
  include 'errors/404.html';
});
```

Flight ermöglicht es auch, Kernkomponenten des Frameworks zu ersetzen. Zum Beispiel können Sie die Standard-Routerklasse durch Ihre eigene benutzerdefinierte Klasse ersetzen:

```php
// Registriere deine benutzerdefinierte Klasse
Flight::register('router', MyRouter::class);

// Wenn Flight die Router-Instanz lädt, lädt es Ihre Klasse
$myrouter = Flight::router();
```

Framework-Methoden wie `map` und `register` können jedoch nicht überschrieben werden. Sie erhalten einen Fehler, wenn Sie dies versuchen.