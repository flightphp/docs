# Ansichten

Flug bietet standardmäßig einige grundlegende Template-Funktionalitäten. Um ein Ansichtsvorlagen aufzurufen, rufen Sie die `render` Methode mit dem Namen der Template-Datei und optionalen Template-Daten auf:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Die übergebenen Template-Daten werden automatisch in das Template injiziert und können wie eine lokale Variable referenziert werden. Template-Dateien sind einfach PHP-Dateien. Wenn der Inhalt der `hello.php` Template-Datei folgender ist:

```php
Hallo, <?= $name ?>!
```

Der Output wäre:

```
Hallo, Bob!
```

Sie können auch manuell Ansichtsvariablen setzen, indem Sie die `set` Methode verwenden:

```php
Flight::view()->set('name', 'Bob');
```

Die Variable `name` ist jetzt in allen Ansichten verfügbar. So können Sie einfach tun:

```php
Flight::render('hello');
```

Beachten Sie, dass Sie beim Festlegen des Namens der Vorlage in der render Methode die Dateierweiterung `.php` weglassen können.

Standardmäßig sucht Flight nach einem `views`-Verzeichnis für Vorlagendateien. Sie können einen alternativen Pfad für Ihre Vorlagen festlegen, indem Sie die folgende Konfiguration setzen:

```php
Flight::set('flight.views.path', '/pfad/zum/views');
```

## Layouts

Es ist üblich, dass Websites eine einzige Layout-Vorlagendatei mit wechselndem Inhalt haben. Um Inhalte zu rendern, die in einem Layout verwendet werden sollen, können Sie einen optionalen Parameter an die `render` Methode übergeben.

```php
Flight::render('header', ['heading' => 'Hallo'], 'headerContent');
Flight::render('body', ['body' => 'Welt'], 'bodyContent');
```

Ihre Ansicht wird dann gespeicherte Variablen namens `headerContent` und `bodyContent` haben. Sie können dann Ihr Layout rendern, indem Sie Folgendes tun:

```php
Flight::render('layout', ['title' => 'Startseite']);
```

Wenn die Vorlagendateien folgendermaßen aussehen:

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

Der Output wäre:
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

## Benutzerdefinierte Ansichten

Flight ermöglicht es Ihnen, den Standard-View-Engine einfach durch Registrierung Ihrer eigenen View-Klasse zu ersetzen. So verwenden Sie den [Smarty](http://www.smarty.net/) Template-Engine für Ihre Ansichten:

```php
// Lade Smarty-Bibliothek
require './Smarty/libs/Smarty.class.php';

// Registriere Smarty als die View-Klasse
// Übergebe auch eine Callback-Funktion, um Smarty beim Laden zu konfigurieren
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Weise Template-Daten zu
Flight::view()->assign('name', 'Bob');

// Zeige das Template an
Flight::view()->display('hello.tpl');
```

Aus Gründen der Vollständigkeit sollten Sie auch die Standard-`render` Methode von Flight überschreiben:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```