# HTML-Ansichten und Vorlagen

Flight bietet standardmäßig einige grundlegende Vorlagenfunktionen.

Flight ermöglicht es Ihnen, die standardmäßige View-Engine einfach zu ersetzen, indem Sie Ihre eigene View-Klasse registrieren. Scrollen Sie nach unten, um Beispiele für die Verwendung von Smarty, Latte, Blade und mehr zu sehen!

## Eingebaute View-Engine

Um eine View-Vorlage anzuzeigen, rufen Sie die Methode `render` mit dem Namen der Vorlagendatei und optionalen Template-Daten auf:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Die Template-Daten, die Sie übergeben, werden automatisch in die Vorlage eingefügt und können wie eine lokale Variable referenziert werden. Vorlagendateien sind einfach PHP-Dateien. Wenn der Inhalt der `hello.php`-Vorlagendatei wie folgt aussieht:

```php
Hallo, <?= $name ?>!
```

Wäre die Ausgabe:

```
Hallo, Bob!
```

Sie können auch manuell View-Variablen setzen, indem Sie die Set-Methode verwenden:

```php
Flight::view()->set('name', 'Bob');
```

Die Variable `name` ist jetzt in all Ihren Views verfügbar. Sie können also einfach Folgendes tun:

```php
Flight::render('hello');
```

Beachten Sie, dass Sie beim Angeben des Namens der Vorlage in der Render-Methode die `.php`-Erweiterung weglassen können.

Standardmäßig sucht Flight nach einem Verzeichnis `views` für Vorlagendateien. Sie können einen anderen Pfad für Ihre Vorlagen festlegen, indem Sie die folgende Konfiguration setzen:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Layouts

Es ist üblich, dass Websites eine einzelne Layout-Vorlagendatei mit wechselnden Inhalten haben. Um Inhalte zu rendern, die in einem Layout verwendet werden, können Sie einen optionalen Parameter an die Methode `render` übergeben.

```php
Flight::render('header', ['heading' => 'Hallo'], 'headerContent');
Flight::render('body', ['body' => 'Welt'], 'bodyContent');
```

Ihre View hat dann gespeicherte Variablen namens `headerContent` und `bodyContent`. Sie können dann Ihr Layout rendern, indem Sie Folgendes tun:

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

Wäre die Ausgabe:
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

## Smarty

So verwenden Sie die [Smarty](http://www.smarty.net/) Vorlagenengine für Ihre Views:

```php
// Smarty-Bibliothek laden
require './Smarty/libs/Smarty.class.php';

// Smarty als View-Klasse registrieren
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

Um vollständig zu sein, sollten Sie auch die standardmäßige Render-Methode von Flight überschreiben:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

So verwenden Sie die [Latte](https://latte.nette.org/) Vorlagenengine für Ihre Views:

```php

// Latte als View-Klasse registrieren
// Außerdem eine Callback-Funktion übergeben, um Latte beim Laden zu konfigurieren
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Hier cached Latte Ihre Vorlagen, um die Dinge zu beschleunigen
	// Eine nette Sache an Latte ist, dass es Ihren Cache automatisch aktualisiert, 
	// wenn Sie Änderungen an Ihren Vorlagen vornehmen!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Teilen Sie Latte mit, wo sich das Root-Verzeichnis Ihrer Views befinden wird.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Und wickeln Sie es ab, damit Sie Flight::render() korrekt verwenden können
Flight::map('render', function(string $template, array $data): void {
  // Das ist wie $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

So verwenden Sie die [Blade](https://laravel.com/docs/8.x/blade) Vorlagenengine für Ihre Views:

Zuerst müssen Sie die BladeOne-Bibliothek über Composer installieren:

```bash
composer require eftec/bladeone
```

Dann können Sie BladeOne als View-Klasse in Flight konfigurieren:

```php
<?php
// BladeOne-Bibliothek laden
use eftec\bladeone\BladeOne;

// BladeOne als View-Klasse registrieren
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

Um vollständig zu sein, sollten Sie auch die standardmäßige Render-Methode von Flight überschreiben:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

In diesem Beispiel könnte die hello.blade.php-Vorlagendatei wie folgt aussehen:

```php
<?php
Hallo, {{ $name }}!
```

Die Ausgabe wäre:

```
Hallo, Bob!
```

Indem Sie diese Schritte befolgen, können Sie die Blade-Vorlagenengine mit Flight integrieren und sie zum Rendern Ihrer Views verwenden.