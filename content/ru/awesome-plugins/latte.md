# Латте

Латте - это полнофункциональный шаблонизатор, который очень легко использовать и ближе к синтаксису PHP, чем Twig или Smarty. Также очень легко расширить и добавить собственные фильтры и функции.

## Установка

Установите с помощью composer.

```bash
composer require latte/latte
```

## Базовая конфигурация

Есть некоторые основные параметры конфигурации, с которыми можно начать работу. Вы можете узнать больше об этом в [Документации по Latte](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Здесь Латте будет кешировать ваши шаблоны, чтобы ускорить работу
	// Одна из хороших вещей в Latte - он автоматически обновляет ваш
	// кеш при внесении изменений в шаблоны!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Скажите Латте, где будет корневой каталог для ваших представлений.
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Простой пример макета

Вот простой пример файла макета. Этот файл будет использоваться для обертывания всех ваших других представлений.

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
				<!-- ваши элементы навигации здесь -->
			</nav>
		</header>
		<div id="content">
			<!-- Вот где волшебство -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

И теперь у нас есть ваш файл, который будет отображаться внутри этого блока контента:

```html
<!-- app/views/home.latte -->
<!-- Это говорит Латте, что этот файл "внутри" файла layout.latte -->
{extends layout.latte}

<!-- Это содержимое, которое будет отображаться внутри макета внутри блока content -->
{block content}
	<h1>Главная страница</h1>
	<p>Добро пожаловать в мое приложение!</p>
{/block}
```

Затем, когда вы отображаете это внутри вашей функции или контроллера, вы сделаете что-то вроде этого:

```php
// простой маршрут
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Главная страница'
	]);
});

// или если вы используете контроллер
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Главная страница'
		]);
	}
}
```

Смотрите [Документацию по Latte](https://latte.nette.org/en/guide) для получения более подробной информации о том, как использовать Latte на полную мощность!