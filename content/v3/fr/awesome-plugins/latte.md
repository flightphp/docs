# Latte

[Latte](https://latte.nette.org/en/guide) est un moteur de templating complet qui est très facile à utiliser et qui ressemble plus à une syntaxe PHP que Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.

## Installation

Installez avec composer.

```bash
composer require latte/latte
```

## Configuration de base

Il existe quelques options de configuration de base pour commencer. Vous pouvez en lire plus à leur sujet dans la [Documentation Latte](https://latte.nette.org/en/guide).

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Où latte stocke spécifiquement son cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

## Exemple simple de layout

Voici un exemple simple d'un fichier de layout. C'est le fichier qui sera utilisé pour envelopper toutes vos autres vues.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Mon App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- vos éléments de navigation ici -->
			</nav>
		</header>
		<div id="content">
			<!-- C'est la magie ici -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

Et maintenant, nous avons votre fichier qui va s'afficher à l'intérieur de ce bloc de contenu :

```html
<!-- app/views/home.latte -->
<!-- Ceci indique à Latte que ce fichier est "à l'intérieur" du fichier layout.latte -->
{extends layout.latte}

<!-- C'est le contenu qui sera affiché à l'intérieur du layout dans le bloc de contenu -->
{block content}
	<h1>Page d'accueil</h1>
	<p>Bienvenue dans mon app !</p>
{/block}
```

Puis, lorsque vous allez afficher cela dans votre fonction ou votre contrôleur, vous feriez quelque chose comme ceci :

```php
// route simple
Flight::route('/', function () {
	Flight::render('home.latte', [
		'title' => 'Page d\'accueil'
	]);
});

// ou si vous utilisez un contrôleur
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::render('home.latte', [
			'title' => 'Page d\'accueil'
		]);
	}
}
```

Consultez la [Documentation Latte](https://latte.nette.org/en/guide) pour plus d'informations sur la façon d'utiliser Latte à son plein potentiel !

## Débogage avec Tracy

_PHP 8.1+ est requis pour cette section._

Vous pouvez également utiliser [Tracy](https://tracy.nette.org/en/) pour vous aider à déboguer vos fichiers de template Latte directement ! Si vous avez déjà installé Tracy, vous devez ajouter l'extension Latte à Tracy.

```php
// services.php
use Tracy\Debugger;

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Où latte stocke spécifiquement son cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	// Ceci n'ajoutera l'extension que si la barre de débogage Tracy est activée
	if (Debugger::$showBar === true) {
		// c'est ici que vous ajoutez le panneau Latte à Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}
	$latte->render($finalPath, $data, $block);
});
```