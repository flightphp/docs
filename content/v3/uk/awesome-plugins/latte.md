# Latte

[Latte](https://latte.nette.org/en/guide) — це потужний шаблонізатор, який дуже простий у використанні та ближчий до синтаксису PHP, ніж Twig чи Smarty. Його також легко розширювати та додавати власні фільтри й функції.

## Встановлення

Встановіть за допомогою composer.

```bash
composer require latte/latte
```

## Базова Конфігурація

Є кілька базових опцій конфігурації для початку. Більше про них можна прочитати в [Документації Latte](https://latte.nette.org/en/guide).

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Де Latte зберігає свій кеш
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

## Простий Приклад Макету

Ось простий приклад файлу макету. Це файл, який буде використовуватися для обгортання всіх ваших інших представлень.

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
				<!-- ваші елементи навігації тут -->
			</nav>
		</header>
		<div id="content">
			<!-- Ось тут магія -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

А тепер у нас є ваш файл, який буде рендеритися всередині блоку content:

```html
<!-- app/views/home.latte -->
<!-- Це повідомляє Latte, що цей файл "всередині" файлу layout.latte -->
{extends layout.latte}

<!-- Це вміст, який буде рендеритися всередині макету в блоці content -->
{block content}
	<h1>Головна Сторінка</h1>
	<p>Ласкаво просимо до моєї програми!</p>
{/block}
```

Потім, коли ви йдете рендерити це у вашій функції чи контролері, ви робите щось на кшталт цього:

```php
// простий маршрут
Flight::route('/', function () {
	Flight::render('home.latte', [
		'title' => 'Home Page'
	]);
});

// або якщо ви використовуєте контролер
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::render('home.latte', [
			'title' => 'Home Page'
		]);
	}
}
```

Дивіться [Документацію Latte](https://latte.nette.org/en/guide) для отримання додаткової інформації про те, як використовувати Latte на повну потужність!

## Налагодження з Tracy

_Потрібен PHP 8.1+ для цієї секції._

Ви також можете використовувати [Tracy](https://tracy.nette.org/en/) для допомоги в налагодженні ваших файлів шаблонів Latte прямо з коробки! Якщо у вас вже встановлено Tracy, вам потрібно додати розширення Latte до Tracy.

```php
// services.php
use Tracy\Debugger;

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Де Latte зберігає свій кеш
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	// Це додасть розширення тільки якщо панель налагодження Tracy увімкнена
	if (Debugger::$showBar === true) {
		// ось де ви додаєте панель Latte до Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}
	$latte->render($finalPath, $data, $block);
});