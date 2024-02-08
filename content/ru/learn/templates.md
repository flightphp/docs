# Просмотры

По умолчанию Flight предоставляет некоторую базовую функциональность шаблонизации.

Если вам нужны более сложные потребности в шаблонизации, ознакомьтесь с примерами Smarty и Latte в разделе [Пользовательские представления](#custom-views).

Для отображения шаблона представления вызовите метод `render` с именем файла шаблона и необязательными данными шаблона:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Данные шаблона, которые вы передаете, автоматически внедряются в шаблон и могут быть использованы как локальная переменная. Файлы шаблонов просто являются файлами PHP. Если содержимое файла шаблона `hello.php` таково:

```php
Привет, <?= $name ?>!
```

Вывод будет:

```
Привет, Bob!
```

Вы также можете вручную устанавливать переменные представления, используя метод set:

```php
Flight::view()->set('name', 'Bob');
```

Переменная `name` теперь доступна во всех ваших представлениях. Таким образом, вы можете просто сделать:

```php
Flight::render('hello');
```

Обратите внимание, что при указании имени шаблона в методе render вы можете опустить расширение `.php`.

По умолчанию Flight будет искать каталог `views` для файлов шаблонов. Вы можете установить альтернативный путь к вашим шаблонам, установив следующую конфигурацию:

```php
Flight::set('flight.views.path', '/path/to/views');
```

## Макеты

Часто веб-сайты имеют один файл макета с изменяющимся содержимым. Чтобы отобразить содержимое, которое будет использоваться в макете, вы можете передать необязательный параметр в метод `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

В вашем представлении будут сохранены переменные с именами `headerContent` и `bodyContent`.
Затем вы можете создать макет, сделав следующее:

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
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

## Пользовательские Представления

Flight позволяет заменить движок представлений по умолчанию, просто зарегистрировав свой собственный класс представлений.

### Smarty

Вот как вы можете использовать движок шаблонов [Smarty](http://www.smarty.net/) для ваших представлений:

```php
// Загрузите библиотеку Smarty
require './Smarty/libs/Smarty.class.php';

// Зарегистрируйте Smarty в качестве класса представлений
// Также передайте обратный вызов для настройки Smarty при загрузке
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
});

// Назначьте данные шаблона
Flight::view()->assign('name', 'Bob');

// Отобразить шаблон
Flight::view()->display('hello.tpl');
```

Для полноты переопределения метода render Flight по умолчанию:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Вот как вы можете использовать движок шаблонов [Latte](https://latte.nette.org/) для ваших представлений:

```php

// Зарегистрируйте Latte в качестве класса представлений
// Также передайте обратный вызов для настройки Latte при загрузке
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Здесь Latte будет кэшировать ваши шаблоны, чтобы ускорить процесс
  // Одна из хороших особенностей Latte в том, что он автоматически обновляет кэш
  // при изменении ваших шаблонов!
  $latte->setTempDirectory(__DIR__ . '/../cache/');

  // Скажите Latte, где будет корневой каталог для ваших представлений.
  $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// И заверните его, чтобы вы могли правильно использовать Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // Это похоже на $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```