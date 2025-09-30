# HTML Перегляди та Шаблони

## Огляд

Flight надає базову функціональність шаблонізації HTML за замовчуванням. Шаблонізація є дуже ефективним способом відокремити логіку вашого додатка від шару представлення.

## Розуміння

Коли ви створюєте додаток, ви, ймовірно, матимете HTML, який хочете передати кінцевому користувачеві. PHP сам по собі є мовою шаблонізації, але _дуже_ легко вплутати бізнес-логіку, таку як виклики бази даних, виклики API тощо, у ваш HTML-файл і зробити тестування та розв'язання залежностей дуже складним процесом. Передаючи дані в шаблон і дозволяючи шаблону рендерити себе, стає набагато легше розв'язувати залежності та проводити модульне тестування вашого коду. Ви подякуєте нам, якщо використовуватимете шаблони!

## Базове Використання

Flight дозволяє замінити стандартний рушій переглядів просто реєструючи свій
власний клас переглядів. Прокрутіть вниз, щоб побачити приклади використання Smarty, Latte, Blade та інших!

### Latte

<span class="badge bg-info">рекомендовано</span>

Ось як ви б використовували рушій шаблонів [Latte](https://latte.nette.org/)
для ваших переглядів.

#### Встановлення

```bash
composer require latte/latte
```

#### Базова Конфігурація

Основна ідея полягає в тому, що ви перезаписуєте метод `render` для використання Latte замість стандартного рендерера PHP.

```php
// перезаписати метод render для використання latte замість стандартного рендерера PHP
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Де latte конкретно зберігає свій кеш
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Використання Latte в Flight

Тепер, коли ви можете рендерити з Latte, ви можете зробити щось на кшталт цього:

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

Коли ви відвідаєте `/Bob` у своєму браузері, вихід буде таким:

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

#### Додаткове Читання

Більш складний приклад використання Latte з макетами показано в розділі [awesome plugins](/awesome-plugins/latte) цієї документації.

Ви можете дізнатися більше про повні можливості Latte, включаючи переклад та мовні можливості, прочитавши [офіційну документацію](https://latte.nette.org/en/).

### Вбудований Рушій Переглядів

<span class="badge bg-warning">застаріло</span>

> **Примітка:** Хоча це все ще стандартна функціональність і технічно працює.

Щоб відобразити шаблон перегляду, викличте метод `render` з назвою 
файлу шаблону та необов'язковими даними шаблону:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Дані шаблону, які ви передаєте, автоматично інжектуються в шаблон і можуть
бути посилання як локальна змінна. Файли шаблонів є просто PHP-файлами. Якщо зміст
файлу шаблону `hello.php` такий:

```php
Hello, <?= $name ?>!
```

Вихід буде:

```text
Hello, Bob!
```

Ви також можете вручну встановити змінні перегляду, використовуючи метод set:

```php
Flight::view()->set('name', 'Bob');
```

Змінна `name` тепер доступна у всіх ваших переглядах. Тому ви можете просто зробити:

```php
Flight::render('hello');
```

Зверніть увагу, що при вказівці назви шаблону в методі render, ви можете
пропустити розширення `.php`.

За замовчуванням Flight шукатиме директорію `views` для файлів шаблонів. Ви можете
встановити альтернативний шлях для ваших шаблонів, встановивши наступну конфігурацію:

```php
Flight::set('flight.views.path', '/path/to/views');
```

#### Макети

Зазвичай для веб-сайтів є один файл шаблону макету з змінним
змістом. Щоб рендерити вміст для використання в макеті, ви можете передати необов'язковий
параметр до методу `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Ваш перегляд тоді матиме збережені змінні під назвою `headerContent` та `bodyContent`.
Потім ви можете рендерити свій макет так:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Якщо файли шаблонів виглядають так:

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

Вихід буде:
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

Ось як ви б використовували рушій шаблонів [Smarty](http://www.smarty.net/)
для ваших переглядів:

```php
// Завантажити бібліотеку Smarty
require './Smarty/libs/Smarty.class.php';

// Зареєструвати Smarty як клас переглядів
// Також передати функцію зворотного виклику для налаштування Smarty при завантаженні
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Призначити дані шаблону
Flight::view()->assign('name', 'Bob');

// Відобразити шаблон
Flight::view()->display('hello.tpl');
```

Для повноти ви також повинні перезаписати стандартний метод render Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Ось як ви б використовували рушій шаблонів [Blade](https://laravel.com/docs/8.x/blade) для ваших переглядів:

Спочатку вам потрібно встановити бібліотеку BladeOne через Composer:

```bash
composer require eftec/bladeone
```

Потім ви можете налаштувати BladeOne як клас переглядів у Flight:

```php
<?php
// Завантажити бібліотеку BladeOne
use eftec\bladeone\BladeOne;

// Зареєструвати BladeOne як клас переглядів
// Також передати функцію зворотного виклику для налаштування BladeOne при завантаженні
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Призначити дані шаблону
Flight::view()->share('name', 'Bob');

// Відобразити шаблон
echo Flight::view()->run('hello', []);
```

Для повноти ви також повинні перезаписати стандартний метод render Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

У цьому прикладі файл шаблону hello.blade.php може виглядати так:

```php
<?php
Hello, {{ $name }}!
```

Вихід буде:

```
Hello, Bob!
```

## Дивіться Також
- [Extending](/learn/extending) - Як перезаписати метод `render` для використання іншого рушія шаблонів.
- [Routing](/learn/routing) - Як зіставляти маршрути з контролерами та рендерити перегляди.
- [Responses](/learn/responses) - Як налаштовувати HTTP-відповіді.
- [Why a Framework?](/learn/why-frameworks) - Як шаблони вписуються в загальну картину.

## Вирішення Проблем
- Якщо у вас є перенаправлення у вашому middleware, але ваш додаток не здається перенаправленим, переконайтеся, що ви додали оператор `exit;` у ваш middleware.

## Журнал Змін
- v2.0 - Початковий реліз.