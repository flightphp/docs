# Latte

Latte is a voll funktionsfähiger Template-Motor, der sehr einfach zu bedienen ist und sich näher an einer PHP-Syntax anfühlt als Twig oder Smarty. Es ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Installation

Installieren Sie mit Composer.

```bash
composer require latte/latte
```

## Grundkonfiguration

Es gibt einige grundlegende Konfigurationsoptionen, um loszulegen. Weitere Informationen dazu finden Sie in der [Latte-Dokumentation](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Hier speichert Latte Ihre Templates, um die Geschwindigkeit zu erhöhen
	// Eine coole Sache an Latte ist, dass es Ihren Cache automatisch erneuert,
	// wenn Sie Änderungen an Ihren Templates vornehmen!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Sagen Sie Latte, wo sich das Stammverzeichnis Ihrer Ansichten befinden wird.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## Einfaches Layout-Beispiel

Hier ist ein einfaches Beispiel für eine Layout-Datei. Diese Datei wird verwendet, um alle Ihre anderen Ansichten zu umschließen.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Meine App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- Ihre Navigationslemente hier -->
			</nav>
		</header>
		<div id="content">
			<!-- Hier passiert die Magie -->
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
<!-- Dies sagt Latte, dass diese Datei "innerhalb" der layout.latte-Datei ist -->
{extends layout.latte}

<!-- Dies ist der Inhalt, der innerhalb des Layouts im Inhaltsblock gerendert wird -->
{block content}
	<h1>Startseite</h1>
	<p>Willkommen in meiner App!</p>
{/block}
```

Dann, wenn Sie dies in Ihrer Funktion oder Ihrem Controller rendern wollen, würden Sie etwas wie folgt tun:

```php
// einfacher Route
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

Sehen Sie sich die [Latte-Dokumentation](https://latte.nette.org/en/guide) für weitere Informationen zur Verwendung von Latte in vollem Umfang an!