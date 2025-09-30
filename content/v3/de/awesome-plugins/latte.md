# Latte

[Latte](https://latte.nette.org/en/guide) ist ein vollwertiges Templating-Engine, das sehr einfach zu bedienen ist und näher an der PHP-Syntax liegt als Twig oder Smarty. Es ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Installation

Installieren Sie es mit Composer.

```bash
composer require latte/latte
```

## Grundlegende Konfiguration

Es gibt einige grundlegende Konfigurationsoptionen, um zu starten. Sie können mehr darüber in der [Latte-Dokumentation](https://latte.nette.org/en/guide) lesen.

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Wo Latte speziell seinen Cache speichert
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

## Einfaches Layout-Beispiel

Hier ist ein einfaches Beispiel für eine Layout-Datei. Dies ist die Datei, die verwendet wird, um alle Ihre anderen Views zu umschließen.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}My App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- Ihre Navigations-Elemente hier -->
			</nav>
		</header>
		<div id="content">
			<!-- Hier liegt die Magie -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

Und jetzt haben wir Ihre Datei, die in diesem Content-Block gerendert wird:

```html
<!-- app/views/home.latte -->
<!-- Dies teilt Latte mit, dass diese Datei "innerhalb" der layout.latte-Datei liegt -->
{extends layout.latte}

<!-- Dies ist der Inhalt, der innerhalb des Layouts im Content-Block gerendert wird -->
{block content}
	<h1>Startseite</h1>
	<p>Willkommen in meiner App!</p>
{/block}
```

Wenn Sie dies in Ihrer Funktion oder Ihrem Controller rendern, würden Sie etwas Ähnliches tun:

```php
// Einfache Route
Flight::route('/', function () {
	Flight::render('home.latte', [
		'title' => 'Startseite'
	]);
});

// Oder wenn Sie einen Controller verwenden
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::render('home.latte', [
			'title' => 'Startseite'
		]);
	}
}
```

Sehen Sie sich die [Latte-Dokumentation](https://latte.nette.org/en/guide) für weitere Informationen an, wie Sie Latte in vollem Umfang nutzen können!

## Debugging mit Tracy

_PHP 8.1+ ist für diesen Abschnitt erforderlich._

Sie können auch [Tracy](https://tracy.nette.org/en/) verwenden, um Ihre Latte-Template-Dateien direkt aus der Box heraus zu debuggen! Wenn Sie Tracy bereits installiert haben, müssen Sie die Latte-Erweiterung zu Tracy hinzufügen.

```php
// services.php
use Tracy\Debugger;

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Wo Latte speziell seinen Cache speichert
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	// Dies fügt die Erweiterung nur hinzu, wenn die Tracy-Debug-Bar aktiviert ist
	if (Debugger::$showBar === true) {
		// Hier fügen Sie das Latte-Panel zu Tracy hinzu
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}
	$latte->render($finalPath, $data, $block);
});
```