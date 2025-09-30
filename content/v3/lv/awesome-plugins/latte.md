# Latte

[Latte](https://latte.nette.org/en/guide) ir pilnvērtīgs veidņu dz motor, kas ir ļoti viegli lietojams un jūtas tuvāk PHP sintaksei nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Instalācija

Instalējiet ar composer.

```bash
composer require latte/latte
```

## Pamata konfigurācija

Ir dažas pamata konfigurācijas opcijas, lai sāktu. Jūs varat lasīt vairāk par tām [Latte dokumentācijā](https://latte.nette.org/en/guide).

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Kur latte specifiski glabā savu kešu
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

## Vienkāršs izkārtojuma piemērs

Šeit ir vienkāršs izkārtojuma faila piemērs. Šis ir fails, kas tiks izmantots, lai aptvertu visas jūsu citas skatus.

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
				<!-- jūsu navigācijas elementi šeit -->
			</nav>
		</header>
		<div id="content">
			<!-- Šī ir maģija šeit -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

Un tagad mums ir jūsu fails, kas tiks renderēts tajā satura blokā:

```html
<!-- app/views/home.latte -->
<!-- Tas pasaka Latte, ka šis fails ir "iekšā" layout.latte failā -->
{extends layout.latte}

<!-- Šis ir saturs, kas tiks renderēts izkārtojumā satura blokā -->
{block content}
	<h1>Sākumlapa</h1>
	<p>Sveiki manā lietotnē!</p>
{/block}
```

Pēc tam, kad jūs dodaties renderēt to savā funkcijā vai kontrolierī, jūs darītu kaut ko šādu:

```php
// vienkāršs maršruts
Flight::route('/', function () {
	Flight::render('home.latte', [
		'title' => 'Sākumlapa'
	]);
});

// vai ja jūs izmantojat kontrollieri
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::render('home.latte', [
			'title' => 'Sākumlapa'
		]);
	}
}
```

Skatiet [Latte dokumentāciju](https://latte.nette.org/en/guide), lai iegūtu vairāk informācijas par to, kā izmantot Latte pilnā potenciālā!

## Kļūdu labošana ar Tracy

_PHP 8.1+ ir nepieciešams šai sadaļai._

Jūs varat izmantot arī [Tracy](https://tracy.nette.org/en/), lai palīdzētu ar jūsu Latte veidņu failu kļūdu labošanu tieši no kastes! Ja jums jau ir instalēts Tracy, jums jāpievieno Latte paplašinājums Tracy.

```php
// services.php
use Tracy\Debugger;

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Kur latte specifiski glabā savu kešu
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	// Tas pievienos paplašinājumu tikai tad, ja ir iespējota Tracy atkļūdošanas josla
	if (Debugger::$showBar === true) {
		// šeit jūs pievienojat Latte paneli Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}
	$latte->render($finalPath, $data, $block);
});
```