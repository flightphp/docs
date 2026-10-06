# HTML-представления и шаблоны

## Обзор

Flight предоставляет базовую функциональность HTML-шаблонизации по умолчанию. Шаблонизация — это очень эффективный способ отделить логику приложения от уровня представления. Выделенный движок (Twig, Latte и т. д.) также даёт [инструментам ИИ для написания кода](/learn/ai) знакомый и ограниченный синтаксис, поэтому они с меньшей вероятностью будут помещать бизнес-логику в ваш HTML.

## Понимание

Когда вы создаёте приложение, вам, скорее всего, понадобится HTML, который вы захотите отдавать конечному пользователю. Сам по себе PHP является языком шаблонов, но в него _очень_ легко завернуть бизнес-логику (например, обращения к базе данных, API-вызовы и т. д.) прямо в HTML-файл, что делает тестирование и разделение кода крайне сложным процессом. Передавая данные в шаблон и позволяя шаблону отображать себя, вы значительно упрощаете разделение кода и модульное тестирование. Вы ещё скажете нам спасибо, если будете использовать шаблоны!

## Базовое использование

Flight позволяет заменить стандартный шаблонизатор, просто сопоставив `render` (или зарегистрировав класс представления). Прокрутите вниз: Twig, Latte, Smarty, Blade и другие.

> **По умолчанию в скелете:** официальный [flightphp/skeleton](https://github.com/flightphp/skeleton) использует **только Twig** в каталоге `app/views/` (`*.twig`). Контроллеры вызывают `$this->app->render('welcome', $data)` (расширение указывать необязательно). Это выбор приложения для новых проектов, а не требование ядра Flight. Latte и другие движки остаются полностью поддерживаемыми.

### Twig

<span class="badge bg-info">по умолчанию в скелете</span>

[Twig](https://twig.symfony.com/) — это гибкий, быстрый и безопасный шаблонизатор, используемый в Symfony и многих других PHP-проектах. Инструменты ИИ для написания кода, как правило, особенно хорошо знают Twig, а он по умолчанию автоматически экранирует вывод, что помогает защититься от XSS.

#### Установка

```bash
composer require twig/twig
```

(Уже включён, когда вы выполняете `composer create-project flightphp/skeleton`.)

#### Базовая настройка

Переопределите метод `render`, чтобы использовать Twig вместо стандартного PHP-рендерера:

```php
// переопределяем метод render, чтобы использовать Twig вместо стандартного PHP-рендерера
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// каталог, где Twig хранит скомпилированные шаблоны
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// Разрешаем "welcome" или "welcome.twig"
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

В скелете эта настройка находится в `app/config/services.php` (общее окружение Twig, путь к кэшу, глобальные переменные вроде `base_url` / CSP nonce). Предпочтительнее внедрять `Engine` и вызывать `$app->render()` из контроллеров, чтобы код оставался [удобным для ИИ и тестирования](/learn/ai).

#### Использование Twig во Flight

Теперь, когда вы можете рендерить с помощью Twig, можно сделать, например, следующее:

```html
{# app/views/home.twig #}
<html>
  <head>
	<title>{% if title %}{{ title }} - {% endif %}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {{ name }}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.twig', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Когда вы откроете `/Bob` в браузере, результат будет таким:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Дополнительные материалы

Более полный пример использования Twig с макетами приведён в разделе [замечательные плагины](/awesome-plugins/twig) этого документа. О метриках во время рендеринга на панели Tracy см. [панель Twig в расширениях Tracy](/awesome-plugins/tracy-extensions#twig-panel-optional).

Вы можете узнать больше о всех возможностях Twig, прочитав [официальную документацию](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">отличная альтернатива</span>

[Latte](https://latte.nette.org/) — это многофункциональный движок с синтаксисом, похожим на PHP. Он по-прежнему является отличным выбором для приложений на Flight; скелет просто стандартизирован на Twig как единый общий вариант по умолчанию (особенно полезно, когда инструменты ИИ генерируют шаблоны).

#### Установка

```bash
composer require latte/latte
```

#### Базовая настройка

Основная идея заключается в том, что вы переопределяете метод `render`, чтобы использовать Latte вместо стандартного PHP-рендерера.

```php
// переопределяем метод render, чтобы использовать Latte вместо стандартного PHP-рендерера
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// каталог, где Latte хранит свой кэш
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Использование Latte во Flight

Теперь, когда вы можете рендерить с помощью Latte, можно сделать, например, следующее:

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Когда вы откроете `/Bob` в браузере, результат будет таким:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Дополнительные материалы

Более сложный пример использования Latte с макетами приведён в разделе [замечательные плагины](/awesome-plugins/latte) этого документа.

Вы можете узнать больше о всех возможностях Latte, включая возможности перевода и локализации, прочитав [официальную документацию](https://latte.nette.org/en/).

### Встроенный движок представлений

<span class="badge bg-warning">устаревшее</span>

> **Примечание:** это по-прежнему функциональность по умолчанию, и технически она всё ещё работает.

Для отображения шаблона представления вызовите метод `render` с именем файла шаблона и необязательными данными шаблона:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Передаваемые данные шаблона автоматически внедряются в шаблон, и на них можно ссылаться как на локальные переменные. Файлы шаблонов — это обычные PHP-файлы. Если содержимое файла шаблона `hello.php` такое:

```php
Hello, <?= $name ?>!
```

Результат будет:

```text
Hello, Bob!
```

Вы также можете вручную установить переменные представления с помощью метода set:

```php
Flight::view()->set('name', 'Bob');
```

Переменная `name` теперь доступна во всех ваших представлениях. Поэтому вы можете просто сделать:

```php
Flight::render('hello');
```

Обратите внимание, что при указании имени шаблона в методе render вы можете опустить расширение `.php`.

По умолчанию Flight ищет каталог `views` для файлов шаблонов. Вы можете указать альтернативный путь для шаблонов, задав следующую конфигурацию:

```php
Flight::set('flight.views.path', '/path/to/views');
```

По умолчанию встроенный `View` во Flight также принимает абсолютный путь к шаблону или имя, которое выходит за пределы этого каталога. Для большинства приложений это стоит ограничить:

```php
Flight::set('flight.views.restrict_to_path', true);
```

Это сохраняет работу `render()`, `fetch()` и `exists()` в пределах `flight.views.path`. По умолчанию эта функция отключена для обратной совместимости. См. [Безопасность](/learn/security#flightviewsrestrict_to_path).

#### Макеты

Для веб-сайтов распространена практика использовать единый файл макета с меняющимся содержимым. Чтобы вывести содержимое, которое будет использоваться в макете, вы можете передать необязательный параметр в метод `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

После этого в представлении будут сохранены переменные `headerContent` и `bodyContent`. Затем вы можете отобразить макет следующим образом:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Если файлы шаблонов выглядят так:

`header.php`:

```php
<h1><?= $heading ?></h1>
```

`body.php`:

```php
<div><?= $body ?></div>
```

`layout.php`:

```php
<html>
  <head>
    <title><?= $title ?></title>
  </head>
  <body>
    <?= $headerContent ?>
    <?= $bodyContent ?>
  </body>
</html>
```

Результат будет:
```html
<html>
  <head>
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

### Smarty

Вот как можно использовать [Smarty](http://www.smarty.net/) в качестве шаблонизатора для ваших представлений:

```php
// Подключаем библиотеку Smarty
require './Smarty/libs/Smarty.class.php';

// Регистрируем Smarty как класс представления
// Также передаём callback-функцию для настройки Smarty при загрузке
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Передаём данные шаблона
Flight::view()->assign('name', 'Bob');

// Отображаем шаблон
Flight::view()->display('hello.tpl');
```

Для полноты картины вам также следует переопределить стандартный метод render во Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Вот как можно использовать [Blade](https://laravel.com/docs/8.x/blade) в качестве шаблонизатора для ваших представлений:

Сначала нужно установить библиотеку BladeOne через Composer:

```bash
composer require eftec/bladeone
```

Затем вы можете настроить BladeOne как класс представления во Flight:

```php
<?php
// Подключаем библиотеку BladeOne
use eftec\bladeone\BladeOne;

// Регистрируем BladeOne как класс представления
// Также передаём callback-функцию для настройки BladeOne при загрузке
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Передаём данные шаблона
Flight::view()->share('name', 'Bob');

// Отображаем шаблон
echo Flight::view()->run('hello', []);
```

Для полноты картины вам также следует переопределить стандартный метод render во Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

В этом примере файл шаблона `hello.blade.php` может выглядеть так:

```php
<?php
Hello, {{ $name }}!
```

Результат будет:

```
Hello, Bob!
```

## Смотрите также
- [Установка](/install) — Структура скелета (`app/views/*.twig`) для новых проектов.
- [Расширение](/learn/extending) — Как переопределить метод `render` для использования другого шаблонизатора.
- [Маршрутизация](/learn/routing) — Как сопоставлять маршруты с контроллерами и отображать представления.
- [Ответы](/learn/responses) — Как настраивать HTTP-ответы.
- [Безопасность](/learn/security) — Автоматическое экранирование, XSS и `flight.views.restrict_to_path`.
- [ИИ и опыт разработчика](/learn/ai) — Почему единый шаблонизатор по умолчанию помогает агентам кодирования.
- [Зачем нужен фреймворк?](/learn/why-frameworks) — Как шаблоны вписываются в общую картину.

## Устранение неполадок
- Если в вашем middleware есть перенаправление, но приложение, похоже, не перенаправляет, убедитесь, что вы добавили оператор `exit;` в свой middleware.
- Если Twig не может найти шаблон, проверьте `flight.views.path` и убедитесь, что файл существует по этому пути с ожидаемым расширением (в скелете: `app/views/`).

## История изменений
- Документация — описана `flight.views.restrict_to_path` для нативных PHP-представлений.
- Документация — Twig описан как официальный шаблонизатор по умолчанию для скелета; Latte остаётся полноценной альтернативой.
- v2.0 — Первоначальный выпуск.