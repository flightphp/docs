# Latte

Latte est un moteur de modèle complet et très facile à utiliser qui se rapproche plus de la syntaxe PHP que de Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.

## Installation

Installer avec composer.

```bash
composer require latte/latte
```

## Configuration de base

Il existe quelques options de configuration de base pour commencer. Vous pouvez en savoir plus à leur sujet dans la [documentation de Latte](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// C'est ici que Latte mettra en cache vos modèles pour accélérer les choses
	// Une chose intéressante à propos de Latte est qu'il rafraîchit automatiquement votre cache
	// lorsque vous apportez des modifications à vos modèles !
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Indiquez à Latte où se trouvera le répertoire racine de vos vues.
	// $app->get('flight.views.path') est défini dans le fichier config.php
	//   Vous pourriez également simplement faire quelque chose comme `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Exemple de mise en page simple

Voici un exemple simple de fichier de mise en page. Ce fichier sera utilisé pour envelopper toutes vos autres vues.

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
				<!-- vos éléments de navigation ici -->
			</nav>
		</header>
		<div id="content">
			<!-- C'est la magie ici -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Droit d'auteur
		</div>
	</body>
</html>
```

Et maintenant nous avons votre fichier qui va être rendu à l'intérieur de ce bloc de contenu :

```html
<!-- app/views/home.latte -->
<!-- Cela indique à Latte que ce fichier est "à l'intérieur" du fichier layout.latte -->
{extends layout.latte}

<!-- C'est le contenu qui sera rendu à l'intérieur de la mise en page à l'intérieur du bloc de contenu -->
{block content}
	<h1>Page d'accueil</h1>
	<p>Bienvenue sur mon application !</p>
{/block}
```

Ensuite, lorsque vous allez rendre ceci dans votre fonction ou contrôleur, vous feriez quelque chose comme ceci :

```php
// itinéraire simple
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Page d'accueil'
	]);
});

// ou si vous utilisez un contrôleur
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Page d'accueil'
		]);
	}
}
```

Consultez la [documentation de Latte](https://latte.nette.org/en/guide) pour plus d'informations sur comment utiliser Latte à son plein potentiel!