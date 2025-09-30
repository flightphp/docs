# Latte

[Latte](https://latte.nette.org/en/guide) — это полнофункциональный шаблонизатор, который очень прост в использовании и ближе к синтаксису PHP, чем Twig или Smarty. Его также очень легко расширять и добавлять собственные фильтры и функции.

## Установка

Установите с помощью composer.

```bash
composer require latte/latte
```

## Базовая настройка

Есть несколько базовых опций настройки для начала работы. Подробнее о них можно прочитать в [Документации Latte](https://latte.nette.org/en/guide).

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Где latte специально хранит свой кэш
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

## Простой пример макета

Вот простой пример файла макета. Это файл, который будет использоваться для обертки всех ваших других представлений.

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
				<!-- your nav elements here -->
			</nav>
		</header>
		<div id="content">
			<!-- This is the magic right here -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

А теперь у нас есть ваш файл, который будет отображаться внутри этого блока content:

```html
<!-- app/views/home.latte -->
<!-- This tells Latte that this file is "inside" the layout.latte file -->
{extends layout.latte}

<!-- This is the content that will be rendered inside the layout inside the content block -->
{block content}
	<h1>Home Page</h1>
	<p>Welcome to my app!</p>
{/block}
```

Затем, когда вы будете отображать это внутри своей функции или контроллера, вы сделаете что-то вроде этого:

```php
// simple route
Flight::route('/', function () {
	Flight::render('home.latte', [
		'title' => 'Home Page'
	]);
});

// or if you're using a controller
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

Смотрите [Документацию Latte](https://latte.nette.org/en/guide) для получения дополнительной информации о том, как использовать Latte на полную мощность!

## Отладка с Tracy

_Требуется PHP 8.1+ для этого раздела._

Вы также можете использовать [Tracy](https://tracy.nette.org/en/) для помощи в отладке ваших файлов шаблонов Latte прямо из коробки! Если у вас уже установлен Tracy, вам нужно добавить расширение Latte к Tracy.

```php
// services.php
use Tracy\Debugger;

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Где latte специально хранит свой кэш
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	// This will only add the extension if the Tracy Debug Bar is enabled
	if (Debugger::$showBar === true) {
		// this is where you add the Latte Panel to Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}
	$latte->render($finalPath, $data, $block);
});