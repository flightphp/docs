# Латте

Латте - это полнофункциональный шаблонизатор, который очень легко использовать и ближе к синтаксису PHP, чем Twig или Smarty. Также очень легко расширяем и добавляем свои собственные фильтры и функции.

## Установка

Установите с помощью composer.

```bash
composer require latte/latte
```

## Базовая настройка

Есть некоторые базовые настройки, с которых можно начать работу. Вы можете узнать больше о них в [Документации по Латте](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Здесь Латте будет кэшировать ваши шаблоны для ускорения работы
	// Одна из интересных особенностей Латте в том, что он автоматически обновляет
	// кеш при внесении изменений в ваши шаблоны!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Сообщите Латте, где будет расположена корневая директория для ваших представлений.
	// $app->get('flight.views.path') установлен в файле config.php
	//   Вы также можете сделать что-то вроде `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Простой пример макета

Вот простой пример файла макета. Этот файл будет использоваться для оборачивания всех ваших других представлений.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Мое приложение</title>
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
			&copy; Авторские права
		</div>
	</body>
</html>
```

И сейчас у нас есть ваш файл, который будет рендериться внутри этого блока контента:

```html
<!-- app/views/home.latte -->
<!-- Это сообщает Латте, что этот файл "внутри" файла layout.latte -->
{extends layout.latte}

<!-- Это содержимое будет рендериться внутри макета внутри блока контента -->
{block content}
	<h1>Домашняя страница</h1>
	<p>Добро пожаловать в мое приложение!</p>
{/block}
```

Затем, когда вы переходите к рендерингу этого внутри вашей функции или контроллера, вы бы сделали что-то вроде этого:

```php
// простой маршрут
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Домашняя страница'
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
			'title' => 'Домашняя страница'
		]);
	}
}
```

Смотрите [Документацию по Латте](https://latte.nette.org/en/guide) для получения дополнительной информации о том, как использовать Латте в полной мере!