# Kafija

[Kafija](https://latte.nette.org/en/guide) ir pilnīgi aprīkota veidnes dzinējs, kurš ir ļoti viegli lietojams un jūtas tuvāk PHP sintaksei nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Uzstādīšana

Uzstādiet ar komponistu.

```bash
composer require latte/latte
```

## Pamata konfigurācija

Ir dažas pamata konfigurācijas iespējas, ar kurām sākt. Jūs varat lasīt vairāk par tām [Kafijas dokumentācijā](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Šeit Kafija saglabās jūsu veidnes, lai paātrinātu lietas
	// Viena jauka lieta par Kafiju ir tā, ka tā automātiski atsvaidzina jūsu
	// kešatmiņu, kad padarāt izmaiņas veidnēs!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Pateiciet Kafijai, kur būs jūsu skatu saknes direktorija.
	// $app->get('flight.views.path') ir iestatīts failā config.php
	// Jūs varētu arī vienkārši izdarīt kaut ko tādu kā `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Vienkāršs izkārtojuma piemērs

Šeit ir vienkāršs izkārtojuma faila piemērs. Šis fails tiks izmantots, lai iesaiņotu visus jūsu citus skatus.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Mans App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- jūsu navigācijas elementi šeit -->
			</nav>
		</header>
		<div id="content">
			<!-- Šeit ir burvība -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Autortiesības
		</div>
	</body>
</html>
```

Un tagad mums ir jūsu fails, kas tiks renderēts iekšējā šajā satura blokā:

```html
<!-- app/views/home.latte -->
<!-- Tas stāsta Kafijai, ka šis fails ir "iekšā" layout.latte failā -->
{extends layout.latte}

<!-- Šis ir saturs, kurš tiks renderēts iekšējā izkārtojumā iekšējā satura blokā -->
{block content}
	<h1>Mājas lapā</h1>
	<p>Laipni lūdzam mans app!</p>
{/block}
```

Tad, kad jūs ejat renderēt to iekšā savā funkcijā vai kontrolierī, jūs darītu kaut kas tāds:

```php
// vienkāršs maršruts
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Mājas Lapā'
	]);
});

// vai, ja izmantojat kontrolieri
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Mājas Lapā'
		]);
	}
}
```

Skatiet [Kafijas dokumentāciju](https://latte.nette.org/en/guide), lai iegūtu vairāk informācijas par to, kā izmantot Kafiju tā pilnīgākajā potenciālā!