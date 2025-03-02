# HTML Представления и Шаблоны

Flight предоставляет некоторые основные функции шаблонизации по умолчанию.

Flight позволяет вам заменить стандартный механизм представлений, просто зарегистрировав свой собственный класс представления. Прокрутите вниз, чтобы увидеть примеры использования Smarty, Latte, Blade и других!

## Встроенный Механизм Представлений

Чтобы отобразить шаблон представления, вызовите метод `render` с именем файла шаблона и необязательными данными шаблона:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Данные шаблона, которые вы передаете, автоматически внедряются в шаблон и могут быть использованы как локальная переменная. Файлы шаблонов — это просто файлы PHP. Если содержимое файла шаблона `hello.php` выглядит так:

```php
Hello, <?= $name ?>!
```

Вывод будет:

```
Hello, Bob!
```

Вы также можете вручную установить переменные представления, используя метод set:

```php
Flight::view()->set('name', 'Bob');
```

Переменная `name` теперь доступна во всех ваших представлениях. Так что вы можете просто сделать:

```php
Flight::render('hello');
```

Обратите внимание, что при указании имени шаблона в методе render, вы можете опустить расширение `.php`.

По умолчанию Flight будет искать каталог `views` для файлов шаблонов. Вы можете установить альтернативный путь для ваших шаблонов, установив следующую конфигурацию:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Макеты

Обычно у веб-сайтов есть один файл шаблона макета с меняющимся контентом. Для рендеринга контента, который будет использован в макете, вы можете передать необязательный параметр в метод `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Ваше представление затем будет иметь сохраненные переменные с именами `headerContent` и `bodyContent`. Затем вы можете отобразить ваш макет, сделав:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Если файлы шаблонов выглядят следующим образом:

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

## Smarty

Вот как вы можете использовать шаблонизатор [Smarty](http://www.smarty.net/) для ваших представлений:

```php
// Подключите библиотеку Smarty
require './Smarty/libs/Smarty.class.php';

// Зарегистрируйте Smarty как класс представления
// Также передайте функцию обратного вызова для настройки Smarty при загрузке
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

Для полноты, вы также должны переопределить стандартный метод рендеринга Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Вот как вы можете использовать шаблонизатор [Latte](https://latte.nette.org/) для ваших представлений:

```php

// Зарегистрируйте Latte как класс представления
// Также передайте функцию обратного вызова для настройки Latte при загрузке
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Здесь Latte будет кэшировать ваши шаблоны для ускорения работы
	// Одна интересная особенность Latte заключается в том, что он автоматически обновляет ваш
	// кэш, когда вы вносите изменения в ваши шаблоны!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Укажите Latte, где будет находиться корневая директория для ваших представлений.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// И заверните это, чтобы вы могли правильно использовать Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // Это похоже на $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Вот как вы можете использовать шаблонизатор [Blade](https://laravel.com/docs/8.x/blade) для ваших представлений:

Сначала вам нужно установить библиотеку BladeOne через Composer:

```bash
composer require eftec/bladeone
```

Затем вы можете настроить BladeOne как класс представления в Flight:

```php
<?php
// Подключите библиотеку BladeOne
use eftec\bladeone\BladeOne;

// Зарегистрируйте BladeOne как класс представления
// Также передайте функцию обратного вызова для настройки BladeOne при загрузке
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

Для полноты, вы также должны переопределить стандартный метод рендеринга Flight:

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

Следуя этим шагам, вы можете интегрировать движок шаблонов Blade с Flight и использовать его для рендеринга ваших представлений.