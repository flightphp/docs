# Latte

Latte is a full featured templating engine that is very easy to use and feels closer to a PHP syntax than Twig or Smarty. It's also very easy to extend and add your own filters and functions.

## Installation

Install with composer.

```bash
composer require latte/latte
```

## Grundkonfiguration

Es gibt einige grundlegende Konfigurationsoptionen, um zu beginnen. Sie können mehr darüber in der [Latte-Dokumentation](https://latte.nette.org/en/guide) lesen.

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Hier wird Latte Ihren Vorlagen-Cache speichern, um die Dinge zu beschleunigen
	// Eine coole Sache an Latte ist, dass es automatisch Ihren Cache aktualisiert, wenn Sie Änderungen an Ihren Vorlagen vornehmen!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Sagen Sie Latte, wo das Stammverzeichnis für Ihre Ansichten sein wird.
	// $app->get('flight.views.path') wird in der config.php-Datei festgelegt
	//   Sie könnten auch einfach etwas wie `__DIR__ . '/../views/'` machen
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Einfaches Layout-Beispiel

Hier ist ein einfaches Beispiel für eine Layout-Datei. Diese Datei wird verwendet, um alle anderen Ansichten zu umschließen.

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
			<!-- Hier ist die Magie -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Urheberrecht
		</div>
	</body>
</html>
```

Und jetzt haben wir Ihre Datei, die innerhalb dieses Inhaltsblocks gerendert wird:

```html
<!-- app/views/home.latte -->
<!-- Dies teilt Latte mit, dass diese Datei "innerhalb" der layout.latte-Datei ist -->
{extends layout.latte}

<!-- Dies ist der Inhalt, der innerhalb des Layouts im Inhaltsblock gerendert wird -->
{block content}
	<h1>Startseite</h1>
	<p>Willkommen bei meiner App!</p>
{/block}
```

Dann, wenn Sie dies in Ihrer Funktion oder Ihrem Controller rendern möchten, würden Sie etwas Ähnliches wie folgt tun:

```php
// einfache Route
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Startseite'
	]);
});

// oder wenn Sie einen Controller verwenden
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Startseite'
		]);
	}
}
```

Weitere Informationen zur Verwendung von Latte in ihrem vollen Potenzial finden Sie in der [Latte-Dokumentation](https://latte.nette.org/en/guide)!