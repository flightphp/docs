```markdown
# Латте

Латте - это полнофункциональный движок шаблонов, который очень легко использовать и ближе к синтаксису PHP, чем Twig или Smarty. Также его очень легко расширить и добавить свои собственные фильтры и функции.

## Установка

Установите с помощью композитора.

```bash
composer require latte/latte
```

## Базовая настройка

Есть несколько базовых параметров конфигурации, с которыми можно начать работу. Вы можете узнать больше о них в [Документации Латте](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Здесь Латте будет кешировать ваши шаблоны для ускорения
	// Одна из интересных особенностей Латте заключается в том, что он автоматически обновляет ваш
	// кеш при внесении изменений в ваши шаблоны!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Укажите Латте, где будет находиться корневой каталог ваших представлений.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## Простой пример макета

Вот простой пример файла макета. Этот файл будет использоваться для обертывания всех ваших других представлений.

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
			<!-- вот где волшебство -->
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

<!-- Это содержимое, которое будет отображаться внутри макета внутри блока содержимого -->
{block content}
	<h1>Домашняя страница</h1>
	<p>Добро пожаловать в мое приложение!</p>
{/block}
```

Затем, когда вы захотите отобразить это внутри вашей функции или контроллера, вы будете делать что-то вроде этого:

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

См. [Документацию Латте](https://latte.nette.org/en/guide) для более подробной информации о том, как использовать Латте на полную мощь!
```