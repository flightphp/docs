# Erweiterung / Container

Flight ist darauf ausgelegt, ein erweiterbares Framework zu sein. Das Framework wird mit einer Reihe von Standardmethoden und -komponenten geliefert, ermöglicht es Ihnen jedoch, Ihre eigenen Methoden zu mappen, Ihre eigenen Klassen zu registrieren oder sogar vorhandene Klassen und Methoden zu überschreiben.

## Methoden Zuordnen

Um Ihre eigene benutzerdefinierte Methode zuzuordnen, verwenden Sie die `map`-Funktion:

```php
// Ordne deine Methode zu
Flight::map('hello', function (string $name) {
  echo "hallo $name!";
});

// Rufe deine benutzerdefinierte Methode auf
Flight::hello('Bob');
```

## Klassen registrieren / Containerisierung

Um Ihre eigene Klasse zu registrieren, verwenden Sie die `register`-Funktion:

```php
// Registriere deine Klasse
Flight::register('user', User::class);

// Erhalte eine Instanz deiner Klasse
$user = Flight::user();
```

Die `register`-Methode ermöglicht es auch, Parameter an den Konstruktor Ihrer Klasse zu übergeben. Wenn Sie Ihre benutzerdefinierte Klasse laden, wird sie also vorinitialisiert. Sie können die Konstruktorparameter definieren, indem Sie ein zusätzliches Array übergeben. Hier ist ein Beispiel für das Laden einer Datenbankverbindung:

```php
// Klasse mit Konstruktorparametern registrieren
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Erhalte eine Instanz deiner Klasse
// Dies erstellt ein Objekt mit den definierten Parametern
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Wenn Sie einen zusätzlichen Rückrufparameter übergeben, wird dieser unmittelbar nach der Klassenkonstruktion ausgeführt. Dadurch können Sie für Ihr neues Objekt Einrichtungsverfahren durchführen. Die Rückruffunktion erhält einen Parameter, nämlich eine Instanz des neuen Objekts.

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

Standardmäßig erhalten Sie jedes Mal, wenn Sie Ihre Klasse laden, eine gemeinsam genutzte Instanz. Um eine neue Instanz einer Klasse zu erhalten, geben Sie einfach `false` als Parameter an:

```php
// Gemeinsam genutzte Instanz der Klasse
$shared = Flight::db();

// Neue Instanz der Klasse
$new = Flight::db(false);
```

Beachten Sie, dass gemappte Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide mit demselben Namen deklarieren, wird nur die zugeordnete Methode aufgerufen.