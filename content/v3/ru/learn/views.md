# Виды

Flight по умолчанию предоставляет некоторые базовые функции шаблонизации. Чтобы отобразить вид
шаблона, вызовите метод `render` с именем файла шаблона и необязательными
данными шаблона:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Данные шаблона, которые вы передаете, автоматически встраиваются в шаблон и могут
быть обращены как локальная переменная. Файлы шаблонов - это просто файлы PHP. Если
содержимое файла шаблона `hello.php` выглядит так:

```php
Привет, <?= $name ?>!
```

То вывод будет:

```
Привет, Bob!
```

Вы также можете вручную устанавливать переменные представления с помощью метода set:

```php
Flight::view()->set('name', 'Bob');
```

Переменная `name` теперь доступна во всех ваших представлениях. Поэтому вы просто можете сделать:

```php
Flight::render('hello');
```

Обратите внимание, что при указании имени шаблона в методе render вы можете
пропустить расширение `.php`.

По умолчанию Flight будет искать каталог `views` для файлов шаблонов. Вы можете
задать альтернативный путь для ваших шаблонов, установив следующую конфигурацию:

```php
Flight::set('flight.views.path', '/путь/к/views');
```

## Макеты

Часто веб-сайты имеют один файл шаблона макета с переменным
содержимым. Чтобы отобразить содержимое для использования в макете, вы можете передать
необязательный параметр в метод `render`.

```php
Flight::render('header', ['heading' => 'Привет'], 'headerContent');
Flight::render('body', ['body' => 'Мир'], 'bodyContent');
```

Ваше представление затем будет иметь сохраненные переменные с именами `headerContent` и `bodyContent`.
Затем вы можете отобразить ваш макет так:

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

Flight позволяет заменить стандартный движок представлений просто зарегистрировав свой
собственный класс представлений. Вот как вы можете использовать [Smarty](http://www.smarty.net/)
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

// Отображение шаблона
Flight::view()->display('hello.tpl');
```

Для полноты вы также должны переопределить стандартный метод render Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```