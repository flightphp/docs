# HTML-Ansichten und Templates

## Überblick

Flight bietet standardmäßig eine grundlegende HTML-Template-Funktionalität. Templating ist eine sehr effektive Möglichkeit, Ihre Anwendungslogik von Ihrer Präsentationsebene zu entkoppeln. Eine dedizierte Engine (Twig, Latte, usw.) gibt [KI-Coding-Tools](/learn/ai) ebenfalls eine vertraute, eingeschränkte Syntax, sodass diese weniger dazu neigen, Geschäftslogik in Ihr HTML zu schreiben.

## Verständnis

Wenn Sie eine Anwendung erstellen, haben Sie wahrscheinlich HTML, das Sie an den Endbenutzer ausliefern möchten. PHP ist selbst eine Template-Sprache, aber es ist _sehr_ einfach, Geschäftslogik wie Datenbankaufrufe, API-Aufrufe usw. in Ihre HTML-Datei zu packen und Testen und Entkopplung zu einem sehr schwierigen Prozess zu machen. Indem Sie Daten in ein Template verschieben und das Template sich selbst rendern lassen, wird es viel einfacher, Ihren Code zu entkoppeln und Unit-Tests zu schreiben. Sie werden uns danken, wenn Sie Templates verwenden!

## Grundlegende Verwendung

Flight ermöglicht es Ihnen, die Standard-View-Engine einfach durch Zuordnung von `render` (oder Registrierung einer View-Klasse) auszutauschen. Scrollen Sie nach unten für Twig, Latte, Smarty, Blade und mehr.

> **Skeleton-Standard:** Das offizielle [flightphp/skeleton](https://github.com/flightphp/skeleton) verwendet unter `app/views/` (`*.twig`) **nur Twig**. Controller rufen `$this->app->render('welcome', $data)` auf (Endung optional). Das ist eine Anwendungsentscheidung für neue Projekte – keine Anforderung des Flight-Kerns. Latte und andere Engines bleiben voll unterstützt.

### Twig

<span class="badge bg-info">Skeleton-Standard</span>

[Twig](https://twig.symfony.com/) ist eine flexible, schnelle und sichere Template-Engine, die von Symfony und vielen anderen PHP-Projekten verwendet wird. KI-Coding-Tools kennen Twig besonders gut, und es escaped Ausgaben standardmäßig automatisch, was vor XSS schützt.

#### Installation

```bash
composer require twig/twig
```

(Bereits enthalten, wenn Sie `composer create-project flightphp/skeleton` verwenden.)

#### Grundlegende Konfiguration

Überschreiben Sie die `render`-Methode, um Twig anstelle des standardmäßigen PHP-Renderers zu verwenden:

```php
// überschreibe die render-Methode, um Twig anstelle des standardmäßigen PHP-Renderers zu verwenden
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Wo Twig seine kompilierten Templates speichert
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// "welcome" oder "welcome.twig" erlauben
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

Im Skeleton befindet sich diese Verdrahtung in `app/config/services.php` (gemeinsame Twig-Umgebung, Cache-Pfad, Globals wie `base_url` / CSP-Nonce). Bevorzugen Sie das Injizieren von `Engine` und das Aufrufen von `$app->render()` aus Controllern, damit der Code [KI- und testfreundlich](/learn/ai) bleibt.

#### Twig in Flight verwenden

Jetzt, wo Sie mit Twig rendern können, können Sie beispielsweise Folgendes tun:

```html
{# app/views/home.twig #}
<html>
  <head>
	<title>{% if title %}{{ title }} - {% endif %}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {{ name }}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.twig', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Wenn Sie in Ihrem Browser `/Bob` aufrufen, wäre die Ausgabe:

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

Ein vollständigeres Beispiel für die Verwendung von Twig mit Layouts finden Sie im Abschnitt [awesome plugins](/awesome-plugins/twig) dieser Dokumentation. Für Metriken zur Renderzeit in der Tracy-Leiste siehe [Twig-Panel in Tracy Extensions](/awesome-plugins/tracy-extensions#twig-panel-optional).

Weitere Informationen zu den vollständigen Funktionen von Twig finden Sie in der [offiziellen Dokumentation](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">großartige Alternative</span>

[Latte](https://latte.nette.org/) ist eine voll ausgestattete Engine mit einer PHP-ähnlichen Syntax. Sie ist nach wie vor eine ausgezeichnete Wahl für Flight-Apps; das Skeleton standardisiert lediglich auf Twig als gemeinsamen Standard (besonders hilfreich, wenn KI-Tools Templates generieren).

#### Installation

```bash
composer require latte/latte
```

#### Grundlegende Konfiguration

Die Grundidee ist, dass Sie die `render`-Methode überschreiben, um Latte anstelle des standardmäßigen PHP-Renderers zu verwenden.

```php
// überschreibe die render-Methode, um Latte anstelle des standardmäßigen PHP-Renderers zu verwenden
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Wo Latte seinen Cache speichert
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Latte in Flight verwenden

Jetzt, wo Sie mit Latte rendern können, können Sie beispielsweise Folgendes tun:

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

Wenn Sie in Ihrem Browser `/Bob` aufrufen, wäre die Ausgabe:

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

Ein komplexeres Beispiel für die Verwendung von Latte mit Layouts finden Sie im Abschnitt [awesome plugins](/awesome-plugins/latte) dieser Dokumentation.

Weitere Informationen zu den vollständigen Funktionen von Latte, einschließlich Übersetzungs- und Sprachfunktionen, finden Sie in der [offiziellen Dokumentation](https://latte.nette.org/en/).

### Integrierte View-Engine

<span class="badge bg-warning">veraltet</span>

> **Hinweis:** Dies ist weiterhin die Standardfunktionalität und funktioniert technisch weiterhin.

Um eine View-Template anzuzeigen, rufen Sie die `render`-Methode mit dem Namen der Template-Datei und optionalen Template-Daten auf:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Die übergebenen Template-Daten werden automatisch in das Template injiziert und können wie lokale Variablen referenziert werden. Template-Dateien sind einfach PHP-Dateien. Wenn der Inhalt der Template-Datei `hello.php` ist:

```php
Hello, <?= $name ?>!
```

Die Ausgabe wäre:

```text
Hello, Bob!
```

Sie können Ansichtsvariablen auch manuell über die set-Methode festlegen:

```php
Flight::view()->set('name', 'Bob');
```

Die Variable `name` ist nun in allen Ihren Ansichten verfügbar. Sie können also einfach Folgendes tun:

```php
Flight::render('hello');
```

Beachten Sie, dass Sie bei der Angabe des Templatenamens in der render-Methode die Erweiterung `.php` weglassen können.

Standardmäßig sucht Flight in einem `views`-Verzeichnis nach Template-Dateien. Sie können einen alternativen Pfad für Ihre Templates festlegen, indem Sie die folgende Konfiguration setzen:

```php
Flight::set('flight.views.path', '/path/to/views');
```

Standardmäßig akzeptiert die integrierte `View` von Flight auch einen absoluten Template-Pfad oder einen Namen, der aus diesem Verzeichnis hinausgeht. Für die meisten Anwendungen sollten Sie das einschränken:

```php
Flight::set('flight.views.restrict_to_path', true);
```

Das hält `render()`, `fetch()` und `exists()` innerhalb von `flight.views.path`. Es ist standardmäßig deaktiviert, um Abwärtskompatibilität zu gewährleisten. Siehe [Sicherheit](/learn/security#flightviewsrestrict_to_path).

#### Layouts

Es ist üblich, dass Websites eine einzige Layout-Template-Datei mit wechselndem Inhalt haben. Um Inhalte zu rendern, die in einem Layout verwendet werden sollen, können Sie einen optionalen Parameter an die `render`-Methode übergeben.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Ihre Ansicht hat dann gespeicherte Variablen namens `headerContent` und `bodyContent`. Sie können Ihr Layout dann wie folgt rendern:

```php
Flight::render('layout', ['title' => 'Home Page']);
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
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

### Smarty

Hier erfahren Sie, wie Sie die [Smarty](http://www.smarty.net/)-Template-Engine für Ihre Ansichten verwenden:

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

// Template-Daten zuweisen
Flight::view()->assign('name', 'Bob');

// Template anzeigen
Flight::view()->display('hello.tpl');
```

Der Vollständigkeit halber sollten Sie auch die standardmäßige render-Methode von Flight überschreiben:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Hier erfahren Sie, wie Sie die [Blade](https://laravel.com/docs/8.x/blade)-Template-Engine für Ihre Ansichten verwenden:

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

// Template-Daten zuweisen
Flight::view()->share('name', 'Bob');

// Template anzeigen
echo Flight::view()->run('hello', []);
```

Der Vollständigkeit halber sollten Sie auch die standardmäßige render-Methode von Flight überschreiben:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

In diesem Beispiel könnte die Template-Datei `hello.blade.php` wie folgt aussehen:

```php
<?php
Hello, {{ $name }}!
```

Die Ausgabe wäre:

```
Hello, Bob!
```

## Siehe auch
- [Installation](/install) – Skeleton-Layout (`app/views/*.twig`) für neue Projekte.
- [Erweiterung](/learn/extending) – Wie Sie die `render`-Methode überschreiben, um eine andere Template-Engine zu verwenden.
- [Routing](/learn/routing) – Wie Sie Routen auf Controller abbilden und Ansichten rendern.
- [Antworten](/learn/responses) – Wie Sie HTTP-Antworten anpassen.
- [Sicherheit](/learn/security) – Automatisches Escaping, XSS und `flight.views.restrict_to_path`.
- [KI & Entwicklererfahrung](/learn/ai) – Warum ein einheitlicher View-Engine-Standard Coding-Agenten hilft.
- [Warum ein Framework?](/learn/why-frameworks) – Wie Templates in das große Ganze passen.

## Fehlerbehebung
- Wenn Sie eine Weiterleitung in Ihrer Middleware haben, Ihre App aber scheinbar nicht weiterleitet, stellen Sie sicher, dass Sie eine `exit;`-Anweisung in Ihrer Middleware hinzufügen.
- Wenn Twig ein Template nicht finden kann, überprüfen Sie `flight.views.path` und dass die Datei unter diesem Pfad mit der erwarteten Erweiterung existiert (Skeleton: `app/views/`).

## Changelog
- Doku – `flight.views.restrict_to_path` für native PHP-Ansichten dokumentiert.
- Doku – Twig als offizieller Skeleton-Standard dokumentiert; Latte bleibt eine erstklassige Alternative.
- v2.0 – Erstveröffentlichung.