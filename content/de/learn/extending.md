# Erweitern

Flight ist so konzipiert, dass es sich um ein erweiterbares Framework handelt. Das Framework wird mit einer Reihe von Standardmethoden und -komponenten geliefert, jedoch können Sie Ihre eigenen Methoden zuordnen, Ihre eigenen Klassen registrieren oder sogar bestehende Klassen und Methoden überschreiben.

Wenn Sie auf der Suche nach einem DIC (Dependency Injection Container) sind, wechseln Sie zur [Dependency Injection Container](dependency-injection-container) Seite.

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

Obwohl es möglich ist, einfache benutzerdefinierte Methoden zu erstellen, wird empfohlen, einfach Standardfunktionen in PHP zu erstellen. Diese haben eine Autovervollständigung in IDEs und sind einfacher zu lesen.
Das Äquivalent des obigen Codes wäre:

```php
function hello(string $name) {
  echo "hallo $name!";
}

hello('Bob');
```

Dies wird eher verwendet, wenn Sie Variablen in Ihre Methode übergeben müssen, um einen erwarteten Wert zu erhalten. Die Verwendung der `register()` Methode wie unten gezeigt dient mehr dazu, Konfigurationsdaten zu übergeben und dann Ihre vorab konfigurierte Klasse aufzurufen.

## Klassen registrieren

Um Ihre eigene Klasse zu registrieren und zu konfigurieren, verwenden Sie die `register` Funktion:

```php
// Registrieren Sie Ihre Klasse
Flight::register('user', User::class);

// Erhalten Sie eine Instanz Ihrer Klasse
$user = Flight::user();
```

Die Registriermethode ermöglicht es Ihnen auch, Parameter an den Konstruktor Ihrer Klasse zu übergeben. Wenn Sie Ihre benutzerdefinierte Klasse laden, wird sie vorinitialisiert.
Sie können die Konstruktorparameter definieren, indem Sie ein zusätzliches Array übergeben.
Hier ist ein Beispiel zum Laden einer Datenbankverbindung:

```php
// Klasse mit Konstruktorparametern registrieren
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'benutzer', 'passwort']);

// Erhalten Sie eine Instanz Ihrer Klasse
// Dies erstellt ein Objekt mit den definierten Parametern
//
// new PDO('mysql:host=localhost;dbname=test','benutzer','passwort');
//
$db = Flight::db();

// und wenn Sie es später im Code benötigen, rufen Sie einfach die gleiche Methode erneut auf
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Wenn Sie einen zusätzlichen Rückrufparameter übergeben, wird er unmittelbar nach der Klassenkonstruktion ausgeführt. Dies ermöglicht es Ihnen, alle Einrichtungsvorgänge für Ihr neues Objekt durchzuführen. Die Rückruffunktion erhält einen Parameter, eine Instanz des neuen Objekts.

```php
// Der Rückruf erhält das konstruierte Objekt
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'benutzer', 'passwort'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Standardmäßig erhalten Sie jedes Mal, wenn Sie Ihre Klasse laden, eine gemeinsam genutzte Instanz.
Um eine neue Instanz einer Klasse zu erhalten, geben Sie einfach `false` als Parameter ein:

```php
// Gemeinsam genutzte Instanz der Klasse
$gemeinsam = Flight::db();

// Neue Instanz der Klasse
$neu = Flight::db(false);
```

Bitte beachten Sie, dass zugeordnete Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide unter demselben Namen deklarieren, wird nur die zugeordnete Methode aufgerufen.

## Überschreiben von Framework-Methoden

Flight ermöglicht es Ihnen, seine Standardfunktionalität anzupassen, um Ihren eigenen Anforderungen gerecht zu werden, ohne dass Sie Code ändern müssen. Sie können alle Methoden, die Sie überschreiben können, [hier](/learn/api) einsehen.

Wenn Flight beispielsweise einer URL keine Route zuordnen kann, ruft es die `notFound` Methode auf, die eine generische `HTTP 404`-Antwort sendet. Sie können dieses Verhalten überschreiben, indem Sie die `map` Methode verwenden:

```php
Flight::map('notFound', function() {
  // Benutzerdefinierte 404-Seite anzeigen
  include 'errors/404.html';
});
```

Flight ermöglicht es auch, Kernkomponenten des Frameworks zu ersetzen.
Sie können beispielsweise die Standard-Routerklasse durch Ihre eigene benutzerdefinierte Klasse ersetzen:

```php
// Registrieren Sie Ihre benutzerdefinierte Klasse
Flight::register('router', MeineRouter::class);

// Wenn Flight die Router-Instanz lädt, wird Ihre Klasse geladen
$meinerouter = Flight::router();
```

Framework-Methoden wie `map` und `register` können jedoch nicht überschrieben werden. Sie erhalten einen Fehler, wenn Sie dies versuchen.