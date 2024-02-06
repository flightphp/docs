# Latte

Latte ir pilnīgi aprīkots veidnes dzinējs, kas ir ļoti viegli lietojams un izjūtami tuvāks PHP sintaksei nekā Twig vai Smarty. Tāpat ir ļoti viegli paplašināms un pievienot savus filtrus un funkcijas.

## Uzstādīšana

Uzstādiet ar komponistu.

```bash
composer require latte/latte
```

## Pamata konfigurācija

Ir dažas pamata konfigurācijas iespējas, ar kurām sākt darbu. Jūs varat lasīt vairāk par tām [Latte dokumentācijā](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Šeit Latte glabās jūsu veidnes, lai paātrinātu lietas
	// Viena ļoti liela lieta par Latte ir tā, ka tas automātiski atsvaidzinās jūsu
	// veidņu kešatmiņu, ja veicat izmaiņas veidnēs!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Paziņojiet Latte, kur būs jūsu skatu saknes direktorijs.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## Vienkāršs izkārtojuma piemērs

Šeit ir vienkāršs izkārtojuma faila piemērs. Šis fails tiks izmantots, lai ietinu visus pārējos skatus.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Manis App</title>
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

Un tagad mums ir jūsu fails, kas tiks atveidots iekšējās satura blokā:

```html
<!-- app/views/home.latte -->
<!-- Tas paziņo Latte, ka šis fails ir "iekšā" layout.latte failā -->
{extends layout.latte}

<!-- Šis ir saturs, kas tiks atveidots izkārtojumā iekšā satura blokā -->
{block content}
	<h1>Sākumlapa</h1>
	<p>Laipni lūgti manā lietotnē!</p>
{/block}
```

Tad, kad jūs dodies atveidot šo funkcijā vai kontrolierī, jūs darītu kaut ko līdzīgu:

```php
// vienkārša maršrutēšana
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Sākumlapa'
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
			'title' => 'Sākumlapa'
		]);
	}
}
```

Skatiet [Latte dokumentāciju](https://latte.nette.org/en/guide) papildinformācijai par Latte pilnvērtīgas izmantošanas veidiem!