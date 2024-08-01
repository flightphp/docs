# Latte

Latte ir pilnīgi funkciju bagātināta templēšanas dzinējs, kas ir ļoti viegli lietojams un sajūtas tuvāks PHP sintaksei nekā Twig vai Smarty. Tas ir arī ļoti viegli paplašināms un pievienot savus filtrus un funkcijas.

## Instalācija

Instalēt ar komponistu.

```bash
composer require latte/latte
```

## Pamata konfigurācija

Ir dažas pamata konfigurācijas iespējas, lai sāktu. Tu vari lasīt vairāk par tām [Latte dokumentācijā](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Šeit Latte saglabās tavas šablones, lai paātrinātu lietas
	// Viena forša lieta par Latte ir tā, ka tā automātiski atjaunos tavu
	// kešatmiņu, kad veicat izmaiņas savās šablonos!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Paziņo Latte, kur būs tava skatu saknes mape.
	// $app->get('flight.views.path') ir iestatīts failā config.php
	//   Tu varētu arī vienkārši kaut ko darīt tādu kā `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Vienkāršs izkārtojuma piemērs

Šeit ir vienkāršs piemērs izkārtojuma failam. Šis fails tiks izmantots, lai apvilktu visus citus tavus skatus.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Man App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- tavas navigācijas vienības šeit -->
			</nav>
		</header>
		<div id="content">
			<!-- Šeit ir maģija -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Autortiesības
		</div>
	</body>
</html>
```

Un tagad mums ir fails, kas tiks renderēts iekšējā šajā saturu blokā:

```html
<!-- app/views/home.latte -->
<!-- Tas informē Latte, ka šis fails "iekšā" layout.latte failā -->
{extends layout.latte}

<!-- Tas ir saturs, kas tiks renderēts iekšā izkārtojuma saturs blokā -->
{block content}
	<h1>Mājas lapā</h1>
	<p>Laipni lūgti manā lietotnē!</p>
{/block}
```

Tad, kad tu ej renderēt to iekš savas funkcijas vai kontrolieri, tu darītu kaut ko tādu:

```php
// vienkārša ruta
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Mājas lapā'
	]);
});

// vai, ja tu izmanto kādu kontrolieri
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Mājas lapā'
		]);
	}
}
```

Skaties [Latte dokumentāciju](https://latte.nette.org/en/guide) vairāk informācijai par to, kā izmantot Latte visā tā pilnvarā!