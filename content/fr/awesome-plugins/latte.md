# Latte

Latte is a full featured templating engine that is very easy to use and feels closer to a PHP syntax than Twig or Smarty. It's also very easy to extend and add your own filters and functions.

## Installation

Install with composer.

```bash
composer require latte/latte
```

## Configuration de base

Il existe quelques options de configuration de base pour commencer. Vous pouvez en savoir plus à leur sujet dans la [Documentation de Latte](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// C'est ici que Latte mettra en cache vos modèles pour accélérer les choses
	// Une chose sympa avec Latte est qu'il rafraîchit automatiquement votre
	// cache lorsque vous apportez des modifications à vos modèles !
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Indiquez à Latte où se trouvera le répertoire racine de vos vues.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## Exemple de mise en page simple

Voici un exemple simple d'un fichier de mise en page. Ce fichier sera utilisé pour envelopper toutes vos autres vues.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Mon application</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- vos éléments de navigation ici -->
			</nav>
		</header>
		<div id="content">
			<!-- C'est là que la magie opère -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Droits d'auteur
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

Ensuite, lorsque vous allez rendre ceci à l'intérieur de votre fonction ou contrôleur, vous feriez quelque chose comme ceci :

```php
// route simple
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
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
		Flight::latte()->render('home.latte', [
			'title' => 'Page d\'accueil'
		]);
	}
}
```

Consultez la [Documentation de Latte](https://latte.nette.org/en/guide) pour plus d'informations sur la façon d'utiliser Latte à son plein potentiel!