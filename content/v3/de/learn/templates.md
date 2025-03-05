# HTML-Ansichten und Vorlagen

Flight bietet standardmäßig einige grundlegende Template-Funktionalitäten an.

Flight ermöglicht es Ihnen, die standardmäßige Ansichtsengine einfach auszutauschen, indem Sie Ihre eigene Ansichts-Klasse registrieren. Scrollen Sie nach unten, um Beispiele für die Verwendung von Smarty, Latte, Blade und mehr zu sehen!

## Eingebaute Ansichtsengine

Um eine Ansichts-Vorlage anzuzeigen, rufen Sie die Methode `render` mit dem Namen der Vorlagendatei und optionalen Vorlagendaten auf:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Die Vorlagendaten, die Sie übergeben, werden automatisch in die Vorlage injiziert und können wie eine lokale Variable referenziert werden. Vorlagendateien sind einfach PHP-Dateien. Wenn der Inhalt der Datei `hello.php` ist:

```php
Hello, <?= $name ?>!
```

Wäre die Ausgabe:

```text
Hello, Bob!
```

Sie können auch manuell Ansichtsvariablen festlegen, indem Sie die Methode set verwenden:

```php
Flight::view()->set('name', 'Bob');
```

Die Variable `name` ist nun in all Ihren Ansichten verfügbar. Sie können also einfach Folgendes tun:

```php
Flight::render('hello');
```

Beachten Sie, dass Sie beim Festlegen des Namens der Vorlage in der Methode render die Endung `.php` weglassen können.

Standardmäßig sucht Flight im Verzeichnis `views` nach Vorlagendateien. Sie können einen alternativen Pfad für Ihre Vorlagen festlegen, indem Sie die folgende Konfiguration setzen:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Layouts

Es ist üblich, dass Websites eine einzelne Layout-Vorlagendatei mit wechselndem Inhalt haben. Um Inhalte zu rendern, die in einem Layout verwendet werden sollen, können Sie einen optionalen Parameter an die Methode `render` übergeben.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Ihre Ansicht hat dann die gespeicherten Variablen `headerContent` und `bodyContent`. Sie können dann Ihr Layout wie folgt rendern:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Wenn die Vorlagendateien so aussehen:

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

Wäre die Ausgabe:
```html
<html>
  <head>
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

## Smarty

So würden Sie die [Smarty](http://www.smarty.net/) Template-Engine für Ihre Ansichten verwenden:

```php
// Smarty-Bibliothek laden
require './Smarty/libs/Smarty.class.php';

// Smarty als Ansichts-Klasse registrieren
// Außerdem eine Callback-Funktion übergeben, um Smarty beim Laden zu konfigurieren
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Vorlagendaten zuweisen
Flight::view()->assign('name', 'Bob');

// Die Vorlage anzeigen
Flight::view()->display('hello.tpl');
```

Zur Vollständigkeit sollten Sie auch die standardmäßige Render-Methode von Flight überschreiben:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

So würden Sie die [Latte](https://latte.nette.org/) Template-Engine für Ihre Ansichten verwenden:

```php
// Latte als Ansichts-Klasse registrieren
// Außerdem eine Callback-Funktion übergeben, um Latte beim Laden zu konfigurieren
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Hier wird Latte Ihre Vorlagen cachen, um die Dinge zu beschleunigen
	// Eine nette Sache an Latte ist, dass es Ihren Cache automatisch aktualisiert,
	// wenn Sie Änderungen an Ihren Vorlagen vornehmen!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Teilen Sie Latte mit, wo das Stammverzeichnis für Ihre Ansichten liegt.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Und schließen Sie es ab, damit Sie Flight::render() korrekt verwenden können
Flight::map('render', function(string $template, array $data): void {
  // Das ist wie $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

So würden Sie die [Blade](https://laravel.com/docs/8.x/blade) Template-Engine für Ihre Ansichten verwenden:

Zuerst müssen Sie die BladeOne-Bibliothek über Composer installieren:

```bash
composer require eftec/bladeone
```

Dann können Sie BladeOne als Ansichts-Klasse in Flight konfigurieren:

```php
<?php
// BladeOne-Bibliothek laden
use eftec\bladeone\BladeOne;

// BladeOne als Ansichts-Klasse registrieren
// Außerdem eine Callback-Funktion übergeben, um BladeOne beim Laden zu konfigurieren
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Vorlagendaten zuweisen
Flight::view()->share('name', 'Bob');

// Die Vorlage anzeigen
echo Flight::view()->run('hello', []);
```

Zur Vollständigkeit sollten Sie auch die standardmäßige Render-Methode von Flight überschreiben:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

In diesem Beispiel könnte die hello.blade.php-Vorlagendatei so aussehen:

```php
<?php
Hello, {{ $name }}!
```

Die Ausgabe wäre:

```
Hello, Bob!
```

Indem Sie diese Schritte befolgen, können Sie die Blade-Template-Engine mit Flight integrieren und sie zum Rendern Ihrer Ansichten verwenden.