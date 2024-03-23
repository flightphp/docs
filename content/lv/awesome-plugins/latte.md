# Kafija

Kafija ir pilnībā funkciju bagātota veidne, kas ir ļoti viegli lietojama un jūtas tuvāka PHP sintaksei nekā Twig vai Smarty. Turklāt to ir ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Instalācija

Instalējiet ar komponistu.

```bash
composer require latte/latte
```

## Pamata konfigurācija

Ir dažas pamata konfigurācijas opcijas, lai sāktu darbu. Par tām variet lasīt vairāk [Kafijas Dokumentācijā](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Šeit Kafija saglabās jūsu veidnes, lai ātrinātu lietošanu
	// Viena jauka lieta par Kafiju ir tā, ka tā automātiski atjaunina jūsu
	// kešatmiņu, kad veicat izmaiņas veidnēs!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Paziņojiet Kafijai, kur būs jūsu skatu saknes direktorija.
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Vienkārša izkārtojuma piemērs

Šeit ir vienkāršs piemērs izkārtojuma failam. Šis fails tiks izmantots, lai iesaiņotu visus pārējos skatus.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="lv">
	<head>
		<title>{$title ? $title . ' - '}Mana Viedokļu Uzstādība</title>
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

Un tagad mums ir jūsu fails, kas tiks atveidots iekšējā šī satura blokā:

```html
<!-- app/views/home.latte -->
<!-- Tas paziņo Kafijai, ka šis fails ir "iekšpus" layout.latte faila -->
{extends layout.latte}

<!-- Šis ir saturs, kas tiks atveidots izkārtojumā iekšienē satura blokā -->
{block content}
	<h1>Mājas Lapaspuse</h1>
	<p>Sveicināti manā aplikācijā!</p>
{/block}
```

Tad, kad jūs dodies atveidot to iekš savas funkcijas vai kontrolierā, jūs darītu kaut ko līdzīgu tam:

```php
// vienkāršs maršruts
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Mājas Lapaspuse'
	]);
});

// vai, ja izmantojat kontroleri
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Mājas Lapaspuse'
		]);
	}
}
```

Skatiet vairāk informācijas par to, kā izmantot Kafiju tā pilnīgākai potenciāla izmantošanai [Kafijas Dokumentācijā](https://latte.nette.org/en/guide)!