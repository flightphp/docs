# Erweiterung / Container

Flight wurde entwickelt, um ein erweiterbares Framework zu sein. Das Framework wird mit einem Satz von Standardmethoden und -komponenten geliefert, aber es ermöglicht Ihnen, Ihre eigenen Methoden zu zuordnen, Ihre eigenen Klassen zu registrieren oder sogar vorhandene Klassen und Methoden zu überschreiben.

## Zuordnen von Methoden

Um Ihre eigene einfache benutzerdefinierte Methode zuzuordnen, verwenden Sie die `map` Funktion:

```php
// Ihre Methode zuordnen
Flight::map('hallo', function (string $name) {
  echo "hallo $name!";
});

// Rufen Sie Ihre benutzerdefinierte Methode auf
Flight::hallo('Bob');
```

Dies wird häufiger verwendet, wenn Sie Variablen an Ihre Methode übergeben müssen, um einen erwarteten Wert zu erhalten. Das Verwenden der Methode `register()` wie unten ist eher für das Übergeben von Konfigurationen und das Aufrufen Ihrer vordefinierten Klasse gedacht.

## Klassenregistrierung / Containerisierung

Um Ihre eigene Klasse zu registrieren und zu konfigurieren, verwenden Sie die `register` Funktion:

```php
// Ihre Klasse registrieren
Flight::register('benutzer', User::class);

// Eine Instanz Ihrer Klasse erhalten
$user = Flight::benutzer();
```

Die Registriermethode ermöglicht es Ihnen auch, Parameter an den Konstruktor Ihrer Klasse zu übergeben. Wenn Sie Ihre benutzerdefinierte Klasse laden, wird sie bereits vorinitialisiert. Sie können die Konstruktorparameter definieren, indem Sie ein zusätzliches Array übergeben. Hier ist ein Beispiel zum Laden einer Datenbankverbindung:

```php
// Klasse mit Konstruktorparametern registrieren
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'benutzer', 'passwort']);

// Eine Instanz Ihrer Klasse erhalten
// Dies wird ein Objekt mit den definierten Parametern erstellen
//
// new PDO('mysql:host=localhost;dbname=test','benutzer','passwort');
//
$db = Flight::db();

// und wenn Sie es später in Ihrem Code benötigen, rufen Sie einfach erneut dieselbe Methode auf
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Wenn Sie einen zusätzlichen Rückrufparameter übergeben, wird er sofort nach der Klassenkonstruktion ausgeführt. Dies ermöglicht es Ihnen, beliebige Einrichtungsvorgänge für Ihr neues Objekt durchzuführen. Die Rückruffunktion akzeptiert einen Parameter, eine Instanz des neuen Objekts.

```php
// Der Rückruf erhält das erstellte Objekt
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'benutzer', 'passwort'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Standardmäßig erhalten Sie jedes Mal, wenn Sie Ihre Klasse laden, eine gemeinsame Instanz. Um eine neue Instanz einer Klasse zu erhalten, geben Sie einfach `false` als Parameter an:

```php
// Gemeinsame Instanz der Klasse
$geteilt = Flight::db();

// Neue Instanz der Klasse
$neu = Flight::db(false);
```

Beachten Sie, dass zugeordnete Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide mit dem gleichen Namen deklarieren, wird nur die zugeordnete Methode aufgerufen.

## Überschreiben

Flight ermöglicht es Ihnen, seine Standardfunktionalität anzupassen, um Ihren eigenen Anforderungen gerecht zu werden, ohne irgendwelchen Code ändern zu müssen.

Wenn beispielsweise Flight keine URL mit einer Route abgleichen kann, ruft es die Methode `notFound` auf, die eine allgemeine `HTTP 404`-Antwort sendet. Sie können dieses Verhalten überschreiben, indem Sie die `map` Methode verwenden:

```php
Flight::map('notFound', function() {
  // Benutzerdefinierte 404-Seite anzeigen
  include 'fehler/404.html';
});
```

Flight ermöglicht es auch, Kernkomponenten des Frameworks zu ersetzen. Beispielsweise können Sie die Standardrouterklasse durch Ihre eigene benutzerdefinierte Klasse ersetzen:

```php
// Ihre benutzerdefinierte Klasse registrieren
Flight::register('router', MyRouter::class);

// Wenn Flight die Routerinstanz lädt, wird Ihre Klasse geladen
$meinrouter = Flight::router();
```

Frameworkmethoden wie `map` und `register` können jedoch nicht überschrieben werden. Wenn Sie versuchen, dies zu tun, erhalten Sie einen Fehler.