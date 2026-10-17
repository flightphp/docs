```markdown
# HTML представления и шаблоны

## Обзор

Flight по умолчанию предоставляет базовую функциональность HTML-шаблонов. Шаблонизация — это очень эффективный способ отделить логику вашего приложения от уровня представления. Выделенный движок (Twig, Latte и т.д.) также дает [инструментам ИИ](/learn/ai) знакомый и ограниченный синтаксис, поэтому они с меньшей вероятностью будут встраивать бизнес-логику в ваш HTML.

## Понимание

При создании приложения вам, скорее всего, понадобится HTML, который нужно отдать конечному пользователю. Сам по себе PHP является языком шаблонов, но _очень_ легко встроить бизнес-логику, такую как обращения к базе данных, API-вызовы и т.д., прямо в HTML-файл, что делает тестирование и разделение кода очень сложным процессом. Передавая данные в шаблон и позволяя шаблону отображать себя, становится гораздо проще разделять код и проводить модульное тестирование. Вы будете благодарны, если будете использовать шаблоны!

## Базовое использование

Flight позволяет заменить стандартный движок представлений, просто переопределив `render` (или зарегистрировав класс представления). Прокрутите вниз, чтобы узнать о Twig, Latte, Smarty, Blade и других.

> **По умолчанию в скелете:** Официальный [flightphp/skeleton](https://github.com/flightphp/skeleton) использует **только Twig** в каталоге `app/views/` (`*.twig`). Контроллеры вызывают `$this->app->render('welcome', $data)` (расширение необязательно). Это выбор приложения для новых проектов, а не требование ядра Flight. Latte и другие движки остаются полностью поддерживаемыми.

### Twig

<span class="badge bg-info">по умолчанию в скелете</span>

[Twig](https://twig.symfony.com/) — это гибкий, быстрый и безопасный шаблонизатор, используемый в Symfony и многих других PHP-проектах. Инструменты ИИ для кодирования особенно хорошо знают Twig, и по умолчанию он автоматически экранирует вывод, что помогает защититься от XSS.

#### Установка

```bash
composer require twig/twig
```

(Уже включен при выполнении `composer create-project flightphp/skeleton`.)

#### Базовая конфигурация

Переопределите метод `render`, чтобы использовать Twig вместо стандартного PHP-рендерера:

```php
// переопределяем метод render для использования Twig вместо стандартного PHP-рендерера
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Где Twig хранит скомпилированные шаблоны
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

В скелете эта настройка находится в `app/config/services.php` (общее окружение Twig, путь к кэшу, глобальные переменные, такие как `base_url` / nonce CSP). Предпочтительно внедрять `Engine` и вызывать `$app->render()` из контроллеров, чтобы код оставался [удобным для ИИ и тестирования](/learn/ai).

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

Когда вы посетите `/Bob` в браузере, вывод будет:

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

#### Дополнительное чтение

Более полный пример использования Twig с макетами приведен в разделе [awesome-плагины](/awesome-plugins/twig) этой документации. Чтобы увидеть метрики времени рендеринга на панели Tracy, обратитесь к разделу [панель Twig в расширениях Tracy](/awesome-plugins/tracy-extensions#twig-panel-optional).

Вы можете узнать больше о полных возможностях Twig, прочитав [официальную документацию](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">отличная альтернатива</span>

[Latte](https://latte.nette.org/) — это полнофункциональный движок с синтаксисом, похожим на PHP. Он по-прежнему является отличным выбором для приложений Flight; скелет просто стандартизирует Twig как единый вариант по умолчанию (это особенно полезно, когда инструменты ИИ генерируют шаблоны).

#### Установка

```bash
composer require latte/latte
```

#### Базовая конфигурация

Основная идея заключается в том, чтобы переопределить метод `render` и использовать Latte вместо стандартного PHP-рендерера.

```php
// переопределяем метод render для использования Latte вместо стандартного PHP-рендерера
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Где именно Latte хранит свой кэш
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

Когда вы посетите `/Bob` в браузере, вывод будет:

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

#### Дополнительное чтение

Более сложный пример использования Latte с макетами приведен в разделе [awesome-плагины](/awesome-plugins/latte) этой документации.

Вы можете узнать больше о полных возможностях Latte, включая возможности перевода и локализации, прочитав [официальную документацию](https://latte.nette.org/en/).

### Встроенный движок представлений

<span class="badge bg-warning">устарело</span>

> **Примечание:** Хотя это всё еще функциональность по умолчанию и технически работает.

Чтобы отобразить шаблон представления, вызовите метод `render` с именем файла шаблона и необязательными данными шаблона:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Передаваемые данные шаблона автоматически внедряются в шаблон и могут использоваться как локальная переменная. Файлы шаблонов — это просто PHP-файлы. Если содержимое файла шаблона `hello.php` выглядит так:

```php
Hello, <?= $name ?>!
```

Вывод будет:

```text
Hello, Bob!
```

Вы также можете вручную задать переменные представления с помощью метода `set`:

```php
Flight::view()->set('name', 'Bob');
```

Теперь переменная `name` доступна во всех ваших представлениях. Поэтому вы можете просто сделать:

```php
Flight::render('hello');
```

Обратите внимание, что при указании имени шаблона в методе `render` можно опустить расширение `.php`.

По умолчанию Flight ищет каталог `views` для файлов шаблонов. Вы можете указать альтернативный путь для ваших шаблонов, задав следующую конфигурацию:

```php
Flight::set('flight.views.path', '/path/to/views');
```

По умолчанию встроенный `View` во Flight также принимает абсолютный путь к шаблону или имя, выходящее за пределы этого каталога. Для большинства приложений это следует ограничить:

```php
Flight::set('flight.views.restrict_to_path', true);
```

Это ограничивает `render()`, `fetch()` и `exists()` пределами `flight.views.path`. По умолчанию эта опция выключена для обратной совместимости. См. [Безопасность](/learn/security#flightviewsrestrict_to_path).

#### Макеты

Для веб-сайтов часто используется единый файл макета с изменяющимся содержимым. Чтобы отобразить контент для использования в макете, вы можете передать необязательный параметр в метод `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

После этого ваше представление будет содержать сохраненные переменные `headerContent` и `bodyContent`. Затем вы можете отобразить макет следующим образом:

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

Вывод будет:

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

Вот как можно использовать шаблонизатор [Smarty](http://www.smarty.net/) для ваших представлений:

```php
// Загружаем библиотеку Smarty
require './Smarty/libs/Smarty.class.php';

// Регистрируем Smarty как класс представления
// Также передаем callback-функцию для настройки Smarty при загрузке
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Назначаем данные шаблона
Flight::view()->assign('name', 'Bob');

// Отображаем шаблон
Flight::view()->display('hello.tpl');
```

Для полноты картины вам также следует переопределить стандартный метод `render` во Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Вот как можно использовать шаблонизатор [Blade](https://laravel.com/docs/8.x/blade) для ваших представлений:

Сначала вам нужно установить библиотеку BladeOne через Composer:

```bash
composer require eftec/bladeone
```

Затем вы можете настроить BladeOne как класс представления во Flight:

```php
<?php
// Загружаем библиотеку BladeOne
use eftec\bladeone\BladeOne;

// Регистрируем BladeOne как класс представления
// Также передаем callback-функцию для настройки BladeOne при загрузке
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Назначаем данные шаблона
Flight::view()->share('name', 'Bob');

// Отображаем шаблон
echo Flight::view()->run('hello', []);
```

Для полноты картины вам также следует переопределить стандартный метод `render` во Flight:

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

Вывод будет:

```
Hello, Bob!
```

## Смотрите также
- [Установка](/install) — Расположение скелета (`app/views/*.twig`) для новых проектов.
- [Расширение](/learn/extending) — Как переопределить метод `render` для использования другого шаблонизатора.
- [Маршрутизация](/learn/routing) — Как сопоставить маршруты с контроллерами и отображать представления.
- [Ответы](/learn/responses) — Как настраивать HTTP-ответы.
- [Безопасность](/learn/security) — Автоматическое экранирование, XSS и `flight.views.restrict_to_path`.
- [ИИ и опыт разработчика](/learn/ai) — Почему один движок представлений по умолчанию помогает агентам кодирования.
- [Зачем нужен фреймворк?](/learn/why-frameworks) — Как шаблоны вписываются в общую картину.

## Устранение неполадок
- Если в вашем middleware есть редирект, но приложение, похоже, не выполняет перенаправление, убедитесь, что добавили оператор `exit;` в ваш middleware.
- Если Twig не может найти шаблон, проверьте `flight.views.path` и что файл существует по этому пути с ожидаемым расширением (скелет: `app/views/`).

## Журнал изменений
- Документация — Описана опция `flight.views.restrict_to_path` для нативных PHP-представлений.
- Документация — Twig задокументирован как официальный вариант по умолчанию в скелете; Latte остается полноценной альтернативой.
- v2.0 — Первоначальный выпуск.
```