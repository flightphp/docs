# Ansichten

Flight bietet standardmäßig einige grundlegende Template-Funktionalitäten. Um ein Ansichts-Template anzuzeigen, rufen Sie die Methode `render` mit dem Namen der Template-Datei und optionalen Template-Daten auf:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Die übergebenen Template-Daten werden automatisch in das Template eingefügt und können wie eine lokale Variable referenziert werden. Template-Dateien sind einfach PHP-Dateien. Wenn der Inhalt der `hello.php` Template-Datei lautet:

```php
Hallo, <?= $name ?>!
```

Die Ausgabe wäre:

```
Hallo, Bob!
```

Sie können auch manuell Ansichtsvariablen festlegen, indem Sie die Methode `set` verwenden:

```php
Flight::view()->set('name', 'Bob');
```

Die Variable `name` ist nun in allen Ihren Ansichten verfügbar. Sie können also einfach Folgendes tun:

```php
Flight::render('hello');
```

Beachten Sie, dass beim Festlegen des Namens des Templates in der Render-Methode die Dateierweiterung `.php` ausgelassen werden kann.

Standardmäßig sucht Flight nach einem `views`-Verzeichnis für Template-Dateien. Sie können einen alternativen Pfad für Ihre Templates festlegen, indem Sie die folgende Konfiguration setzen:

```php
Flight::set('flight.views.path', '/pfad/zu/ansichten');
```

## Layouts

Es ist üblich, dass Websites eine einzelne Layout-Template-Datei mit sich änderndem Inhalt haben. Um Inhalte zu rendern, die in einem Layout verwendet werden sollen, können Sie einen optionalen Parameter an die `render`-Methode übergeben.

```php
Flight::render('header', ['heading' => 'Hallo'], 'headerContent');
Flight::render('body', ['body' => 'Welt'], 'bodyContent');
```

Ihre Ansicht wird dann gespeicherte Variablen namens `headerContent` und `bodyContent` haben. Sie können dann Ihr Layout rendern, indem Sie Folgendes tun:

```php
Flight::render('layout', ['title' => 'Startseite']);
```

Wenn die Template-Dateien wie folgt aussehen:

`header.php`:

```php
<h1><?= $heading ?></h1>
```

`body.php`:

```php
<div><?= $body ?></div>
```

`layout.php`:

```php
<html>
  <head>
    <title><?= $title ?></title>
  </head>
  <body>
    <?= $headerContent ?>
    <?= $bodyContent ?>
  </body>
</html>
```

Die Ausgabe wäre:
```html
<html>
  <head>
    <title>Startseite</title>
  </head>
  <body>
    <h1>Hallo</h1>
    <div>Welt</div>
  </body>
</html>
```

## Individuelle Ansichten

Flight ermöglicht es Ihnen, den Standard-View-Engine einfach durch Registrieren Ihrer eigenen View-Klasse auszutauschen. So verwenden Sie z. B. den [Smarty](http://www.smarty.net/) Template-Engine für Ihre Ansichten:

```php
// Smarty-Bibliothek laden
require './Smarty/libs/Smarty.class.php';

// Registrieren Sie Smarty als View-Klasse
// Geben Sie auch eine Rückruffunktion zum Konfigurieren von Smarty beim Laden an
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Template-Daten zuweisen
Flight::view()->assign('name', 'Bob');

// Template anzeigen
Flight::view()->display('hello.tpl');
```

Zu Ihrer Information sollten Sie auch die Standard-Rendermethode von Flight überschreiben:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```