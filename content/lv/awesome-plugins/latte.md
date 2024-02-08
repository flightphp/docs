# Latvian

# Kafija

Kafija ir pilna funkciju sagatavošanas dzinējs, kas ir ļoti viegli lietojams un jūtas tuvāk PHP sintaksei nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Instalācija

Instalējiet ar komponistu.

```bash
composer require latte/latte
```

## Pamata konfigurācija

Ir dažas pamata konfigurācijas iespējas, ar kurām sākt. Par tām var uzzināt vairāk [Kafijas dokumentācijā] (https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('lativert', LatteEngine::class, [], function(LatteEngine $lativert) use ($app) {

	// Šis ir vieta, kur Kafija saglabās jūsu sagatavotās šablonus, lai paātrinātu lietas
	// Viens lielisks lieta par Kafiju ir tas, ka tā automātiski atjauno jūsu
	// kešatmiņu, kad veicat izmaiņas savos šablonos!
	$lativert->setTempDirectory(__DIR__ . '/../cache/');

	// Pateikt Kafijai, kur būs jūsu skatus saturošais saknis direktorijā.
	$lativert->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## Vienkāršs izkārtojuma piemērs

Šeit ir vienkāršs izkārtojuma faila piemērs. Šis fails tiks izmantots, lai apvalkātu visus jūsu citus skatus.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Manai lietotnei</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- jūsu navigācijas elementi šeit -->
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

Un tagad mums ir jūsu fails, kas tiks atskaņots iekšējā šajā saturu blokā:

```html
<!-- app/views/home.latte -->
<!-- Tas pateiks Kafijai, ka šis fails ir "iekšā" layout.latte failā -->
{extends layout.latte}

<!-- Tas ir saturs, kas tiks atskaņots iekšējā izkārtojumā iekšējā saturu blokā -->
{block content}
	<h1>Mājas lapā</h1>
	<p>Laipni lūdzam manā lietotnē!</p>
{/block}
```

Tad, kad jūs atskaņojat to iekšā savā funkcijā vai kontrolētājā, jums būtu jādara kaut kas tāds:

```php
// vienkārša maršruta
Flight::route('/', function () {
	Flight::lativert()->render('home.latte', [
		'title' => 'Sākumlapa'
	]);
});

// vai ja izmantojat kontrolētāju
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
klase HomeController
{
	public function index()
	{
		Flight::lativert()->render('home.latte', [
			'title' => 'Sākumlapa'
		]);
	}
}
```

Redziet [Kafijas dokumentāciju] (https://latte.nette.org/en/guide), lai iegūtu vairāk informācijas par to, kā Kafiju izmantot tā pilnīgā potenciālā!