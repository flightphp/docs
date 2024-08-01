# HTML Виды и Шаблоны

Flight предоставляет некоторые базовые возможности шаблонизации по умолчанию.

Если вам нужны более сложные потребности в шаблонизации, обратитесь к примерам Smarty и Latte в разделе [Пользовательские Виды](#custom-views).

## По умолчанию движок представления

Чтобы отобразить представление шаблона, вызовите метод `render` с именем файла шаблона и необязательными данными шаблона:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Данные шаблона, которые вы передаете, автоматически вставляются в шаблон и могут
быть использованы как локальная переменная. Файлы шаблонов представляют собой просто файлы PHP. Если содержимое файла шаблона `hello.php` таково:

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

Переменная `name` теперь доступна во всех ваших представлениях. Поэтому вы можете просто сделать:

```php
Flight::render('hello');
```

Обратите внимание, что при указании имени шаблона в методе render вы можете
опустить расширение `.php`.

По умолчанию Flight будет искать каталог `views` для файлов шаблонов. Вы можете
указать альтернативный путь для ваших шаблонов, установив следующую конфигурацию:

```php
Flight::set('flight.views.path', '/путь/к/шаблонам');
```

### Макеты

Часто в веб-сайтах есть единственный файл шаблона макета с изменяющимися
содержимым. Чтобы отобразить содержимое, которое будет использоваться в макете, вы можете передать необязательный параметр в метод `render`.

```php
Flight::render('header', ['heading' => 'Привет'], 'headerContent');
Flight::render('body', ['body' => 'Мир'], 'bodyContent');
```

У вас в представлении будут сохранены переменные с именами `headerContent` и `bodyContent`.
Затем вы можете отобразить свой макет, сделав так:

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

## Пользовательские движки представления

Flight позволяет заменить используемый по умолчанию движок представления просто зарегистрировав свой собственный класс представления.

### Smarty

Вот как вы будете использовать [Smarty](http://www.smarty.net/)
шаблонный движок для ваших представлений:

```php
// Загрузка библиотеки Smarty
require './Smarty/libs/Smarty.class.php';

// Регистрация Smarty как класса представления
// Также передайте функцию обратного вызова для настройки Smarty при загрузке
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Назначение данных шаблона
Flight::view()->assign('name', 'Bob');

// Отобразить шаблон
Flight::view()->display('hello.tpl');
```

Для полноты вы должны также переопределить метод render по умолчанию Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Вот как вы будете использовать [Latte](https://latte.nette.org/)
шаблонный движок для ваших представлений:

```php

// Регистрация Latte как класса представления
// Также передайте функцию обратного вызова для настройки Latte при загрузке
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Здесь Latte будет кешировать ваши шаблоны для ускорения работы
	// Одна из удобных особенностей Latte заключается в том, что она автоматически обновляет
	// кеш при внесении изменений в ваши шаблоны!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Скажите Latte, где будет находиться корневой каталог для ваших представлений.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// И завершите так, чтобы вы могли правильно использовать Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // Это похоже на $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```