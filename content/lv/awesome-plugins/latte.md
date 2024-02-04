# Kafija

Kafija ir pilnīgi aprīkots sagatavošanas dzinējs, kas ir ļoti viegli izmantojams un jūtās tuvāk PHP sintaksei nekā Twig vai Smarty. Tas ir arī ļoti viegli paplašināms un pievieno saviem filtriem un funkcijām.

## Instalācija

Instalējiet ar komponistu.

```bash
composer require latte/latte
```

## Pamata konfigurācija

Ir dažas pamata konfigurācijas iespējas, ar kurām sākt. Par tām var izlasīt vairāk [Latte dokumentācijā](https://latte.nette.org/en/guide).

```php
use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Šeit Latte saglabās jūsu sagataves kešatmiņā, lai paātrinātu lietas
	// Viena jauka lieta par Latte ir tā, ka tas automātiski atsvaidzina jūsu
	// kešatmiņu, kad veicat izmaiņas savās sagatavēs!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Paziņojiet Latte, kur būs jūsu skatu sakņu katalogs.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## Vienkāršs izkārtojuma piemērs

Šeit ir vienkāršs izkārtojuma faila piemērs. Šis fails tiks izmantots, lai ietinu visas jūsu citas skatus.

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
			<!-- Šeit ir burvju notikums -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Autortiesības
		</div>
	</body>
</html>
```

Un tagad mums ir jūsu fails, kas tiks atveidots iekšā šajā saturu blokā:

```html
<!-- app/views/home.latte -->
<!-- Tas pastāsta Latte, ka šis fails ir "iekšā" layout.latte failā -->
{extends layout.latte}

<!-- Tas ir saturs, kas tiks atveidots izkārtojumā iekšā saturu blokā -->
{block content}
	<h1>Sākuma lapa</h1>
	<p>Laipni lūdzam manā lietotnē!</p>
{/block}
```

Tad, kad jūs atveidojat to iekšā savā funkcijā vai kontrolētājā, jūs varētu darīt kaut ko tādu:

```php
// vienkārša maršruta
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Sākuma lapa'
	]);
});

// vai, ja izmantojat kontrolētāju
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Sākuma lapa'
		]);
	}
}
```

Skatiet [Latte dokumentāciju](https://latte.nette.org/en/guide), lai iegūtu vairāk informācijas par Latte izmantošanu tā pilnīgā potenciālā!