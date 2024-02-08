# Латте

Латте - это полнофункциональный механизм шаблонизации, который очень легко использовать и ближе к синтаксису PHP, чем Twig или Smarty. Также очень легко расширить и добавить свои собственные фильтры и функции.

## Установка

Установите с помощью композера.

```bash
composer require latte/latte
```

## Базовая настройка

Есть несколько основных параметров конфигурации, с которыми можно начать работу. Вы можете узнать больше о них в [Документации по Латте](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Здесь Латте будет кэшировать ваши шаблоны для ускорения работы
	// Одна из интересных особенностей Латте заключается в том, что он автоматически обновляет
	// кэш при внесении изменений в ваши шаблоны!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Укажите Латте корневой каталог для ваших представлений.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## Простой пример макета

Вот простой пример файла макета. Этот файл будет использоваться для обрамления всех ваших других представлений.

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
			&copy; Авторские права
		</div>
	</body>
</html>
```

А теперь у нас есть файл, который будет отображаться внутри этого блока контента:

```html
<!-- app/views/home.latte -->
<!-- Это говорит Латте, что этот файл "внутри" файла layout.latte -->
{extends layout.latte}

<!-- Это контент, который будет отображаться внутри макета в блоке содержимого -->
{block content}
	<h1>Домашняя страница</h1>
	<p>Добро пожаловать в мое приложение!</p>
{/block}
```

Затем, когда вы собираетесь отобразить это внутри вашей функции или контроллера, вы бы сделали что-то вроде этого:

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

Смотрите [Документацию по Латте](https://latte.nette.org/en/guide) для получения дополнительной информации о том, как использовать Латте на полную мощь!