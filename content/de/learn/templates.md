# HTML-Ansichten und Vorlagen

Flight bietet standardmäßig einige grundlegende Vorlagenfunktionalitäten.

Wenn Sie komplexere Vorlagenanforderungen haben, beachten Sie die Smarty- und Latte-Beispiele im Abschnitt [Benutzerdefinierte Ansichten](#custom-views).

## Standardansichtsmaschine

Um eine Ansichtsvorlage anzuzeigen, rufen Sie die Methode `render` mit dem Namen der Vorlagendatei und optionalen Vorlagendaten auf:

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

Sie können auch manuell Ansichtsvariablen festlegen, indem Sie die Methode `set` verwenden:

```php
Flight::view()->set('name', 'Bob');
```

Die Variable `name` steht nun in allen Ihren Ansichten zur Verfügung. Sie können also einfach Folgendes tun:

```php
Flight::render('hello');
```

Beachten Sie, dass Sie bei der Angabe des Namens der Vorlage in der `render`-Methode die `.php`-Erweiterung weglassen können.

Standardmäßig sucht Flight nach einem `views`-Verzeichnis für Vorlagendateien. Sie können einen alternativen Pfad für Ihre Vorlagen festlegen, indem Sie die folgende Konfiguration festlegen:

```php
Flight::set('flight.views.path', '/pfad/zur/vorlagen');
```

### Layouts

Es ist üblich, dass Websites eine einzelne Layoutvorlagendatei mit austauschbarem Inhalt haben. Um Inhalt zu rendern, der in einem Layout verwendet werden soll, können Sie einen optionalen Parameter an die `render`-Methode übergeben.

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

## Benutzerdefinierte Ansichtsmaschinen

Flight ermöglicht es Ihnen, die Standardansichtsmaschine einfach durch Registrierung Ihrer eigenen Ansichtsklasse auszutauschen.

### Smarty

So verwenden Sie den [Smarty](http://www.smarty.net/) Vorlagenmotor für Ihre Ansichten:

```php
// Smarty-Bibliothek laden
require './Smarty/libs/Smarty.class.php';

// Registrieren Sie Smarty als Ansichtsklasse
// Übergeben Sie auch eine Rückruffunktion, um Smarty beim Laden zu konfigurieren
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

Zu Vollständigkeit sollten Sie auch die Standard-`render`-Methode von Flight überschreiben:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

So verwenden Sie den [Latte](https://latte.nette.org/) Vorlagenmotor für Ihre Ansichten:

```php

// Registrieren Sie Latte als Ansichtsklasse
// Übergeben Sie auch eine Rückruffunktion, um Latte beim Laden zu konfigurieren
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Hier wird Latte Ihre Vorlagen zwischenspeichern, um die Dinge zu beschleunigen.
	// Ein schöner Aspekt an Latte ist, dass es Ihren Cache automatisch aktualisiert,
	// wenn Sie Änderungen an Ihren Vorlagen vornehmen!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Teilen Sie Latte mit, in welchem Stammverzeichnis sich Ihre Ansichten befinden werden.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Und verpacken Sie es, damit Sie Flight::render() korrekt verwenden können
Flight::map('render', function(string $template, array $data): void {
  // Dies entspricht $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```