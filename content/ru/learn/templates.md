# Представления

Flight по умолчанию предоставляет некоторые базовые функции шаблонизации.

Если вам нужны более сложные потребности в шаблонизации, см. примеры Smarty и Latte в разделе [Пользовательские представления](#custom-views).

Чтобы отобразить шаблон представления, вызовите метод `render` с именем
файла шаблона и необязательными данными шаблона:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Данные шаблона, которые вы передаете, автоматически вставляются в шаблон и могут
быть использованы как локальная переменная. Файлы шаблонов представляют собой обычные PHP-файлы. Если содержимое файла шаблона `hello.php` выглядит так:

```php
Привет, <?= $name ?>!
```

Вывод будет:

```
Привет, Bob!
```

Также можно вручную устанавливать переменные представления, используя метод set:

```php
Flight::view()->set('name', 'Bob');
```

Переменная `name` теперь доступна во всех ваших представлениях. Поэтому вы можете просто сделать:

```php
Flight::render('hello');
```

Обратите внимание, что при указании имени шаблона в методе render можно
опустить расширение `.php`.

По умолчанию Flight будет искать каталог `views` для файлов шаблонов. Вы можете
установить альтернативный путь для ваших шаблонов, установив следующую конфигурацию:

```php
Flight::set('flight.views.path', '/путь/к/шаблонам');
```

## Макеты

Часто веб-сайты имеют один общий файл макета с переменным
содержанием. Чтобы отобразить содержимое для использования в макете, можно передать
дополнительный параметр в метод `render`.

```php
Flight::render('header', ['heading' => 'Привет'], 'headerContent');
Flight::render('body', ['body' => 'Мир'], 'bodyContent');
```

Ваше представление затем будет иметь сохраненные переменные с именами `headerContent` и `bodyContent`.
Затем можно отобразить макет, выполнив:

```php
Flight::render('layout', ['title' => 'Домашняя страница']);
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
    <title>Домашняя страница</title>
  </head>
  <body>
    <h1>Привет</h1>
    <div>Мир</div>
  </body>
</html>
```

## Пользовательские представления

Flight позволяет заменить базовый механизм представлений, просто зарегистрировав свой
собственный класс представления.

### Smarty

Вот как использовать [шаблонизатор Smarty](http://www.smarty.net/)
для ваших представлений:

```php
// Загрузить библиотеку Smarty
require './Smarty/libs/Smarty.class.php';

// Регистрация Smarty в качестве класса представления
// Также передайте функцию обратного вызова для настройки Smarty при загрузке
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
});

// Назначить данные шаблона
Flight::view()->assign('name', 'Bob');

// Отобразить шаблон
Flight::view()->display('hello.tpl');
```

Для завершенности следует также переопределить метод отображения по умолчанию в Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Вот как использовать [шаблонизатор Latte](https://latte.nette.org/)
для ваших представлений:

```php

// Регистрация Latte в качестве класса представления
// Также передайте функцию обратного вызова для настройки Latte при загрузке
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Здесь Latte будет кэшировать ваши шаблоны для ускорения
	// Одна из хороших особенностей Latte заключается в том, что он автоматически обновляет
	// кэш при изменении ваших шаблонов!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Скажите Latte, где будет расположена корневая директория для ваших представлений.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// И заверните это, чтобы можно было использовать Flight::render() правильно
Flight::map('render', function(string $template, array $data): void {
  // Это похоже на $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```