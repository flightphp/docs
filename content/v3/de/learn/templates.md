# HTML-Ansichten und Vorlagen

## Überblick

Flight bietet standardmäßig einige grundlegende Funktionen für HTML-Templating. Templating ist eine sehr effektive Methode, um die Anwendungslogik von der Präsentationsschicht zu trennen.

## Verständnis

Wenn Sie eine Anwendung erstellen, haben Sie wahrscheinlich HTML, das Sie an den Endbenutzer zurückliefern möchten. PHP ist an sich eine Templating-Sprache, aber es ist _sehr_ einfach, Geschäftslogik wie Datenbankaufrufe, API-Aufrufe usw. in Ihre HTML-Datei zu integrieren und das Testen und Entkoppeln zu einem sehr schwierigen Prozess zu machen. Indem Sie Daten in eine Vorlage schieben und die Vorlage sich selbst rendern lassen, wird es viel einfacher, Ihren Code zu entkoppeln und Unit-Tests durchzuführen. Sie werden uns dankbar sein, wenn Sie Vorlagen verwenden!

## Grundlegende Verwendung

Flight ermöglicht es Ihnen, den Standard-View-Engine einfach zu ersetzen, indem Sie Ihre eigene View-Klasse registrieren. Scrollen Sie nach unten, um Beispiele zu sehen, wie Sie Smarty, Latte, Blade und mehr verwenden können!

### Latte

<span class="badge bg-info">empfohlen</span>

Hier ist, wie Sie den [Latte](https://latte.nette.org/)
Template-Engine für Ihre Ansichten verwenden würden.

#### Installation

```bash
composer require latte/latte
```

#### Grundlegende Konfiguration

Die Hauptidee ist, dass Sie die `render`-Methode überschreiben, um Latte anstelle des Standard-PHP-Renders zu verwenden.

```php
// überschreiben der render-Methode, um Latte anstelle des Standard-PHP-Renders zu verwenden
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Wo Latte speziell seinen Cache speichert
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Verwendung von Latte in Flight

Jetzt, da Sie mit Latte rendern können, können Sie etwas wie das tun:

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Wenn Sie `/Bob` in Ihrem Browser besuchen, wäre die Ausgabe:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Weiterführende Lektüre

Ein komplexeres Beispiel zur Verwendung von Latte mit Layouts wird im Abschnitt [awesome plugins](/awesome-plugins/latte) dieser Dokumentation gezeigt.

Sie können mehr über die vollen Fähigkeiten von Latte, einschließlich Übersetzung und Sprachfähigkeiten, erfahren, indem Sie die [offizielle Dokumentation](https://latte.nette.org/en/) lesen.

### Eingebauter View-Engine

<span class="badge bg-warning">veraltet</span>

> **Hinweis:** Obwohl dies immer noch die Standardfunktionalität ist und technisch noch funktioniert.

Um eine View-Vorlage anzuzeigen, rufen Sie die `render`-Methode mit dem Namen
der Vorlagendatei und optionalen Vorlagendaten auf:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Die Vorlagendaten, die Sie übergeben, werden automatisch in die Vorlage injiziert und können
wie eine lokale Variable referenziert werden. Vorlagendateien sind einfach PHP-Dateien. Wenn der
Inhalt der `hello.php`-Vorlagendatei so aussieht:

```php
Hello, <?= $name ?>!
```

Wäre die Ausgabe:

```text
Hello, Bob!
```

Sie können View-Variablen auch manuell mit der `set`-Methode festlegen:

```php
Flight::view()->set('name', 'Bob');
```

Die Variable `name` ist jetzt in allen Ihren Views verfügbar. Also können Sie einfach tun:

```php
Flight::render('hello');
```

Beachten Sie, dass beim Angabe des Namens der Vorlage in der `render`-Methode die
`.php`-Erweiterung weggelassen werden kann.

Standardmäßig sucht Flight nach einem `views`-Verzeichnis für Vorlagendateien. Sie können
einen alternativen Pfad für Ihre Vorlagen festlegen, indem Sie die folgende Konfiguration setzen:

```php
Flight::set('flight.views.path', '/path/to/views');
```

#### Layouts

Es ist üblich, dass Websites eine einzige Layout-Vorlagendatei mit austauschbarem
Inhalt haben. Um Inhalt zu rendern, der in einem Layout verwendet werden soll, können Sie einen optionalen
Parameter an die `render`-Methode übergeben.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Ihre View wird dann gespeicherte Variablen namens `headerContent` und `bodyContent` haben.
Sie können dann Ihr Layout rendern, indem Sie tun:

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

### Smarty

Hier ist, wie Sie den [Smarty](http://www.smarty.net/)
Template-Engine für Ihre Ansichten verwenden würden:

```php
// Laden der Smarty-Bibliothek
require './Smarty/libs/Smarty.class.php';

// Registrieren von Smarty als View-Klasse
// Auch Übergeben einer Callback-Funktion, um Smarty beim Laden zu konfigurieren
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Zuweisen von Vorlagendaten
Flight::view()->assign('name', 'Bob');

// Anzeigen der Vorlage
Flight::view()->display('hello.tpl');
```

Zur Vollständigkeit sollten Sie auch die Standard-`render`-Methode von Flight überschreiben:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Hier ist, wie Sie den [Blade](https://laravel.com/docs/8.x/blade) Template-Engine für Ihre Ansichten verwenden würden:

Zuerst müssen Sie die BladeOne-Bibliothek über Composer installieren:

```bash
composer require eftec/bladeone
```

Dann können Sie BladeOne als View-Klasse in Flight konfigurieren:

```php
<?php
// Laden der BladeOne-Bibliothek
use eftec\bladeone\BladeOne;

// Registrieren von BladeOne als View-Klasse
// Auch Übergeben einer Callback-Funktion, um BladeOne beim Laden zu konfigurieren
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Zuweisen von Vorlagendaten
Flight::view()->share('name', 'Bob');

// Anzeigen der Vorlage
echo Flight::view()->run('hello', []);
```

Zur Vollständigkeit sollten Sie auch die Standard-`render`-Methode von Flight überschreiben:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

In diesem Beispiel könnte die Datei hello.blade.php so aussehen:

```php
<?php
Hello, {{ $name }}!
```

Die Ausgabe wäre:

```
Hello, Bob!
```

## Siehe auch
- [Erweitern](/learn/extending) - Wie man die `render`-Methode überschreibt, um einen anderen Template-Engine zu verwenden.
- [Routing](/learn/routing) - Wie man Routen zu Controllern zuweist und Views rendert.
- [Responses](/learn/responses) - Wie man HTTP-Antworten anpasst.
- [Warum ein Framework?](/learn/why-frameworks) - Wie Vorlagen ins Gesamtbild passen.

## Fehlerbehebung
- Wenn Sie eine Weiterleitung in Ihrem Middleware haben, aber Ihre App scheint nicht weiterzuleiten, stellen Sie sicher, dass Sie eine `exit;`-Anweisung in Ihrem Middleware hinzufügen.

## Changelog
- v2.0 - Erste Veröffentlichung.