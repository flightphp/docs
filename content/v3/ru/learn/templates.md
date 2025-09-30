# HTML-шаблоны и представления

## Обзор

Flight предоставляет базовую функциональность шаблонизации HTML по умолчанию. Шаблонизация — это очень эффективный способ отделить логику приложения от слоя представления.

## Понимание

При создании приложения вам, вероятно, потребуется HTML, который вы захотите передать конечному пользователю. PHP сам по себе является языком шаблонизации, но _очень_ легко включить в файл HTML бизнес-логику, такую как вызовы базы данных, API и т.д., что делает тестирование и разделение очень сложным процессом. Передавая данные в шаблон и позволяя шаблону рендериться самостоятельно, становится гораздо проще разделять и проводить модульное тестирование вашего кода. Вы поблагодарите нас, если будете использовать шаблоны!

## Базовое использование

Flight позволяет заменить стандартный движок представлений, просто зарегистрировав свой собственный класс представлений. Прокрутите вниз, чтобы увидеть примеры использования Smarty, Latte, Blade и других!

### Latte

<span class="badge bg-info">рекомендуется</span>

Вот как вы можете использовать движок шаблонов [Latte](https://latte.nette.org/) для ваших представлений.

#### Установка

```bash
composer require latte/latte
```

#### Базовая конфигурация

Основная идея в том, чтобы переопределить метод `render` для использования Latte вместо стандартного рендерера PHP.

```php
// переопределите метод render для использования latte вместо стандартного рендерера PHP
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Где latte специально хранит свой кэш
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Использование Latte в Flight

Теперь, когда вы можете рендерить с помощью Latte, вы можете сделать что-то вроде этого:

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

Когда вы посетите `/Bob` в вашем браузере, вывод будет следующим:

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

Более сложный пример использования Latte с макетами показан в разделе [awesome plugins](/awesome-plugins/latte) этой документации.

Вы можете узнать больше о полных возможностях Latte, включая перевод и языковые возможности, прочитав [официальную документацию](https://latte.nette.org/en/).

### Встроенный движок представлений

<span class="badge bg-warning">устарело</span>

> **Примечание:** Хотя это всё ещё функциональность по умолчанию и технически работает.

Чтобы отобразить шаблон представления, вызовите метод `render` с именем файла шаблона и необязательными данными шаблона:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Данные шаблона, которые вы передаёте, автоматически внедряются в шаблон и могут быть использованы как локальная переменная. Файлы шаблонов — это просто файлы PHP. Если содержимое файла шаблона `hello.php` выглядит так:

```php
Hello, <?= $name ?>!
```

Вывод будет:

```text
Hello, Bob!
```

Вы также можете вручную установить переменные представлений с помощью метода set:

```php
Flight::view()->set('name', 'Bob');
```

Переменная `name` теперь доступна во всех ваших представлениях. Таким образом, вы можете просто сделать:

```php
Flight::render('hello');
```

Обратите внимание, что при указании имени шаблона в методе render вы можете опустить расширение `.php`.

По умолчанию Flight будет искать директорию `views` для файлов шаблонов. Вы можете установить альтернативный путь для ваших шаблонов, задав следующую конфигурацию:

```php
Flight::set('flight.views.path', '/path/to/views');
```

#### Макеты

Обычно для веб-сайтов используется один файл шаблона макета с изменяемым содержимым. Чтобы отрендерить содержимое для использования в макете, вы можете передать необязательный параметр в метод `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Ваше представление затем сохранит переменные с именами `headerContent` и `bodyContent`. Затем вы можете отрендерить свой макет следующим образом:

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

Вот как вы можете использовать движок шаблонов [Smarty](http://www.smarty.net/) для ваших представлений:

```php
// Загрузите библиотеку Smarty
require './Smarty/libs/Smarty.class.php';

// Зарегистрируйте Smarty как класс представлений
// Также передайте функцию обратного вызова для конфигурации Smarty при загрузке
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Назначьте данные шаблона
Flight::view()->assign('name', 'Bob');

// Отобразите шаблон
Flight::view()->display('hello.tpl');
```

Для полноты вы также должны переопределить стандартный метод render Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Вот как вы можете использовать движок шаблонов [Blade](https://laravel.com/docs/8.x/blade) для ваших представлений:

Сначала вам нужно установить библиотеку BladeOne через Composer:

```bash
composer require eftec/bladeone
```

Затем вы можете настроить BladeOne как класс представлений в Flight:

```php
<?php
// Загрузите библиотеку BladeOne
use eftec\bladeone\BladeOne;

// Зарегистрируйте BladeOne как класс представлений
// Также передайте функцию обратного вызова для конфигурации BladeOne при загрузке
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Назначьте данные шаблона
Flight::view()->share('name', 'Bob');

// Отобразите шаблон
echo Flight::view()->run('hello', []);
```

Для полноты вы также должны переопределить стандартный метод render Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

В этом примере файл шаблона hello.blade.php может выглядеть так:

```php
<?php
Hello, {{ $name }}!
```

Вывод будет:

```
Hello, Bob!
```

## См. также
- [Расширение](/learn/extending) — Как переопределить метод `render` для использования другого движка шаблонов.
- [Маршрутизация](/learn/routing) — Как сопоставлять маршруты с контроллерами и рендерить представления.
- [Ответы](/learn/responses) — Как настраивать HTTP-ответы.
- [Зачем фреймворк?](/learn/why-frameworks) — Как шаблоны вписываются в общую картину.

## Устранение неисправностей
- Если у вас есть перенаправление в вашем middleware, но приложение не перенаправляется, убедитесь, что вы добавили инструкцию `exit;` в ваш middleware.

## Журнал изменений
- v2.0 — Первоначальный релиз.