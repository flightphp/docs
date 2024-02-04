# Latte

Latte est un moteur de modèle complet qui est très facile à utiliser et se rapproche davantage de la syntaxe PHP que de Twig ou Smarty. Il est également très facile à étendre et à ajouter vos propres filtres et fonctions.

## Installation

Installer avec composer.

```bash
composer require latte/latte
```

## Configuration de base

Il existe quelques options de configuration de base pour commencer. Vous pouvez en savoir plus à leur sujet dans la [Documentation de Latte](https://latte.nette.org/fr/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Voici où Latte mettra en cache vos modèles pour accélérer les choses
	// Une chose intéressante à propos de Latte est qu'il rafraîchit automatiquement votre
	// cache lorsque vous apportez des modifications à vos modèles!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Indiquez à Latte où se trouvera le répertoire racine de vos vues.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## Exemple de mise en page simple

Voici un exemple simple d'un fichier de mise en page. Il s'agit du fichier qui sera utilisé pour envelopper toutes vos autres vues.

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
			<!-- C'est là que se trouve la magie -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Droit d'auteur
		</div>
	</body>
</html>
```

Et maintenant, nous avons votre fichier qui va être rendu à l'intérieur de ce bloc de contenu :

```html
<!-- app/views/home.latte -->
<!-- Cela dit à Latte que ce fichier est "à l'intérieur" du fichier layout.latte -->
{extends layout.latte}

<!-- Ceci est le contenu qui sera rendu à l'intérieur de la mise en page dans le bloc de contenu -->
{block content}
	<h1>Page d'Accueil</h1>
	<p>Bienvenue sur mon app!</p>
{/block}
```

Ensuite, lorsque vous allez rendre ceci à l'intérieur de votre fonction ou contrôleur, vous feriez quelque chose comme ceci :

```php
// itinéraire simple
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Page d'Accueil'
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
			'title' => 'Page d'Accueil'
		]);
	}
}
```

Consultez la [Documentation de Latte](https://latte.nette.org/fr/guide) pour plus d'informations sur la façon d'utiliser Latte à son plein potentiel!