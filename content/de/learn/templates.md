# Ansichten

Flight bietet standardmäßig einige grundlegende Template-Funktionalitäten.

Wenn Sie komplexere Template-Anforderungen haben, finden Sie Beispiele für Smarty und Latte im Abschnitt [Benutzerdefinierte Ansichten](#benutzerdefinierte-ansichten).

Um eine Ansichtsvorlage anzuzeigen, rufen Sie die `render` Methode mit dem Namen der Vorlagendatei und optionalen Vorlagedaten auf:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Die von Ihnen übergebenen Vorlagendaten werden automatisch in die Vorlage eingefügt und können wie eine lokale Variable referenziert werden. Vorlagendateien sind einfach PHP-Dateien. Wenn der Inhalt der `hello.php` Vorlagendatei folgendermaßen aussieht:

```php
Hallo, <?= $name ?>!
```

Das Ergebnis wäre:

```
Hallo, Bob!
```

Sie können auch manuell Ansichtsvariablen festlegen, indem Sie die `set` Methode verwenden:

```php
Flight::view()->set('name', 'Bob');
```

Die Variable `name` steht nun in allen Ihren Ansichten zur Verfügung. Sie können also einfach Folgendes tun:

```php
Flight::render('hello');
```

Beachten Sie, dass Sie bei der Angabe des Namens der Vorlage in der `render` Methode die Dateierweiterung `.php` weglassen können.

Standardmäßig sucht Flight nach einem `views`-Verzeichnis für Vorlagendateien. Sie können einen alternativen Pfad für Ihre Vorlagen festlegen, indem Sie die folgende Konfiguration setzen:

```php
Flight::set('flight.views.path', '/pfad/zur/vorlagen');
```

## Layouts

Es ist üblich, dass Websites eine einzelne Layout-Vorlagendatei mit wechselndem Inhalt haben. Um Inhalte zu rendern, die in einem Layout verwendet werden sollen, können Sie einen optionalen Parameter an die `render` Methode übergeben.

```php
Flight::render('header', ['heading' => 'Hallo'], 'headerContent');
Flight::render('body', ['body' => 'Welt'], 'bodyContent');
```

Ihre Ansicht wird dann gespeicherte Variablen namens `headerContent` und `bodyContent` haben. Sie können dann Ihr Layout rendern, indem Sie Folgendes tun:

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

Das Ergebnis wäre:

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

Flight ermöglicht es Ihnen, den Standard-View-Engine einfach zu ersetzen, indem Sie Ihre eigene View-Klasse registrieren.

### Smarty

So verwenden Sie den [Smarty](http://www.smarty.net/) Template-Motor für Ihre Ansichten:

```php
// Smarty-Bibliothek laden
require './Smarty/libs/Smarty.class.php';

// Smarty als View-Klasse registrieren
// Geben Sie auch eine Rückruffunktion zur Konfiguration von Smarty beim Laden an
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Vorlagendaten zuweisen
Flight::view()->assign('name', 'Bob');

// Vorlage anzeigen
Flight::view()->display('hello.tpl');
```

Für Vollständigkeit sollten Sie auch die Standard-`render` Methode von Flight überschreiben:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

So verwenden Sie den [Latte](https://latte.nette.org/) Template-Motor für Ihre Ansichten:

```php

// Latte als View-Klasse registrieren
// Geben Sie auch eine Rückruffunktion zur Konfiguration von Latte beim Laden an
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Hier werden die Latte Ihre Vorlagen zwischenspeichern, um die Geschwindigkeit zu erhöhen
	// Eine nette Eigenschaft von Latte ist, dass es automatisch Ihren Zwischenspeicher aktualisiert
	// wenn Sie Änderungen an Ihren Vorlagen vornehmen!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Sagen Sie Latte, wo das Stammverzeichnis für Ihre Ansichten sein wird.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Und verpacken Sie es, damit Sie Flight::render() korrekt verwenden können
Flight::map('render', function(string $template, array $data): void {
  // Dies entspricht $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```  