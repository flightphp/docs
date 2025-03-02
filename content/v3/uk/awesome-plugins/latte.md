# Latte

[Latte](https://latte.nette.org/en/guide) — це повнофункціональний шаблонний引擎, який дуже простий у використанні і виглядає ближче до синтаксису PHP, ніж Twig або Smarty. Його також дуже легко розширити та додати власні фільтри та функції.

## Встановлення

Встановіть за допомогою composer.

```bash
composer require latte/latte
```

## Основна конфігурація

Існує кілька основних конфігураційних параметрів для початку роботи. Ви можете дізнатися про них більше в [Latte Documentation](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Тут Latte буде кешувати ваші шаблони, щоб прискорити процес
	// Однією з чудових особливостей Latte є те, що він автоматично оновлює ваш
	// кеш, коли ви вносите зміни у ваші шаблони!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Скажіть Latte, де буде коренева директорія для ваших шаблонів.
	// $app->get('flight.views.path') встановлюється у файлі config.php
	//   Ви також могли б просто зробити щось на кшталт `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Приклад простого макету

Ось простий приклад файлу макету. Це файл, який буде використано для обгортання всіх інших ваших шаблонів.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Мій додаток</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- ваші елементи навігації тут -->
			</nav>
		</header>
		<div id="content">
			<!-- Тут і є ця магія -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Авторські права
		</div>
	</body>
</html>
```

А тепер у нас є ваш файл, який буде відображатися всередині цього блоку контенту:

```html
<!-- app/views/home.latte -->
<!-- Це говорить Latte, що цей файл "всередині" файлу layout.latte -->
{extends layout.latte}

<!-- Це контент, який буде відображатися всередині макету в блоці контенту -->
{block content}
	<h1>Головна сторінка</h1>
	<p>Ласкаво просимо до мого додатку!</p>
{/block}
```

Отже, коли ви будете відображати це у вашій функції або контролері, ви можете зробити щось на кшталт цього:

```php
// простий маршрут
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Головна сторінка'
	]);
});

// або якщо ви використовуєте контролер
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Головна сторінка'
		]);
	}
}
```

Дивіться [Latte Documentation](https://latte.nette.org/en/guide) для отримання додаткової інформації про те, як максимально використовувати Latte!