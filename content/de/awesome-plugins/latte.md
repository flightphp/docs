# Latte

Latte ist eine vollständig ausgestattete Vorlagen-Engine, die sehr einfach zu bedienen ist und sich näher an einer PHP-Syntax als Twig oder Smarty anfühlt. Es ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Installation

Mit Composer installieren.

```bash
composer require latte/latte
```

## Grundlegende Konfiguration

Es gibt einige grundlegende Konfigurationsoptionen, um zu beginnen. Weitere Informationen dazu finden Sie in der [Latte-Dokumentation](https://latte.nette.org/de/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Hier wird Latte Ihre Vorlagen zwischenspeichern, um die Dinge zu beschleunigen
	// Eine interessante Sache an Latte ist, dass es automatisch Ihre
	// Zwischenspeicher aktualisiert, wenn Sie Änderungen an Ihren Vorlagen vornehmen!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Geben Sie Latte an, wo sich das Stammverzeichnis Ihrer Ansichten befindet.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## Einfaches Layout-Beispiel

Hier ist ein einfaches Beispiel für eine Layout-Datei. Diese Datei wird verwendet, um alle Ihre anderen Ansichten zu umschließen.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="de">
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
<!-- Dies teilt Latte mit, dass diese Datei "innerhalb" der layout.latte-Datei liegt -->
{extends layout.latte}

<!-- Dies ist der Inhalt, der innerhalb des Layouts im Inhaltsblock gerendert wird -->
{block content}
	<h1>Startseite</h1>
	<p>Willkommen in meiner App!</p>
{/block}
```

Dann, wenn Sie dies in Ihrer Funktion oder Ihrem Controller rendern möchten, würden Sie etwas wie folgt tun:

```php
// einfacher Routenaufruf
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

Sehen Sie sich die [Latte-Dokumentation](https://latte.nette.org/de/guide) für weitere Informationen darüber an, wie Sie Latte optimal nutzen können!