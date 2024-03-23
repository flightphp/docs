### Erweiterung

Flight ist darauf ausgelegt, ein erweiterbares Framework zu sein. Das Framework wird mit einem Satz von Standardmethoden und -komponenten geliefert, aber es ermöglicht Ihnen, Ihre eigenen Methoden zu mappen, Ihre eigenen Klassen zu registrieren oder sogar bestehende Klassen und Methoden zu überschreiben.

Wenn Sie nach einem DIC (Dependency Injection Container) suchen, gehen Sie zur [Dependency Injection Container](dependency-injection-container)-Seite.

## Mappen von Methoden

Um Ihre eigene einfache benutzerdefinierte Methode zu mappen, verwenden Sie die `map` Funktion:

```php
// Mappen Sie Ihre Methode
Flight::map('hello', function (string $name) {
  echo "hallo $name!";
});

// Rufen Sie Ihre benutzerdefinierte Methode auf
Flight::hello('Bob');
```

Dies wird mehr verwendet, wenn Sie Variablen in Ihre Methode übergeben müssen, um einen erwarteten Wert zu erhalten. Die Verwendung der `register()` Methode wie unten gezeigt ist mehr dazu da, Konfigurationen zu übergeben und dann Ihre vorab konfigurierte Klasse aufzurufen.

## Klassen registrieren

Um Ihre eigene Klasse zu registrieren und zu konfigurieren, verwenden Sie die `register` Funktion:

```php
// Registrieren Sie Ihre Klasse
Flight::register('user', User::class);

// Holen Sie sich eine Instanz Ihrer Klasse
$user = Flight::user();
```

Die Registrierungsmethode ermöglicht es Ihnen auch, Parameter an den Konstruktor Ihrer Klasse zu übergeben. Wenn Sie Ihre benutzerdefinierte Klasse laden, wird sie somit vorinitialisiert. Sie können die Konstruktorparameter definieren, indem Sie ein zusätzliches Array übergeben. Hier ist ein Beispiel zum Laden einer Datenbankverbindung:

```php
// Klasse mit Konstruktorparametern registrieren
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'benutzer', 'passwort']);

// Holen Sie sich eine Instanz Ihrer Klasse
// Dies erstellt ein Objekt mit den definierten Parametern
//
// new PDO('mysql:host=localhost;dbname=test','benutzer','passwort');
//
$db = Flight::db();

// und wenn Sie es später in Ihrem Code benötigen, rufen Sie einfach die gleiche Methode erneut auf
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Wenn Sie einen zusätzlichen Rückrufparameter übergeben, wird dieser sofort nach der Klassenkonstruktion ausgeführt. Dies ermöglicht es Ihnen, jegliche Einrichtungsverfahren für Ihr neues Objekt durchzuführen. Die Rückruffunktion benötigt ein Parameter, eine Instanz des neuen Objekts.

```php
// Der Rückruf wird das konstruierte Objekt erhalten
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'benutzer', 'passwort'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Standardmäßig erhalten Sie bei jeder Verwendung Ihrer Klasse eine gemeinsam genutzte Instanz. Um eine neue Instanz einer Klasse zu erhalten, geben Sie einfach `false` als Parameter ein:

```php
// Gemeinsam genutzte Instanz der Klasse
$shared = Flight::db();

// Neue Instanz der Klasse
$neu = Flight::db(false);
```

Bitte beachten Sie, dass gemappte Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide mit demselben Namen deklarieren, wird nur die gemappte Methode aufgerufen.

## Überschreiben von Framework-Methoden

Flight ermöglicht es Ihnen, seine Standardfunktionalität anzupassen, um Ihren eigenen Anforderungen gerecht zu werden, ohne dass Sie Code ändern müssen.

Wenn beispielsweise Flight keine URL mit einer Route abgleichen kann, ruft es die `notFound`-Methode auf, die eine generische `HTTP 404`-Antwort sendet. Sie können dieses Verhalten überschreiben, indem Sie die `map` Methode verwenden:

```php
Flight::map('notFound', function() {
  // Benutzerdefinierte 404-Seite anzeigen
  include 'fehler/404.html';
});
```

Flight ermöglicht es auch, Kernkomponenten des Frameworks zu ersetzen. Zum Beispiel können Sie die Standard-Routenklasse durch Ihre eigene benutzerdefinierte Klasse ersetzen:

```php
// Registrieren Sie Ihre benutzerdefinierte Klasse
Flight::register('router', MyRouter::class);

// Wenn Flight die Router-Instanz lädt, wird Ihre Klasse geladen
$meinrouter = Flight::router();
```

Framework-Methoden wie `map` und `register` können jedoch nicht überschrieben werden. Sie erhalten einen Fehler, wenn Sie es dennoch versuchen.