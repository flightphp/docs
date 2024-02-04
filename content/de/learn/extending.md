# Erweiterung / Container

Flight ist so konzipiert, dass es ein erweiterbares Framework ist. Das Framework wird mit einer Reihe von Standardmethoden und -komponenten geliefert, aber es ermöglicht Ihnen, Ihre eigenen Methoden zu mappen, Ihre eigenen Klassen zu registrieren oder sogar vorhandene Klassen und Methoden zu überschreiben.

## Mapping von Methoden

Um Ihre eigene benutzerdefinierte Methode zu mappen, verwenden Sie die `map` Funktion:

```php
// Ihre Methode mappen
Flight::map('hallo', function (string $name) {
  echo "hallo $name!";
});

// Ihre benutzerdefinierte Methode aufrufen
Flight::hallo('Bob');
```

## Registrierung von Klassen / Containerisierung

Um Ihre eigene Klasse zu registrieren, verwenden Sie die `register` Funktion:

```php
// Ihre Klasse registrieren
Flight::register('benutzer', Benutzer::class);

// Eine Instanz Ihrer Klasse abrufen
$benutzer = Flight::benutzer();
```

Die `register` Methode ermöglicht es Ihnen auch, Parameter an den Konstruktor Ihrer Klasse zu übergeben. Wenn Sie Ihre benutzerdefinierte Klasse laden, wird sie vorkonfiguriert geliefert. Sie können die Konstruktorparameter festlegen, indem Sie ein zusätzliches Array übergeben. Hier ist ein Beispiel zum Laden einer Datenbankverbindung:

```php
// Klasse mit Konstruktorparametern registrieren
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'benutzer', 'passwort']);

// Eine Instanz Ihrer Klasse abrufen
//
// new PDO('mysql:host=localhost;dbname=test','benutzer','passwort');
//
$db = Flight::db();
```

Wenn Sie einen zusätzlichen Rückrufparameter übergeben, wird er sofort nach der Klassenkonstruktion ausgeführt. Dies ermöglicht es Ihnen, beliebige Einrichtungsverfahren für Ihr neues Objekt durchzuführen. Die Rückruffunktion nimmt einen Parameter entgegen, nämlich eine Instanz des neuen Objekts.

```php
// Der Rückruf erhält das konstruierte Objekt übergeben
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'benutzer', 'passwort'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Standardmäßig erhalten Sie bei jedem Laden Ihrer Klasse eine gemeinsam genutzte Instanz. Um eine neue Instanz einer Klasse zu erhalten, geben Sie einfach `false` als Parameter an:

```php
// Gemeinsam genutzte Instanz der Klasse
$gemeinsam = Flight::db();

// Neue Instanz der Klasse
$neu = Flight::db(false);
```

Bitte beachten Sie, dass gemappte Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide mit demselben Namen deklarieren, wird nur die gemappte Methode aufgerufen.