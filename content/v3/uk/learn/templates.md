# HTML-представлення та шаблони

## Огляд

Flight за замовчуванням надає деякі базові функції HTML-шаблонізації. Шаблонізація — це дуже ефективний спосіб відокремити логіку вашого додатку від рівня представлення. Спеціалізований рушій (Twig, Latte тощо) також надає [AI-інструментам для програмування](/learn/ai) звичний, обмежений синтаксис, тому вони менш схильні вставляти бізнес-логіку у ваш HTML.

## Розуміння

Коли ви створюєте додаток, ймовірно, у вас буде HTML, який ви захочете повернути кінцевому користувачу. PHP сам по собі є мовою шаблонів, але _дуже_ легко вставити бізнес-логіку, таку як виклики бази даних, API тощо, у ваш HTML-файл, що робить тестування та розділення дуже складним процесом. Передаючи дані в шаблон і дозволяючи шаблону відображати себе, стає набагато простіше розділяти та модульно тестувати ваш код. Ви подякуєте нам, якщо використовуєте шаблони!

## Базове використання

Flight дозволяє замінити стандартний рушій представлень, просто відобразивши `render` (або зареєструвавши клас представлення). Прокрутіть вниз, щоб дізнатися про Twig, Latte, Smarty, Blade та інші.

> **Стандартний скелет:** Офіційний [flightphp/skeleton](https://github.com/flightphp/skeleton) використовує **лише Twig** у `app/views/` (`*.twig`). Контролери викликають `$this->app->render('welcome', $data)` (розширення необов'язкове). Це вибір додатку для нових проєктів, а не вимога ядра Flight. Latte та інші рушії залишаються повністю підтримуваними.

### Twig

<span class="badge bg-info">стандартний скелет</span>

[Twig](https://twig.symfony.com/) — це гнучкий, швидкий та безпечний рушій шаблонів, який використовується Symfony та багатьма іншими PHP-проєктами. AI-інструменти для програмування зазвичай добре знають Twig, і він автоматично екранує вивід за замовчуванням, що допомагає захистити від XSS.

#### Встановлення

```bash
composer require twig/twig
```

(Вже включено, коли ви виконуєте `composer create-project flightphp/skeleton`.)

#### Базова конфігурація

Перезапишіть метод `render`, щоб використовувати Twig замість стандартного PHP-рендерера:

```php
// перезапишіть метод render, щоб використовувати Twig замість стандартного PHP-рендерера
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Де Twig зберігає свої скомпільовані шаблони
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// Дозволити "welcome" або "welcome.twig"
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

У скелеті ця конфігурація знаходиться в `app/config/services.php` (спільне середовище Twig, шлях кешу, глобальні змінні, такі як `base_url` / CSP nonce). Надавайте перевагу ін'єкції `Engine` та виклику `$app->render()` з контролерів, щоб код залишався [дружнім до AI та тестування](/learn/ai).

#### Використання Twig у Flight

Тепер, коли ви можете рендерити за допомогою Twig, ви можете зробити щось на кшталт цього:

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

Коли ви відвідаєте `/Bob` у своєму браузері, вивід буде таким:

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

#### Додаткове читання

Більш повний приклад використання Twig з макетами наведено в розділі [чудові плагіни](/awesome-plugins/twig) цієї документації. Щодо метрик часу рендерингу на панелі Tracy, дивіться [панель Twig у Tracy Extensions](/awesome-plugins/tracy-extensions#twig-panel-optional).

Ви можете дізнатися більше про повні можливості Twig, прочитавши [офіційну документацію](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">чудова альтернатива</span>

[Latte](https://latte.nette.org/) — це повнофункціональний рушій із синтаксисом, схожим на PHP. Він все ще є відмінним вибором для додатків Flight; скелет просто стандартизує Twig як єдиний спільний стандарт (особливо корисно, коли AI-інструменти генерують шаблони).

#### Встановлення

```bash
composer require latte/latte
```

#### Базова конфігурація

Основна ідея полягає в тому, що ви перезаписуєте метод `render`, щоб використовувати Latte замість стандартного PHP-рендерера.

```php
// перезапишіть метод render, щоб використовувати latte замість стандартного PHP-рендерера
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Де latte зберігає свій кеш
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Використання Latte у Flight

Тепер, коли ви можете рендерити за допомогою Latte, ви можете зробити щось на кшталт цього:

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

Коли ви відвідаєте `/Bob` у своєму браузері, вивід буде таким:

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

#### Додаткове читання

Більш складний приклад використання Latte з макетами наведено в розділі [чудові плагіни](/awesome-plugins/latte) цієї документації.

Ви можете дізнатися більше про повні можливості Latte, включаючи переклад та мовні можливості, прочитавши [офіційну документацію](https://latte.nette.org/en/).

### Вбудований рушій представлень

<span class="badge bg-warning">застаріло</span>

> **Примітка:** Хоча це все ще стандартна функціональність і технічно працює.

Щоб відобразити шаблон представлення, викличте метод `render` з назвою 
файлу шаблону та необов'язковими даними шаблону:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Дані шаблону, які ви передаєте, автоматично впроваджуються в шаблон, і на них можна
посилатися як на локальну змінну. Файли шаблонів — це просто PHP-файли. Якщо
вміст файлу шаблону `hello.php` такий:

```php
Hello, <?= $name ?>!
```

Вивід буде таким:

```text
Hello, Bob!
```

Ви також можете вручну встановити змінні представлення за допомогою методу set:

```php
Flight::view()->set('name', 'Bob');
```

Змінна `name` тепер доступна в усіх ваших представленнях. Отже, ви можете просто зробити:

```php
Flight::render('hello');
```

Зауважте, що вказуючи назву шаблону в методі render, ви можете
опустити розширення `.php`.

За замовчуванням Flight шукає файли шаблонів у каталозі `views`. Ви можете
встановити альтернативний шлях для ваших шаблонів, встановивши таку конфігурацію:

```php
Flight::set('flight.views.path', '/path/to/views');
```

За замовчуванням вбудований `View` Flight також приймає абсолютний шлях до шаблону або назву, яка виходить за межі цього каталогу. Для більшості додатків вам слід це обмежити:

```php
Flight::set('flight.views.restrict_to_path', true);
```

Це утримує `render()`, `fetch()` та `exists()` в межах `flight.views.path`. За замовчуванням це вимкнено для зворотної сумісності. Дивіться [Безпека](/learn/security#flightviewsrestrict_to_path).

#### Макети

Зазвичай веб-сайти мають єдиний файл шаблону макета зі змінним
вмістом. Щоб відрендерити вміст, який використовуватиметься в макеті, ви можете передати необов'язковий
параметр у метод `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Потім ваше представлення матиме збережені змінні з назвами `headerContent` та `bodyContent`.
Потім ви можете відрендерити свій макет, зробивши:

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

Вивід буде таким:
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

Ось як ви можете використовувати рушій шаблонів [Smarty](http://www.smarty.net/)
для ваших представлень:

```php
// Завантажити бібліотеку Smarty
require './Smarty/libs/Smarty.class.php';

// Зареєструвати Smarty як клас представлення
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

Для повноти, ви також повинні перевизначити стандартний метод render Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Ось як ви можете використовувати рушій шаблонів [Blade](https://laravel.com/docs/8.x/blade) для ваших представлень:

Спочатку вам потрібно встановити бібліотеку BladeOne через Composer:

```bash
composer require eftec/bladeone
```

Потім ви можете налаштувати BladeOne як клас представлення у Flight:

```php
<?php
// Завантажити бібліотеку BladeOne
use eftec\bladeone\BladeOne;

// Зареєструвати BladeOne як клас представлення
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

Для повноти, ви також повинні перевизначити стандартний метод render Flight:

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

Вивід буде таким:

```
Hello, Bob!
```

## Дивіться також
- [Встановлення](/install) - Структура скелета (`app/views/*.twig`) для нових проєктів.
- [Розширення](/learn/extending) - Як перевизначити метод `render`, щоб використовувати інший рушій шаблонів.
- [Маршрутизація](/learn/routing) - Як зіставити маршрути з контролерами та відрендерити представлення.
- [Відповіді](/learn/responses) - Як налаштувати HTTP-відповіді.
- [Безпека](/learn/security) - Автоматичне екранування, XSS та `flight.views.restrict_to_path`.
- [AI та досвід розробника](/learn/ai) - Чому один стандартний рушій представлень допомагає агентам програмування.
- [Чому фреймворк?](/learn/why-frameworks) - Як шаблони вписуються в загальну картину.

## Вирішення проблем
- Якщо у вас є перенаправлення у вашому проміжному програмному забезпеченні, але ваш додаток, здається, не перенаправляє, переконайтеся, що ви додали оператор `exit;` у вашому проміжному програмному забезпеченні.
- Якщо Twig не може знайти шаблон, перевірте `flight.views.path` та чи існує файл за цим шляхом з очікуваним розширенням (скелет: `app/views/`).

## Журнал змін
- Документація – задокументовано `flight.views.restrict_to_path` для нативних PHP-представлень.
- Документація – Twig задокументовано як офіційний стандарт скелета; Latte залишається першокласною альтернативою.
- v2.0 - Початковий випуск.