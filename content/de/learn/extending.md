# Erweiterung / Container

Flight ist darauf ausgelegt, ein erweiterbares Framework zu sein. Das Framework wird mit einer Reihe von Standardmethoden und -komponenten geliefert, aber es ermöglicht Ihnen, Ihre eigenen Methoden zu kartieren, Ihre eigenen Klassen zu registrieren oder sogar vorhandene Klassen und Methoden zu überschreiben.

## Kartierung von Methoden

Um Ihre eigene einfache benutzerdefinierte Methode zu kartieren, verwenden Sie die `map` Funktion:

```php
// Kartieren Sie Ihre Methode
Flight::map('hello', function (string $name) {
  echo "Hallo $name!";
});

// Rufen Sie Ihre benutzerdefinierte Methode auf
Flight::hello('Bob');
```

Dies wird eher verwendet, wenn Sie Variablen in Ihre Methode übergeben müssen, um einen erwarteten Wert zu erhalten. Die Verwendung der `register()` Methode wie unten gezeigt ist eher dazu gedacht, Konfigurationsinformationen zu übergeben und dann Ihre vordefinierte Klasse aufzurufen.

## Registrierung von Klassen / Containerisierung

Um Ihre eigene Klasse zu registrieren und zu konfigurieren, verwenden Sie die `register` Funktion:

```php
// Registrieren Sie Ihre Klasse
Flight::register('user', Benutzer::class);

// Holen Sie sich eine Instanz Ihrer Klasse
$user = Flight::user();
```

Die Register-Methode ermöglicht es Ihnen auch, Parameter an den Konstruktor Ihrer Klasse zu übergeben. Wenn Sie Ihre benutzerdefinierte Klasse laden, wird sie also vorinitialisiert. Sie können die Konstruktorparameter definieren, indem Sie ein zusätzliches Array übergeben. Hier ist ein Beispiel für das Laden einer Datenbankverbindung:

```php
// Klasse mit Konstruktorparametern registrieren
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Holen Sie sich eine Instanz Ihrer Klasse
// Dies erstellt ein Objekt mit den definierten Parametern
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// und wenn Sie es später in Ihrem Code benötigen, rufen Sie einfach erneut dieselbe Methode auf
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Wenn Sie einen zusätzlichen Callback-Parameter übergeben, wird er unmittelbar nach der Klassenkonstruktion ausgeführt. Dadurch können Sie alle Einrichtungsprozeduren für Ihr neues Objekt durchführen. Die Callback-Funktion nimmt einen Parameter entgegen, eine Instanz des neuen Objekts.

```php
// Der Callback erhält das konstruierte Objekt
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

Beachten Sie, dass kartierte Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide mit demselben Namen deklarieren, wird nur die kartierte Methode aufgerufen.

## Überschreiben

Flight ermöglicht es Ihnen, seine Standardfunktionalitäten Ihren eigenen Bedürfnissen anzupassen, ohne dass Sie den Code ändern müssen.

Wenn zum Beispiel Flight keine URL mit einer Route übereinstimmen kann, ruft es die `notFound` Methode auf, die eine generische `HTTP 404`-Antwort sendet. Sie können dieses Verhalten überschreiben, indem Sie die `map` Methode verwenden:

```php
Flight::map('notFound', function() {
  // Zeige benutzerdefinierte 404 Seite an
  include 'errors/404.html';
});
```

Flight ermöglicht es auch, Kernkomponenten des Frameworks zu ersetzen.
Sie können beispielsweise die Standard-Routerklasse durch Ihre eigene benutzerdefinierte Klasse ersetzen:

```php
// Registrieren Sie Ihre benutzerdefinierte Klasse
Flight::register('router', MyRouter::class);

// Wenn Flight die Router-Instanz lädt, wird Ihre Klasse geladen
$myrouter = Flight::router();
```

Frameworkmethoden wie `map` und `register` können jedoch nicht überschrieben werden. Sie erhalten einen Fehler, wenn Sie versuchen, dies zu tun.