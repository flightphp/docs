
# Latte

[Latte](https://latte.nette.org/en/guide) - это полнофункциональный движок шаблонов, который очень прост в использовании и ближе к синтаксису PHP, чем Twig или Smarty. Также очень легко расширяем и добавляем собственные фильтры и функции.

## Установка

Установите с помощью composer.

```bash
composer require latte/latte
```

## Основная конфигурация

Есть несколько основных опций конфигурации, с которых можно начать. Вы можете узнать больше о них в [Документации по Latte](https://latte.nette.org/en/guide).

```php
use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Здесь Latte будет кэшировать ваши шаблоны для ускорения работы
	// Одна из хороших вещей в Latte заключается в том, что он автоматически обновляет ваш
	// кэш при внесении изменений в ваши шаблоны!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Скажите Latte, где будет базовый каталог для ваших представлений.
	// $app->get('flight.views.path') устанавливается в файле config.php
	//   Вы также можете просто сделать что-то вроде `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Простой пример макета

Вот простой пример файла макета. Этот файл будет использоваться для оформления всех ваших других представлений.

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

И теперь у нас есть ваш файл, который будет отображаться внутри этого блока контента:

```html
<!-- app/views/home.latte -->
<!-- Это говорит Latte, что этот файл "внутри" файла layout.latte -->
{extends layout.latte}

<!-- Это содержимое, которое будет отображаться в макете внутри блока контента -->
{block content}
	<h1>Домашняя страница</h1>
	<p>Добро пожаловать в мое приложение!</p>
{/block}
```

Затем, когда вы будете рендерить это внутри вашей функции или контроллера, вы сделаете что-то вроде этого:

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

Смотрите [Документацию по Latte](https://latte.nette.org/en/guide) для получения дополнительной информации о том, как использовать Latte в полной мере!