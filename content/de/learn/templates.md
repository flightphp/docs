## Ansichten

Flight bietet standardmäßig einige grundlegende Vorlagenfunktionalitäten.

Wenn Sie komplexere Vorlagenanforderungen haben, sehen Sie sich die Smarty- und Latte-Beispiele im Abschnitt [Benutzerdefinierte Ansichten](#benutzerdefinierte-ansichten) an.

Um eine Ansichtsvorlage anzuzeigen, rufen Sie die `render`-Methode mit dem Namen der Vorlagendatei und optionalen Vorlagendaten auf:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Die von Ihnen übergebenen Vorlagendaten werden automatisch in die Vorlage eingefügt und können wie eine lokale Variable referenziert werden. Vorlagendateien sind einfach PHP-Dateien. Wenn der Inhalt der `hello.php`-Vorlagendatei folgendermaßen aussieht:

```php
Hallo, <?= $name ?>!
```

Die Ausgabe wäre:

```
Hallo, Bob!
```

Sie können auch manuell Ansichtsvariablen festlegen, indem Sie die `set`-Methode verwenden:

```php
Flight::view()->set('name', 'Bob');
```

Die Variable `name` ist jetzt in allen Ihren Ansichten verfügbar. Daher können Sie einfach Folgendes tun:

```php
Flight::render('hello');
```

Beachten Sie, dass beim Festlegen des Namens der Vorlage in der `render`-Methode die Dateierweiterung `.php` ausgelassen werden kann.

Standardmäßig sucht Flight nach einem `views`-Verzeichnis für Vorlagendateien. Sie können einen alternativen Pfad für Ihre Vorlagen festlegen, indem Sie die folgende Konfiguration setzen:

```php
Flight::set('flight.views.path', '/pfad/zu/vorlagen');
```

## Layouts

Es ist üblich, dass Websites eine einzelne Layoutvorlagendatei mit wechselndem Inhalt haben. Um Inhalt zum Rendern in einem Layout zu verwenden, können Sie einen optionalen Parameter an die `render`-Methode übergeben.

```php
Flight::render('header', ['heading' => 'Hallo'], 'headerContent');
Flight::render('body', ['body' => 'Welt'], 'bodyContent');
```

Ihre Ansicht wird dann gespeicherte Variablen mit den Namen `headerContent` und `bodyContent` haben. Sie können dann Ihr Layout rendern, indem Sie Folgendes tun:

```php
Flight::render('layout', ['title' => 'Startseite']);
```

Wenn die Vorlagendateien wie folgt aussehen:

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

## Benutzerdefinierte Ansichten

Flight ermöglicht es Ihnen, den Standard-Ansichtsmechanismus einfach durch Registrierung Ihrer eigenen Ansichtsklasse zu ersetzen.

### Smarty

So verwenden Sie den [Smarty](http://www.smarty.net/) Template-Engine für Ihre Ansichten:

```php
// Smarty-Bibliothek laden
require './Smarty/libs/Smarty.class.php';

// Registrieren Sie Smarty als Ansichtsklasse
// Geben Sie auch eine Rückruffunktion weiter, um Smarty beim Laden zu konfigurieren
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Template-Daten zuweisen
Flight::view()->assign('name', 'Bob');

// Die Vorlage anzeigen
Flight::view()->display('hello.tpl');
```

Zu Vollständigkeit sollten Sie auch die Standard-`render`-Methode von Flight überschreiben:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

So verwenden Sie die [Latte](https://latte.nette.org/) Template-Engine für Ihre Ansichten:

```php

// Registrieren Sie Latte als Ansichtsklasse
// Geben Sie auch eine Rückruffunktion weiter, um Latte beim Laden zu konfigurieren
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Hier speichert Latte Ihre Vorlagen zum Beschleunigen des Ladevorgangs
	// Eine nette Sache bei Latte ist, dass es automatisch Ihren Cache erneuert,
	// wenn Sie Änderungen an Ihren Vorlagen vornehmen!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Sagen Sie Latte, wo sich das Stammverzeichnis Ihrer Ansichten befinden wird.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Und verpacken Sie es, damit Sie Flight::render() korrekt verwenden können
Flight::map('render', function(string $template, array $data): void {
  // Das hier ist ähnlich wie $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```